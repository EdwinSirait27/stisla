<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\DocumentAttachment;
use App\Models\DocumentRecipient;
use App\Models\Documents;
use App\Models\Documenttypes;
use App\Models\Employee;
use App\Models\Stores;
use App\Services\BeritaAcaraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Berita acara dengan approval bertingkat (lihat BeritaAcaraService untuk alurnya).
 *
 * Akses:
 * - Membuat & tab pribadi (perlu persetujuan saya, ajuan saya, tembusan): permission CreateBeritaAcara.
 * - Kirim ulang email / pengingat: ManageBeritaAcara.
 * - Melihat satu berita acara: pengaju, approver (semua putaran), tembusan, atau ManageBeritaAcara.
 * - Melihat semua berita acara (tab Semua): ManageBeritaAcara. Terpisah dari ManageDocument (dokumen HR).
 * - Menyetujui / mengembalikan: hanya approver yang sedang mendapat giliran.
 * - Revisi / batal: hanya pengaju.
 */
class BeritaAcaraController extends Controller
{
    private const MAX_LOCATIONS = 20;
    /** Langkah yang menunggu lebih lama dari ini dianggap tertahan (tab Semua). */
    private const STUCK_DAYS = 3;

    public function __construct(private BeritaAcaraService $service)
    {
    }

    private function me(Request $request): Employee
    {
        $employee = $request->user()?->employee;
        abort_if(!$employee, 403, 'Akun Anda belum terhubung ke data karyawan.');
        return $employee;
    }

    /** Jenis dokumen beralur approval yang aktif untuk perusahaan karyawan ini. */
    private function typesFor(Employee $employee)
    {
        return BeritaAcaraService::typesFor($employee);
    }

    /** Query dasar: hanya dokumen beralur approval. */
    private function baQuery()
    {
        return Documents::query()
            ->whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('requires_approval', true))
            ->with(['content', 'employee', 'companydocumentconfigs.documenttypes', 'companydocumentconfigs.company']);
    }

    private function canView(Request $request, Documents $doc, Employee $me): bool
    {
        return $doc->employee_id === $me->id
            || $doc->approvals()->where('employee_id', $me->id)->exists()
            || $doc->recipients()->where('employee_id', $me->id)->exists()
            || $request->user()->can('ManageBeritaAcara');
    }

    private function findBa(Request $request, string $id): array
    {
        abort_unless(preg_match('/^[0-9a-f-]{36}$/i', $id), 404);
        $doc = $this->baQuery()->findOrFail($id);
        $me = $this->me($request);
        abort_unless($this->canView($request, $doc, $me), 403, 'Anda tidak terlibat dalam berita acara ini.');
        return [$doc, $me];
    }

    public function index(Request $request)
    {
        $me = $this->me($request);
        $canCreatePerm = $request->user()->can('CreateBeritaAcara');
        $canAll = $request->user()->can('ManageBeritaAcara');

        // Tab pribadi (persetujuan, ajuan, tembusan): CreateBeritaAcara. Tab Semua: ManageBeritaAcara.
        $tabs = $canCreatePerm ? ['approval', 'mine', 'cc'] : [];
        if ($canAll) $tabs[] = 'all';
        abort_if(!$tabs, 403, 'Daftar berita acara butuh permission CreateBeritaAcara atau ManageBeritaAcara. Berita acara yang menunggu persetujuan Anda tetap bisa dibuka dari dashboard atau email.');
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : $tabs[0];

        $queries = [
            'approval' => fn() => $this->baQuery()->whereHas('approvals', fn($q) => $q->where('employee_id', $me->id)->where('status', 'pending')),
            'mine'     => fn() => $this->baQuery()->where('employee_id', $me->id),
            'cc'       => fn() => $this->baQuery()->whereHas('recipients', fn($q) => $q->where('employee_id', $me->id))->where('status', '!=', 'cancelled'),
            'all'      => fn() => $this->baQuery(),
        ];

        $counts = [
            'approval'      => $canCreatePerm ? $queries['approval']()->count() : 0,
            'mine'          => $canCreatePerm ? $queries['mine']()->count() : 0,
            'mine_revision' => $canCreatePerm ? $queries['mine']()->where('status', 'revision')->count() : 0,
            'mine_open'     => $canCreatePerm ? $queries['mine']()->whereIn('status', Documents::BA_IN_APPROVAL)->count() : 0,
            'cc_unread'     => $canCreatePerm ? DocumentRecipient::where('employee_id', $me->id)->whereNull('read_at')
                ->whereHas('document', fn($q) => $q->where('status', '!=', 'cancelled'))->count() : 0,
        ];

        // Ringkasan seluruh berita acara (tab Semua)
        $overall = null;
        if ($canAll) {
            $byStatus = $this->baQuery()->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) AS c'))->groupBy('status')->pluck('c', 'status');
            $overall = [
                'open'     => (int) (($byStatus['pending'] ?? 0) + ($byStatus['in_review'] ?? 0)),
                'revision' => (int) ($byStatus['revision'] ?? 0),
                'approved' => (int) ($byStatus['approved'] ?? 0),
                // Langkah yang menunggu lebih dari 3 hari (perlu diingatkan)
                'stuck'    => DocumentApproval::where('status', 'pending')->where('updated_at', '<', now()->subDays(self::STUCK_DAYS))
                    ->whereHas('document', fn($q) => $q->whereIn('status', Documents::BA_IN_APPROVAL))->count(),
            ];
        }

        $status = $request->query('status');
        $sort = $request->query('sort') === 'waiting' ? 'waiting' : 'status';
        $query = $queries[$tab]()
            ->when($status === 'stuck', fn($q) => $q->whereIn('status', Documents::BA_IN_APPROVAL)
                ->whereHas('approvals', fn($a) => $a->where('status', 'pending')->where('updated_at', '<', now()->subDays(self::STUCK_DAYS))))
            ->when($status && $status !== 'stuck' && array_key_exists($status, Documents::STATUS_LABELS), fn($q) => $q->where('status', $status))
            ->when($tab === 'all' && $request->filled('type'), fn($q) => $q->whereHas('companydocumentconfigs', fn($c) => $c->where('document_type_id', $request->query('type'))))
            ->when($tab === 'all' && $request->filled('company'), fn($q) => $q->whereHas('companydocumentconfigs', fn($c) => $c->where('company_id', $request->query('company'))))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . str_replace(['%', '_'], ['\%', '\_'], $request->query('q')) . '%';
                $q->where(fn($w) => $w->where('document_number', 'like', $term)
                    ->orWhereHas('content', fn($c) => $c->where('subject', 'like', $term))
                    ->orWhereHas('employee', fn($e) => $e->where('employee_name', 'like', $term)));
            });

        $sort === 'waiting'
            // Yang paling lama menunggu di atas
            ? $query->orderByRaw("FIELD(status, 'pending', 'in_review', 'revision', 'approved', 'cancelled')")
                ->orderBy(DocumentApproval::select('updated_at')->whereColumn('document_id', 'documents.id')->where('status', 'pending')->limit(1))
            : $query->orderByRaw("FIELD(status, 'revision', 'pending', 'in_review', 'approved', 'cancelled')")->orderByDesc('issued_date');

        $documents = $query->withCount(['attachments', 'recipients'])->paginate(20)->withQueryString();
        $ids = $documents->pluck('id');

        // Per dokumen: langkah yang menunggu (siapa & sejak kapan) dan kemajuan putaran berjalan
        $current = DocumentApproval::with('employee:id,employee_name')->whereIn('document_id', $ids)->where('status', 'pending')->get()->keyBy('document_id');
        $progress = DocumentApproval::whereIn('document_approvals.document_id', $ids)
            ->join('document_contents as dc', fn($j) => $j->on('dc.document_id', '=', 'document_approvals.document_id')->on('dc.round', '=', 'document_approvals.round'))
            ->select('document_approvals.document_id')
            ->selectRaw('COUNT(*) AS total')->selectRaw("SUM(document_approvals.status = 'approved') AS done")
            ->groupBy('document_approvals.document_id')->get()->keyBy('document_id');
        $unread = DocumentRecipient::where('employee_id', $me->id)->whereNull('read_at')->whereIn('document_id', $ids)->pluck('document_id')->flip();

        return view('pages.BeritaAcara.index', compact('tab', 'tabs', 'counts', 'documents', 'current', 'progress', 'unread', 'status', 'sort', 'me', 'overall') + [
            'canCreate'  => $canCreatePerm && $this->typesFor($me)->isNotEmpty(),
            'canAll'     => $canAll,
            'stuckDays'  => self::STUCK_DAYS,
            'types'      => $canAll ? Documenttypes::where('requires_approval', true)->orderBy('document_name')->pluck('document_name', 'id') : collect(),
            'companies'  => $canAll ? \App\Models\Company::orderBy('name')->pluck('name', 'id') : collect(),
        ]);
    }

    /** Kirim ulang email / pengingat (ManageBeritaAcara). */
    public function remind(Request $request, string $id)
    {
        abort_unless($request->user()->can('ManageBeritaAcara'), 403, 'Mengirim ulang email butuh permission ManageBeritaAcara.');
        [$doc, $me] = $this->findBa($request, $id);
        $data = $request->validate([
            'target' => ['required', Rule::in(['current', 'superior', 'next', 'email'])],
            'email'  => ['nullable', 'required_if:target,email', 'email:rfc', 'max:255'],
            'note'   => ['nullable', 'string', 'max:500'],
        ], [
            'target.required'  => 'Pilih penerima.',
            'email.required_if' => 'Isi alamat email tujuan.',
            'email.email'      => 'Alamat email tidak valid.',
        ]);

        $label = $this->service->remind($doc, $data['target'], $data['email'] ?? null, $data['note'] ?? null, $me);

        return back()->with('success', "Email dikirim ke {$label}. Email masuk antrian dan terkirim dalam beberapa saat.");
    }

    private function people(Employee $me)
    {
        return BeritaAcaraService::people($me);
    }

    /** Jabatan saat ini per karyawan: [employee_id => nama jabatan]. */
    private function titles(array $employeeIds): array
    {
        return \Illuminate\Support\Facades\DB::table('employees_tables as e')
            ->leftJoin('position_tables as p', 'p.id', '=', 'e.position_id')
            ->whereIn('e.id', $employeeIds ?: ['-'])->pluck('p.name', 'e.id')->all();
    }
    public function create(Request $request)
    {
        abort_unless($request->user()->can('CreateBeritaAcara'), 403);
        $me = $this->me($request);
        return view('pages.BeritaAcara.create', [
            'types'    => $this->typesFor($me),
            'preselect' => $request->query('type'),
            'people'   => $this->people($me),
            'document' => null,
        ]);
    }

    private function validated(Request $request, Employee $me, bool $isRevision = false): array
    {
        // Atasan pengaju (kalau ada) dipasang sebagai approver pertama sebelum validasi
        [$approverIds, $recipientIds] = BeritaAcaraService::withSuperiorFirst($me, (array) $request->input('approvers', []), (array) $request->input('recipients', []));
        $request->merge(['approvers' => $approverIds, 'recipients' => $recipientIds]);

        $activeIds = fn() => Employee::where('status', 'Active')->where('id', '!=', $me->id)->pluck('id')->all();
        $allowedTypes = $this->typesFor($me)->pluck('id')->all();

        $rules = [
            'subject'       => 'required|string|max:255',
            'event_at'      => 'nullable|date',
            'locations'     => 'nullable|array|max:' . self::MAX_LOCATIONS,
            'locations.*'   => ['distinct', 'uuid', Rule::exists('stores_tables', 'id')],
            'body'          => 'required|string|max:20000',
            'approvers'     => 'required|array|min:1|max:' . BeritaAcaraService::MAX_APPROVERS,
            'approvers.*'   => ['required', 'distinct', Rule::in($activeIds())],
            'recipients'    => 'nullable|array|max:' . BeritaAcaraService::MAX_RECIPIENTS,
            'recipients.*'  => ['distinct', Rule::in($activeIds()), Rule::notIn((array) $request->input('approvers', []))],
            'attachments'   => 'nullable|array|max:' . BeritaAcaraService::MAX_FILES,
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'remove_attachments'   => 'nullable|array',
            'remove_attachments.*' => 'string',
        ];
        if (!$isRevision) {
            $rules['document_type_id'] = ['required', Rule::in($allowedTypes)];
        }

        return $request->validate($rules, [
            'document_type_id.required' => 'Pilih jenis berita acara.',
            'document_type_id.in'       => 'Jenis ini belum diaktifkan untuk perusahaan Anda.',
            'subject.required'          => 'Perihal wajib diisi.',
            'body.required'             => 'Uraian wajib diisi.',
            'approvers.required'        => 'Pilih minimal satu approver.',
            'approvers.max'             => 'Maksimal ' . BeritaAcaraService::MAX_APPROVERS . ' approver, termasuk atasan Anda.',
            'approvers.*.distinct'      => 'Approver tidak boleh dipilih dua kali.',
            'approvers.*.in'            => 'Approver harus karyawan aktif dan bukan Anda sendiri.',
            'recipients.*.not_in'       => 'Tembusan tidak boleh sama dengan approver.',
            'recipients.*.in'           => 'Tembusan harus karyawan aktif dan bukan Anda sendiri.',
            'attachments.max'           => 'Maksimal ' . BeritaAcaraService::MAX_FILES . ' lampiran.',
            'attachments.*.mimes'       => 'Lampiran harus JPG, PNG, WEBP, atau PDF.',
            'attachments.*.max'         => 'Ukuran lampiran maksimal 5 MB.',
        ]);
    }

    private function contentFrom(array $v): array
    {
        $names = Stores::whereIn('id', $v['locations'] ?? [])->pluck('name', 'id')->all();

        return [
            'subject'  => trim($v['subject']),
            'event_at' => $v['event_at'] ?? null,
            // Tempat: lokasi terpilih, urut sesuai pilihan; nama digabung untuk tampilan & PDF
            'store_ids' => $storeIds = array_values($v['locations'] ?? []) ?: null,
            'location'  => $storeIds
                ? collect($storeIds)->map(fn($id) => $names[$id] ?? null)->filter()->implode(', ')
                : null,
            'body'     => trim($v['body']),
        ];
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('CreateBeritaAcara'), 403);
        $me = $this->me($request);
        $v = $this->validated($request, $me);

        $doc = $this->service->submit(
            Documenttypes::findOrFail($v['document_type_id']), $me, $this->contentFrom($v),
            $v['approvers'], $v['recipients'] ?? [], $request->file('attachments', [])
        );

        return redirect()->route('beritaacara.show', $doc->id)->with('success', "Berita acara {$doc->document_number} diajukan ke approver pertama.");
    }

    public function show(Request $request, string $id)
    {
        [$doc, $me] = $this->findBa($request, $id);
        $doc->load(['approvals.employee', 'recipients.employee', 'attachments']);

        // Tembusan membuka dokumen: tandai sudah dibaca
        DocumentRecipient::where('document_id', $doc->id)->where('employee_id', $me->id)->whereNull('read_at')->update(['read_at' => now()]);

        $round = $doc->content?->round ?? 1;
        $step = $this->service->currentStep($doc);

        return view('pages.BeritaAcara.show', [
            'titles'    => $this->titles(array_merge([$doc->employee_id], $doc->approvals->pluck('employee_id')->all(), $doc->recipients->pluck('employee_id')->all())),
            'doc'       => $doc,
            'me'        => $me,
            'steps'     => $doc->approvals->where('round', $round)->values(),
            'history'   => $doc->approvals->where('round', '<', $round)->groupBy('round'),
            'step'      => $step,
            'myTurn'    => in_array($doc->status, Documents::BA_IN_APPROVAL, true) && $step && $step->employee_id === $me->id,
            // Riwayat aktivitas dokumen ini (diajukan, disetujui/dikembalikan per langkah, dst.)
            'activities' => \Spatie\Activitylog\Models\Activity::where('subject_type', Documents::class)->where('subject_id', $doc->id)
                ->where('log_name', 'document')->latest()->latest('id')->limit(50)->get(['id', 'event', 'description', 'created_at']),
            'isOwner'   => $doc->employee_id === $me->id,
            'mySignature' => $me->signature ? route('useremployeesignature.photo', basename($me->signature)) : null,
            'canRemind'   => $request->user()->can('ManageBeritaAcara') && $doc->status !== 'cancelled',
            'reminder'    => $request->user()->can('ManageBeritaAcara') ? $this->service->reminderTargets($doc) : null,
            'waitingSince' => $step?->updated_at,
            'superior'    => $doc->employee ? BeritaAcaraService::superiorOf($doc->employee) : null,
        ]);
    }

    public function edit(Request $request, string $id)
    {
        [$doc, $me] = $this->findBa($request, $id);
        abort_unless($doc->employee_id === $me->id && $doc->status === 'revision', 403, 'Hanya pengaju yang bisa merevisi berita acara yang dikembalikan.');
        $doc->load(['approvals', 'recipients', 'attachments']);

        return view('pages.BeritaAcara.edit', [
            'document' => $doc,
            'types'    => $this->typesFor($me),
            'people'   => $this->people($me),
            'returned' => $doc->approvals->where('status', 'returned')->sortByDesc('acted_at')->first()?->load('employee'),
        ]);
    }

    public function update(Request $request, string $id)
    {
        [$doc, $me] = $this->findBa($request, $id);
        $v = $this->validated($request, $me, true);

        $this->service->resubmit($doc, $me, $this->contentFrom($v), $v['approvers'], $v['recipients'] ?? [],
            $request->file('attachments', []), $v['remove_attachments'] ?? []);

        return redirect()->route('beritaacara.show', $doc->id)->with('success', 'Berita acara diajukan ulang, persetujuan dimulai lagi dari approver pertama.');
    }

    public function approve(Request $request, string $id)
    {
        [$doc, $me] = $this->findBa($request, $id);
        $v = $request->validate(['signature' => 'nullable|string|max:2000000', 'note' => 'nullable|string|max:1000']);
        $this->service->approve($doc, $me, $v['signature'] ?? null, $v['note'] ?? null);

        $doc->refresh();
        return redirect()->route('beritaacara.show', $doc->id)->with('success',
            $doc->status === 'approved' ? 'Disetujui. Semua approver sudah menyetujui, berita acara sah.' : 'Disetujui. Berita acara diteruskan ke approver berikutnya.');
    }

    public function returnRevision(Request $request, string $id)
    {
        [$doc, $me] = $this->findBa($request, $id);
        $v = $request->validate(['note' => 'required|string|min:5|max:1000'], ['note.required' => 'Tulis alasan pengembalian.', 'note.min' => 'Alasan minimal 5 karakter.']);
        $this->service->returnForRevision($doc, $me, $v['note']);

        return redirect()->route('beritaacara.show', $doc->id)->with('success', 'Berita acara dikembalikan ke pengaju untuk direvisi.');
    }

    public function cancel(Request $request, string $id)
    {
        [$doc, $me] = $this->findBa($request, $id);
        $this->service->cancel($doc, $me);
        return redirect()->route('beritaacara.show', $doc->id)->with('success', 'Berita acara dibatalkan.');
    }

    public function attachment(Request $request, string $id, string $attachmentId)
    {
        [$doc] = $this->findBa($request, $id);
        $att = DocumentAttachment::where('document_id', $doc->id)->findOrFail($attachmentId);
        abort_unless(Storage::disk('s3')->exists($att->path), 404);

        return response(Storage::disk('s3')->get($att->path))
            ->header('Content-Type', $att->mime ?: 'application/octet-stream')
            ->header('Content-Disposition', 'inline; filename="' . addcslashes($att->original_name, '"\\') . '"')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'private, max-age=600');
    }

    /** Gambar tanda tangan satu approval (salinan saat menyetujui). */
    public function signature(Request $request, string $id, string $approvalId)
    {
        [$doc] = $this->findBa($request, $id);
        $step = DocumentApproval::where('document_id', $doc->id)->where('status', 'approved')->findOrFail($approvalId);
        abort_unless($step->signature_path && Storage::disk('s3')->exists($step->signature_path), 404);

        return response(Storage::disk('s3')->get($step->signature_path))
            ->header('Content-Type', 'image/png')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /** PDF hanya untuk berita acara yang sudah disetujui semua approver. */
    public function pdf(Request $request, string $id)
    {
        [$doc] = $this->findBa($request, $id);
        abort_unless($doc->status === 'approved', 403, 'PDF tersedia setelah semua approver menyetujui.');
        $doc->load(['approvals.employee', 'recipients.employee', 'attachments']);

        $round = $doc->content?->round ?? 1;
        // Gambar dari S3 disisipkan sebagai data URI (dompdf tidak mengambil URL yang butuh login)
        $image = function (?string $path): ?string {
            if (!$path || !Storage::disk('s3')->exists($path)) return null;
            $mime = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp'][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'image/png';
            return "data:{$mime};base64," . base64_encode(Storage::disk('s3')->get($path));
        };
        $steps = $doc->approvals->where('round', $round)->sortBy('sequence')->values()->map(function ($s) use ($image) {
            $s->signatureData = $image($s->signature_path);
            return $s;
        });
        // Pengaju: salinan saat diajukan; berita acara lama tanpa salinan memakai tanda tangan profil
        $submitterSignature = $image($doc->content?->submitter_signature_path) ?? $image($doc->employee?->signature);
        // Tautan lampiran: lewat HRX (perlu login & hak akses), bukan tautan S3 langsung
        $attachments = $doc->attachments->map(fn($a) => [
            'name' => $a->original_name,
            'url'  => route('beritaacara.attachment', [$doc->id, $a->id]),
        ]);

        $pdf = Pdf::loadView('documents.types.BA', [
            'document' => $doc,
            'content'  => $doc->content,
            'company'  => $doc->companydocumentconfigs->company,
            'type'     => $doc->companydocumentconfigs->documenttypes,
            'submitter' => $doc->employee,
            'steps'    => $steps,
            'submitterSignature' => $submitterSignature,
            'attachments' => $attachments,
            'titles'   => $this->titles(array_merge([$doc->employee_id], $steps->pluck('employee_id')->all(), $doc->recipients->pluck('employee_id')->all())),
        ])->setPaper('a4');

        return $pdf->stream(str_replace('/', '-', $doc->document_number) . '.pdf');
    }
}
