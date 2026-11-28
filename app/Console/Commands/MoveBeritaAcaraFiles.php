<?php

namespace App\Console\Commands;

use App\Models\DocumentApproval;
use App\Models\DocumentAttachment;
use App\Models\DocumentContent;
use App\Models\Documents;
use App\Services\BeritaAcaraService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Pindahkan file berita acara lama ke struktur per nomor dokumen:
 *   documents/berita-acara/{nomor}/lampiran/…
 *   documents/berita-acara/{nomor}/tanda-tangan/pengaju-putaran-{n}.png
 *   documents/berita-acara/{nomor}/tanda-tangan/approver-putaran-{n}-ke-{urutan}.png
 *
 * Aman diulang: file yang sudah di folder tujuan dilewati. Path di database baru diganti setelah
 * file berhasil disalin; file lama dihapus sesudahnya. Tanda tangan yang masih menunjuk ke foto
 * profil (cadangan lama) disalin, bukan dipindah, supaya tanda tangan profil tidak terhapus.
 */
class MoveBeritaAcaraFiles extends Command
{
    protected $signature = 'berita-acara:move-files {--dry-run : Tampilkan rencana tanpa memindahkan}';
    protected $description = 'Pindahkan lampiran & tanda tangan berita acara lama ke folder per nomor dokumen';

    private int $moved = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function handle()
    {
        $dry = (bool) $this->option('dry-run');
        $disk = Storage::disk('s3');

        $docs = Documents::whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('requires_approval', true))->get();
        foreach ($docs as $doc) {
            $folder = BeritaAcaraService::folder($doc);

            foreach (DocumentAttachment::where('document_id', $doc->id)->get() as $a) {
                $this->relocate($disk, $dry, $a->path, $folder . '/lampiran/' . basename($a->path), fn($p) => $a->update(['path' => $p]));
            }
            foreach (DocumentApproval::where('document_id', $doc->id)->whereNotNull('signature_path')->get() as $s) {
                $this->relocate($disk, $dry, $s->signature_path, "{$folder}/tanda-tangan/approver-putaran-{$s->round}-ke-{$s->sequence}.png", fn($p) => $s->update(['signature_path' => $p]));
            }
            $c = DocumentContent::where('document_id', $doc->id)->whereNotNull('submitter_signature_path')->first();
            if ($c) {
                $this->relocate($disk, $dry, $c->submitter_signature_path, "{$folder}/tanda-tangan/pengaju-putaran-{$c->round}.png", fn($p) => $c->update(['submitter_signature_path' => $p]));
            }
        }

        // Folder tanda tangan lama yang sudah kosong
        if (!$dry) {
            foreach ($disk->directories('documents/signatures') as $dir) {
                if (!$disk->allFiles($dir)) $disk->deleteDirectory($dir);
            }
        }

        $this->info(($dry ? '[dry-run] ' : '') . "Dipindah: {$this->moved}, dilewati: {$this->skipped}, gagal: {$this->failed}");
        return $this->failed ? self::FAILURE : self::SUCCESS;
    }

    private function relocate($disk, bool $dry, string $from, string $to, callable $save): void
    {
        if ($from === $to || str_starts_with($from, dirname($to) . '/')) {
            $this->skipped++;
            return;
        }
        // Hanya file milik berita acara yang dipindah; file profil (cadangan tanda tangan) disalin saja
        $owned = str_starts_with($from, 'documents/berita-acara/') || str_starts_with($from, 'documents/signatures/');

        $this->line(($dry ? '  [rencana] ' : '  ') . "{$from}  →  {$to}" . ($owned ? '' : '  (disalin dari profil)'));
        if ($dry) {
            $this->moved++;
            return;
        }

        try {
            if (!$disk->exists($from)) {
                $this->warn("    file asal tidak ada di S3, dilewati");
                $this->failed++;
                return;
            }
            $disk->copy($from, $to);
            $save($to);
            if ($owned) $disk->delete($from);
            $this->moved++;
        } catch (Throwable $e) {
            $this->error("    gagal: {$e->getMessage()}");
            $this->failed++;
        }
    }
}
