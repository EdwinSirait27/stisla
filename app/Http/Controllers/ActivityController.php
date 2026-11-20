<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;

/**
 * Activity Log: siapa melakukan apa, berbasis Spatie activity log.
 * Akses: permission "activity.view" (route + pengecekan di setiap method).
 */
class ActivityController extends Controller
{
    /** Label modul (log_name) yang lebih mudah dibaca. */
    public const MODULES = [
        'auth'               => 'Login',
        'roster'             => 'Roster',
        'employee'           => 'Karyawan',
        'document'           => 'Dokumen',
        'employee_salary'    => 'Gaji',
        'payroll_period'     => 'Periode payroll',
        'fingerprint'        => 'Fingerprint',
        'SkLetter'           => 'SK',
        'Submissionposition' => 'Pengajuan jabatan',
        'Structuresnew'      => 'Struktur',
        'default'            => 'Lainnya',
    ];

    public const EVENTS = [
        'created'      => ['Dibuat', 'is-approved'],
        'updated'      => ['Diubah', 'is-pending'],
        'deleted'      => ['Dihapus', 'is-rejected'],
        'login'        => ['Login', 'is-open'],
        'logout'       => ['Logout', 'is-draft'],
        'login_failed' => ['Login gagal', 'is-rejected'],
        'login_blocked' => ['Login ditolak', 'is-rejected'],
        'sent'         => ['Dikirim', 'is-approved'],
        'send_failed'  => ['Gagal kirim', 'is-rejected'],
    ];

    private const AUTH_EVENTS = ['login', 'logout', 'login_failed', 'login_blocked'];

    /** Kolom yang tidak ditampilkan di rincian perubahan. */
    private const HIDDEN_KEYS = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'updated_at', 'created_at'];

    private function authorizeView(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user || !$user->hasPermissionTo('activity.view')) {
            abort(403, 'Unauthorized');
        }
    }

    public static function hashId(string $id): string
    {
        return substr(hash('sha256', $id . env('APP_KEY')), 0, 8);
    }

    private function findUserByHash(string $hashedId): ?User
    {
        $id = User::query()->pluck('id')->first(fn($uid) => self::hashId($uid) === $hashedId);

        return $id ? User::with(['Employee.position' => fn($q) => $q->wherePivot('is_primary', true), 'roles:id,name'])->find($id) : null;
    }

    // ── Halaman utama ─────────────────────────────────────────────

    public function index()
    {
        $this->authorizeView();

        $today = Carbon::today();
        $week  = Carbon::today()->subDays(6);

        $stats = [
            'today'        => Activity::where('created_at', '>=', $today)->count(),
            'week'         => Activity::where('created_at', '>=', $week)->count(),
            'users_week'   => Activity::where('created_at', '>=', $week)->whereNotNull('causer_id')->distinct('causer_id')->count('causer_id'),
            'failed_week'  => Activity::where('created_at', '>=', $week)->whereIn('event', ['login_failed', 'login_blocked'])->count(),
            'total'        => Activity::count(),
        ];

        // Aktivitas per modul 7 hari terakhir
        $byModule = Activity::where('created_at', '>=', $week)
            ->selectRaw('log_name, COUNT(*) AS c')->groupBy('log_name')->orderByDesc('c')->get();

        $logNames = Activity::query()->distinct()->orderBy('log_name')->pluck('log_name')->filter()->values();
        $events   = Activity::query()->distinct()->orderBy('event')->pluck('event')->filter()->values();

        return view('pages.Activity.Activity', [
            'stats'    => $stats,
            'byModule' => $byModule,
            'logNames' => $logNames,
            'events'   => $events,
            'modules'  => self::MODULES,
            'eventMap' => self::EVENTS,
        ]);
    }

    /** DataTables: ringkasan per user (pelaku). */
    public function getUsers(Request $request)
    {
        $this->authorizeView();

        $since = $this->sinceFromRange($request->input('range'));

        $sub = Activity::query()
            ->selectRaw('causer_id, COUNT(*) AS total, MAX(created_at) AS last_at')
            ->selectRaw("SUM(event IN ('login_failed', 'login_blocked')) AS failed")
            ->where('causer_type', User::class)
            ->whereNotNull('causer_id')
            ->when($since, fn($q) => $q->where('created_at', '>=', $since))
            ->when($request->filled('log_name'), fn($q) => $q->where('log_name', $request->log_name))
            ->groupBy('causer_id');

        $query = User::query()
            ->joinSub($sub, 'a', 'a.causer_id', '=', 'users.id')
            ->leftJoin('employees_tables as e', 'e.id', '=', 'users.employee_id')
            ->select('users.id', 'users.username', 'users.active_role_hrx', 'e.employee_name', 'e.employee_pengenal', 'a.total', 'a.last_at', 'a.failed');

        return DataTables::of($query)
            ->addColumn('user_cell', fn($u) =>
                '<span class="pr-cell-name">' . e($u->employee_name ?? $u->username) . '</span>'
                . '<span class="pr-cell-sub">' . e(collect([$u->employee_pengenal ?? $u->username, $u->active_role_hrx])->filter()->implode(', ')) . '</span>')
            ->addColumn('total_fmt', fn($u) => number_format($u->total, 0, ',', '.'))
            ->addColumn('failed_fmt', fn($u) => $u->failed ? '<span class="status-badge is-rejected">' . (int) $u->failed . '</span>' : '<span class="pr-muted">-</span>')
            ->addColumn('last_fmt', fn($u) => $u->last_at
                ? '<span class="pr-cell-name">' . e(Carbon::parse($u->last_at)->locale('id')->diffForHumans()) . '</span><span class="pr-cell-sub">' . e(Carbon::parse($u->last_at)->locale('id')->translatedFormat('d M Y H:i')) . '</span>'
                : '-')
            ->addColumn('action', fn($u) => '<a href="' . route('Activity.show', self::hashId($u->id)) . '" class="pr-btn pr-btn-sm">Lihat log</a>')
            ->filterColumn('user_cell', fn($q, $k) => $q->where(fn($w) => $w->where('e.employee_name', 'like', "%{$k}%")
                ->orWhere('e.employee_pengenal', 'like', "%{$k}%")->orWhere('users.username', 'like', "%{$k}%")))
            ->orderColumn('user_cell', 'e.employee_name $1')
            ->orderColumn('total_fmt', 'a.total $1')
            ->orderColumn('failed_fmt', 'a.failed $1')
            ->orderColumn('last_fmt', 'a.last_at $1')
            ->rawColumns(['user_cell', 'failed_fmt', 'last_fmt', 'action'])
            ->make(true);
    }

    /** DataTables: daftar aktivitas (semua user, atau satu user lewat ?user=hash). */
    public function getActivities(Request $request)
    {
        $this->authorizeView();

        $query = Activity::query()
            ->with(['causer' => fn($q) => $q->select('id', 'username', 'employee_id'), 'causer.employee:id,employee_name'])
            ->select('activity_log.*');

        if ($request->filled('user')) {
            $user = $this->findUserByHash($request->user);
            abort_if(!$user, 404);
            $query->where('causer_type', User::class)->where('causer_id', $user->id);
        }
        if ($since = $this->sinceFromRange($request->input('range'))) {
            $query->where('created_at', '>=', $since);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        $query->when($request->filled('log_name'), fn($q) => $q->where('log_name', $request->log_name));
        $query->when($request->filled('event'), fn($q) => $q->where('event', $request->event));
        $query->when($request->input('causer') === 'system', fn($q) => $q->whereNull('causer_id'));

        return DataTables::of($query)
            ->addColumn('time_fmt', fn($a) => '<span class="pr-cell-name">' . e($a->created_at->locale('id')->translatedFormat('d M Y H:i')) . '</span>'
                . '<span class="pr-cell-sub">' . e($a->created_at->locale('id')->diffForHumans()) . '</span>')
            ->addColumn('causer_fmt', fn($a) => $a->causer
                ? '<a href="' . route('Activity.show', self::hashId($a->causer->id)) . '" class="act-user">' . e($a->causer->employee?->employee_name ?? $a->causer->username) . '</a>'
                : '<span class="pr-muted">Sistem</span>')
            ->addColumn('module_fmt', fn($a) => '<span class="act-module">' . e(self::MODULES[$a->log_name] ?? Str::headline($a->log_name ?? '-')) . '</span>')
            ->addColumn('event_fmt', function ($a) {
                [$label, $cls] = self::EVENTS[$a->event] ?? [Str::headline($a->event ?: 'Lainnya'), 'is-draft'];
                return '<span class="status-badge ' . $cls . '">' . e($label) . '</span>';
            })
            ->addColumn('detail_fmt', fn($a) => $this->renderDetail($a))
            ->filterColumn('detail_fmt', fn($q, $k) => $q->where('description', 'like', "%{$k}%"))
            ->filterColumn('causer_fmt', fn($q, $k) => $q->whereHasMorph('causer', [User::class], fn($u) => $u->where('username', 'like', "%{$k}%")
                ->orWhereHas('employee', fn($e) => $e->where('employee_name', 'like', "%{$k}%"))))
            ->orderColumn('time_fmt', 'created_at $1')
            ->orderColumn('module_fmt', 'log_name $1')
            ->orderColumn('event_fmt', 'event $1')
            ->rawColumns(['time_fmt', 'causer_fmt', 'module_fmt', 'event_fmt', 'detail_fmt'])
            ->make(true);
    }

    public function show(string $hashedId)
    {
        $this->authorizeView();

        $user = $this->findUserByHash($hashedId);
        abort_if(!$user, 404, 'User not found.');

        $mine = Activity::where('causer_type', User::class)->where('causer_id', $user->id);
        $week = Carbon::today()->subDays(6);

        $stats = [
            'total'       => (clone $mine)->count(),
            'week'        => (clone $mine)->where('created_at', '>=', $week)->count(),
            'first'       => (clone $mine)->min('created_at'),
            'last'        => (clone $mine)->max('created_at'),
            'last_login'  => (clone $mine)->where('event', 'login')->latest()->first(),
            'failed_week' => (clone $mine)->whereIn('event', ['login_failed', 'login_blocked'])->where('created_at', '>=', $week)->count(),
        ];
        $byModule = (clone $mine)->selectRaw('log_name, COUNT(*) AS c')->groupBy('log_name')->orderByDesc('c')->get();
        $byEvent  = (clone $mine)->selectRaw('event, COUNT(*) AS c')->groupBy('event')->orderByDesc('c')->get();

        // Aktivitas per hari, 30 hari terakhir
        $daily = (clone $mine)->where('created_at', '>=', Carbon::today()->subDays(29))
            ->selectRaw('DATE(created_at) AS d, COUNT(*) AS c')->groupBy('d')->pluck('c', 'd');
        $days = collect(range(29, 0))->map(fn($i) => Carbon::today()->subDays($i))
            ->map(fn($d) => ['date' => $d, 'count' => (int) ($daily[$d->toDateString()] ?? 0)]);

        return view('pages.Activity.show', [
            'user'     => $user,
            'hashedId' => $hashedId,
            'stats'    => $stats,
            'byModule' => $byModule,
            'byEvent'  => $byEvent,
            'days'     => $days,
            'modules'  => self::MODULES,
            'eventMap' => self::EVENTS,
        ]);
    }

    // ── Helper ────────────────────────────────────────────────────

    private function sinceFromRange(?string $range): ?Carbon
    {
        return match ($range) {
            'today' => Carbon::today(),
            '7d'    => Carbon::today()->subDays(6),
            '30d'   => Carbon::today()->subDays(29),
            '90d'   => Carbon::today()->subDays(89),
            default => null,
        };
    }

    /** Deskripsi + rincian perubahan (old → new) dari properties Spatie, semua nilai di-escape. */
    private function renderDetail(Activity $a): string
    {
        $html = '<span class="act-desc">' . e(Str::limit($a->description ?? '-', 220)) . '</span>';

        $props = $a->properties ?? collect();
        $new   = collect($props->get('attributes', []))->except(self::HIDDEN_KEYS);
        $old   = collect($props->get('old', []))->except(self::HIDDEN_KEYS);
        $fmt   = function ($v) {
            if (is_array($v)) $v = implode(', ', array_map(fn($x) => is_scalar($x) ? $x : json_encode($x), $v));
            if ($v === null || $v === '') return '–';
            if (is_bool($v)) return $v ? 'ya' : 'tidak';
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T/', $v)) {
                try {
                    $c = Carbon::parse($v)->timezone(config('app.timezone'));
                    return $c->format('H:i') === '00:00' ? $c->format('d/m/Y') : $c->format('d/m/Y H:i');
                } catch (\Throwable) {}
            }
            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return Carbon::parse($v)->format('d/m/Y');
            }
            return Str::limit((string) $v, 80);
        };

        $rows = [];
        if ($old->isNotEmpty()) {
            foreach ($new as $k => $v) {
                // Kolom yang tidak ada di "old" hanya label (mis. nama karyawan), bukan perubahan
                if (!$old->has($k)) continue;
                $ov = $old->get($k);
                if ($fmt($ov) === $fmt($v)) continue;
                $rows[] = '<li><span class="act-key">' . e(Str::headline($k)) . '</span> <del>' . e($fmt($ov)) . '</del> <ins>' . e($fmt($v)) . '</ins></li>';
            }
        } elseif ($a->event !== 'deleted' && $new->isNotEmpty() && !in_array($a->event, self::AUTH_EVENTS, true)) {
            foreach ($new->take(6) as $k => $v) {
                $rows[] = '<li><span class="act-key">' . e(Str::headline($k)) . '</span> <ins>' . e($fmt($v)) . '</ins></li>';
            }
            if ($new->count() > 6) $rows[] = '<li class="act-more">dan ' . ($new->count() - 6) . ' kolom lain</li>';
        }

        // Login: IP & perangkat
        if (in_array($a->event, self::AUTH_EVENTS, true) && $props->get('ip')) {
            $channel = $props->get('channel');
            if ($channel) {
                $rows[] = '<li><span class="act-key">Lewat</span> ' . ($channel === 'mobile' ? 'Aplikasi mobile' : 'Web') . '</li>';
            }
            $rows[] = '<li><span class="act-key">IP</span> ' . e($props->get('ip')) . '</li>';
            if ($props->get('device_name') || $props->get('device_id')) {
                $rows[] = '<li><span class="act-key">Device</span> ' . e($props->get('device_name') ?: Str::limit($props->get('device_id'), 16)) . '</li>';
            } elseif ($ua = $props->get('user_agent')) {
                $rows[] = '<li><span class="act-key">Perangkat</span> ' . e($this->shortAgent($ua)) . '</li>';
            }
            if ($props->get('registered_device')) {
                $rows[] = '<li><span class="act-key">Device terdaftar</span> ' . e($props->get('registered_device')) . '</li>';
            }
        }

        if ($rows) {
            $html .= '<ul class="act-changes">' . implode('', array_slice($rows, 0, 12)) . '</ul>';
        }

        return $html;
    }

    private function shortAgent(string $ua): string
    {
        $browser = match (true) {
            str_contains($ua, 'Edg/')     => 'Edge',
            str_contains($ua, 'Chrome/')  => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/')  => 'Safari',
            str_contains($ua, 'okhttp') || str_contains($ua, 'Dart') => 'Aplikasi mobile',
            default => 'Browser lain',
        };
        $os = match (true) {
            str_contains($ua, 'Android')   => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Windows')   => 'Windows',
            str_contains($ua, 'Mac OS')    => 'macOS',
            str_contains($ua, 'Linux')     => 'Linux',
            default => '',
        };

        return trim($browser . ($os ? ' di ' . $os : ''));
    }
}
