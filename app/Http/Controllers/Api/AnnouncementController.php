<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Pengumuman untuk aplikasi mobile (hanya baca). Pengumuman dibuat HR di web (DashboardHRController).
 *
 * - Yang tampil hanya yang sudah terbit (publish_date <= hari ini). Default hanya yang masih berlaku
 *   (end_date kosong atau >= hari ini), sama seperti dashboard karyawan di web; scope=all ikut
 *   menampilkan yang sudah berakhir.
 * - Isi pengumuman berupa HTML dari editor teks. Aplikasi tidak pernah menerima HTML: isinya diubah
 *   di sini menjadi blok teks biasa (paragraf, judul, poin daftar) plus daftar tautan http/https,
 *   sehingga tidak ada skrip, gaya, atau tag yang bisa ikut terbawa.
 */
class AnnouncementController extends Controller
{
    private const PER_PAGE  = 20;
    private const NEW_DAYS  = 3;   // terbit dalam sekian hari terakhir = "Baru"
    private const EXCERPT   = 160;

    private function published(string $scope): Builder
    {
        $today = today()->toDateString();

        return Announcement::query()
            ->whereNotNull('publish_date')
            ->whereDate('publish_date', '<=', $today)
            ->when($scope === 'active', fn($q) => $q->where(fn($w) => $w->whereNull('end_date')->orWhereDate('end_date', '>=', $today)));
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page'  => 'nullable|integer|min:1|max:1000',
            'scope' => 'nullable|in:active,all',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Filter tidak valid', 'errors' => $validator->errors()], 422);
        }

        $scope = $request->input('scope', 'active');
        $page  = (int) $request->input('page', 1);

        $query = $this->published($scope)->with('user.Employee');
        $total = (clone $query)->count();
        $rows  = $query->orderByDesc('publish_date')->orderByDesc('created_at')
            ->skip(($page - 1) * self::PER_PAGE)->take(self::PER_PAGE + 1)->get();

        $items = $rows->take(self::PER_PAGE)->map(fn(Announcement $a) => $this->summary($a))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'scope'        => $scope,
                'items'        => $items,
                'page'         => $page,
                'has_more'     => $rows->count() > self::PER_PAGE,
                'total'        => $total,
                'active_count' => $scope === 'active' ? $total : $this->published('active')->count(),
                'new_count'    => $this->published('active')->whereDate('publish_date', '>=', today()->subDays(self::NEW_DAYS - 1))->count(),
            ],
        ]);
    }

    public function show(Request $request, string $id)
    {
        $announcement = $this->published('all')->with('user.Employee')->find($id);
        if (!$announcement) {
            return response()->json(['success' => false, 'message' => 'Pengumuman tidak ditemukan atau belum terbit', 'code' => 'NOT_FOUND'], 404);
        }

        [$blocks, $links] = $this->toBlocks((string) $announcement->content);

        return response()->json([
            'success' => true,
            'data' => $this->summary($announcement) + [
                'blocks' => $blocks,
                'links'  => $links,
            ],
        ]);
    }

    private function summary(Announcement $a): array
    {
        $tz      = config('app.timezone');
        $publish = $a->publish_date ? Carbon::parse($a->publish_date, $tz) : null;
        $end     = $a->end_date ? Carbon::parse($a->end_date, $tz) : null;
        $today   = today();

        return [
            'id'            => $a->id,
            'title'         => $this->clean($a->title ?: 'Tanpa judul', 255),
            'excerpt'       => Str::limit($this->plainText((string) $a->content), self::EXCERPT),
            'publish_date'  => $publish?->toDateString(),
            'publish_label' => $publish?->locale('id')->translatedFormat('d F Y'),
            'end_date'      => $end?->toDateString(),
            'end_label'     => $end?->locale('id')->translatedFormat('d F Y'),
            'state'         => $end && $end->lt($today) ? 'ended' : 'active',
            'is_new'        => $publish && $publish->gte($today->copy()->subDays(self::NEW_DAYS - 1)),
            'author'        => $this->clean($a->user?->Employee?->employee_name ?? '', 120) ?: null,
        ];
    }

    /** Teks satu baris tanpa tag, entitas, karakter kontrol, atau spasi berlebih. */
    private function clean(string $value, int $max): string
    {
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x{0000}-\x{0008}\x{000B}\x{000C}\x{000E}-\x{001F}\x{007F}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}\x{FEFF}]/u', '', $text) ?? '';
        $text = preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $text)) ?? '';

        return Str::limit(trim($text), $max, '');
    }

    private function plainText(string $html): string
    {
        return $this->clean(preg_replace('/<(br|\/p|\/li|\/h[1-6]|\/div)[^>]*>/i', ' ', $html) ?? '', 2000);
    }

    /**
     * HTML editor → [blocks, links].
     * blocks: [{type: 'heading'|'paragraph'|'item', text, marker?}] — marker "1." / "•" untuk poin daftar.
     * Baris baru (<br>) di dalam paragraf dipertahankan sebagai "\n".
     */
    private function toBlocks(string $html): array
    {
        $blocks = [];
        $links  = [];
        if (trim($html) === '') {
            return [$blocks, $links];
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><body>' . $html . '</body>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        // Tag yang isinya tidak boleh ikut sama sekali
        foreach (['script', 'style', 'iframe', 'object', 'embed', 'noscript', 'template'] as $tag) {
            foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        // Tautan: hanya http/https, maksimal 20
        foreach ($dom->getElementsByTagName('a') as $a) {
            $href = trim((string) $a->getAttribute('href'));
            if (count($links) < 20 && preg_match('#^https?://#i', $href) && filter_var($href, FILTER_VALIDATE_URL)) {
                $links[$href] = ['url' => $href, 'label' => $this->clean($a->textContent, 120) ?: $href];
            }
        }

        $text = function (\DOMNode $node) use (&$text): string {
            $out = '';
            foreach ($node->childNodes as $child) {
                if ($child instanceof \DOMText) {
                    $out .= $child->data;
                } elseif ($child instanceof \DOMElement) {
                    $name = strtolower($child->tagName);
                    if ($name === 'br') $out .= "\n";
                    elseif (!in_array($name, ['ul', 'ol'], true)) $out .= $text($child);
                }
            }
            return $out;
        };
        $push = function (string $type, string $raw, ?string $marker = null) use (&$blocks) {
            // Rapikan per baris, pertahankan baris baru dari <br>
            $lines = array_map(fn($l) => $this->clean($l, 2000), explode("\n", $raw));
            $value = trim(implode("\n", $lines));
            $value = preg_replace("/\n{3,}/", "\n\n", $value) ?? $value;
            if ($value === '' || count($blocks) >= 300) return;
            $blocks[] = array_filter(['type' => $type, 'text' => $value, 'marker' => $marker], fn($v) => $v !== null);
        };

        // Daftar (ol/ul), termasuk daftar bertingkat di dalam poin
        $list = function (\DOMElement $listNode) use (&$list, $text, $push) {
            $ordered = strtolower($listNode->tagName) === 'ol';
            $n = max(1, (int) ($listNode->getAttribute('start') ?: 1));
            foreach ($listNode->childNodes as $li) {
                if (!$li instanceof \DOMElement || strtolower($li->tagName) !== 'li') continue;
                $push('item', $text($li), $ordered ? ($n++) . '.' : '•');
                foreach ($li->childNodes as $sub) {
                    if ($sub instanceof \DOMElement && in_array(strtolower($sub->tagName), ['ul', 'ol'], true)) $list($sub);
                }
            }
        };

        $walk = function (\DOMNode $node) use (&$walk, $list, $text, $push) {
            foreach ($node->childNodes as $child) {
                if ($child instanceof \DOMText) {
                    $push('paragraph', $child->data);
                    continue;
                }
                if (!$child instanceof \DOMElement) continue;

                $name = strtolower($child->tagName);
                if (preg_match('/^h[1-6]$/', $name)) {
                    $push('heading', $text($child));
                } elseif ($name === 'ol' || $name === 'ul') {
                    $list($child);
                } elseif (in_array($name, ['div', 'section', 'article', 'blockquote', 'table', 'tbody', 'thead', 'tr'], true)) {
                    $walk($child);
                } elseif ($name === 'img' || $name === 'hr') {
                    continue;
                } else {
                    // p, td, span, strong, a, dll.
                    $push('paragraph', $text($child));
                }
            }
        };

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) $walk($body);

        // URL yang ditulis sebagai teks biasa (bukan tautan) juga dijadikan tautan
        foreach ($blocks as $block) {
            preg_match_all('#https?://[^\s<>"\']+#i', $block['text'], $m);
            foreach ($m[0] as $url) {
                $url = rtrim($url, '.,;:)!?');
                if (count($links) < 20 && !isset($links[$url]) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $links[$url] = ['url' => $url, 'label' => parse_url($url, PHP_URL_HOST) ?: $url];
                }
            }
        }

        return [$blocks, array_values($links)];
    }
}
