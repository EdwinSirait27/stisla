<?php

namespace App\Services;

use App\Mail\BeritaAcaraMail;
use App\Models\Companydocumentconfigs;
use App\Models\DocumentApproval;
use App\Models\DocumentAttachment;
use App\Models\DocumentContent;
use App\Models\DocumentRecipient;
use App\Models\Documents;
use App\Models\Documenttypes;
use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Alur berita acara (dan jenis dokumen lain dengan requires_approval):
 *
 *   ajukan (pending) ─► approver 1 setuju (in_review) ─► approver 2 ... ─► semua setuju ─► documents.status = approved
 *                 │ kembalikan
 *                 ▼
 *         documents.status = revision ─► pengaju memperbaiki ─► ajukan ulang (putaran baru, mulai approver 1)
 *
 * - Nomor surat diberikan saat diajukan (Documents::generateDocumentNumber) dan tetap saat revisi.
 * - Setiap approval menyimpan SALINAN tanda tangan approver saat itu, jadi dokumen tidak berubah
 *   kalau approver mengganti tanda tangan di profil.
 * - Saat diajukan ulang, langkah putaran lama yang belum/sudah disetujui ditandai voided (tanda tangan batal).
 * - Setiap langkah (diajukan, disetujui approver ke-n, dikembalikan di langkah ke-n, diajukan ulang,
 *   dibatalkan, disetujui semua) dicatat ke activity log (log_name "document") dengan aktor dan
 *   catatannya. Log otomatis model Documents dimatikan selama aksi ini supaya tidak tercatat ganda.
 * - Email (company_email) dikirim lewat queue: approver saat gilirannya, pengaju saat dikembalikan /
 *   disetujui, tembusan saat disetujui final.
 */
class BeritaAcaraService
{
    public const MAX_APPROVERS  = 10;
    public const MAX_RECIPIENTS = 20;
    public const MAX_FILES      = 5;

    public function __construct(private UploadStorage $uploads)
    {
    }

    /** Pengaturan aktif untuk perusahaan pengaju dan jenis dokumen ini. */
    /** Jenis dokumen beralur approval yang aktif untuk perusahaan karyawan ini. */
    public static function typesFor(Employee $employee)
    {
        return Documenttypes::where('requires_approval', true)->where('is_active', true)
            ->whereIn('id', fn($q) => $q->select('document_type_id')->from('company_document_configs')
                ->where('company_id', $employee->company_id)->where('is_active', true))
            ->orderBy('document_name')->get(['id', 'document_name', 'nickname']);
    }

    /**
     * Karyawan aktif untuk dipilih sebagai approver / tembusan. Jabatan & departemen dari kolom
     * position_id / department_id (tabel pivot riwayat belum lengkap untuk semua karyawan).
     */
    public static function people(Employee $me)
    {
        // Jabatan: employee->position()->first() (pivot, utamakan is_primary); kalau pivot kosong
        // pakai kolom position_id (pivot riwayat belum lengkap untuk semua karyawan).
        $pivotPosition = DB::table('employee_positions as ep')
            ->join('position_tables as pp', 'pp.id', '=', 'ep.position_id')
            ->whereColumn('ep.employee_id', 'e.id')
            ->orderByDesc('ep.is_primary')->orderBy('ep.created_at')
            ->limit(1)->select('pp.name');

        return DB::table('employees_tables as e')
            ->leftJoin('position_tables as p', 'p.id', '=', 'e.position_id')
            ->leftJoin('departments_tables as d', 'd.id', '=', 'e.department_id')
            ->where('e.status', 'Active')->whereNull('e.deleted_at')->where('e.id', '!=', $me->id)
            ->orderBy('e.employee_name')
            ->select(['e.id', 'e.employee_name as name', 'e.employee_pengenal as nip', 'p.name as position_col', 'd.department_name as department', 'e.company_email'])
            ->selectSub($pivotPosition, 'position_pivot')
            ->get()
            ->map(function ($e) {
                $position = $e->position_pivot ?: $e->position_col;
                return [
                    'id'    => $e->id,
                    'name'  => $e->name,
                    'nip'   => $e->nip,
                    'label' => $e->name . ($position ? ' - ' . $position : ''),
                    'sub'   => collect([$position, $e->department])->filter()->implode(', '),
                    'mail'  => (bool) $e->company_email, // tanpa company_email: tidak dapat email, tetap lihat di HRX
                ];
            })->values();
    }

    /**
     * Atasan langsung pengaju dari pivot employee_atasans (sama dengan form karyawan & cuti):
     * atasan utama dulu, hanya yang masih aktif. Null kalau tidak punya atasan.
     */
    public static function superiorOf(Employee $employee): ?Employee
    {
        return $employee->atasanList()
            ->where('employees_tables.status', 'Active')
            ->where('employees_tables.id', '!=', $employee->id)
            ->orderByDesc('employee_atasans.is_primary')->orderBy('employee_atasans.created_at')
            ->first();
    }

    /**
     * Kalau pengaju punya atasan, atasan wajib jadi approver pertama: dipindah/ditambahkan ke urutan 1
     * dan dikeluarkan dari tembusan. Mengembalikan [approverIds, recipientIds].
     */
    public static function withSuperiorFirst(Employee $submitter, array $approverIds, array $recipientIds): array
    {
        $approverIds  = array_values(array_unique(array_filter($approverIds, 'is_string')));
        $recipientIds = array_values(array_unique(array_filter($recipientIds, 'is_string')));

        if ($superiorId = self::superiorOf($submitter)?->id) {
            $approverIds  = array_values(array_unique([$superiorId, ...$approverIds]));
            $recipientIds = array_values(array_diff($recipientIds, [$superiorId]));
        }

        return [$approverIds, $recipientIds];
    }

    /** Dipanggil submit/resubmit: aturan atasan-pertama tetap berlaku walau form dilewati. */
    private function chain(Employee $submitter, array $approverIds, array $recipientIds): array
    {
        [$approverIds, $recipientIds] = self::withSuperiorFirst($submitter, $approverIds, $recipientIds);
        if (!$approverIds || count($approverIds) > self::MAX_APPROVERS) {
            throw ValidationException::withMessages(['approvers' => 'Approver harus 1 sampai ' . self::MAX_APPROVERS . ' orang, termasuk atasan Anda.']);
        }

        return [$approverIds, $recipientIds];
    }

    public function configFor(Employee $submitter, Documenttypes $type): ?Companydocumentconfigs
    {
        return Companydocumentconfigs::where('company_id', $submitter->company_id)
            ->where('document_type_id', $type->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @param array{subject:string,event_at:?string,location:?string,body:string} $content
     * @param string[] $approverIds  urutan sesuai pilihan pengaju
     * @param string[] $recipientIds tembusan
     * @param UploadedFile[] $files
     */
    public function submit(Documenttypes $type, Employee $submitter, array $content, array $approverIds, array $recipientIds, array $files): Documents
    {
        $config = $this->configFor($submitter, $type);
        if (!$config) {
            throw ValidationException::withMessages(['document_type_id' => 'Jenis dokumen ini belum diaktifkan untuk perusahaan Anda. Hubungi HR.']);
        }

        [$approverIds, $recipientIds] = $this->chain($submitter, $approverIds, $recipientIds);

        // Dokumen dibuat dulu supaya nomornya terbit; lampiran diunggah sesudahnya ke folder nomor itu
        // (upload ke S3 tidak dilakukan di dalam transaksi database).
        $document = activity()->withoutLogs(fn() => DB::transaction(function () use ($config, $submitter, $content, $approverIds, $recipientIds) {
            $document = Documents::create([
                'company_document_config_id' => $config->id,
                'employee_id' => $submitter->id,
                'issued_by'   => $submitter->id,
                'issued_date' => today(),
                'status'      => 'pending',
            ]);

            DocumentContent::create($content + ['document_id' => $document->id, 'round' => 1, 'submitted_at' => now()]);
            $this->createApprovals($document, $approverIds, 1, self::superiorOf($submitter)?->id);
            $this->syncRecipients($document, $recipientIds);

            return $document;
        }));

        try {
            $this->attachFiles($document, $this->storeFiles($files, $document));
        } catch (\Throwable $e) {
            // Upload gagal: batalkan ajuan seluruhnya (dokumen & file yang sempat terunggah)
            $this->purge($document);
            throw $e;
        }

        $this->snapshotSubmitterSignature($document, $submitter, 1);
        $this->record($document, 'submitted', "{$submitter->employee_name} mengajukan berita acara {$document->document_number} ke "
            . $this->chainText($approverIds), ['round' => 1, 'approvers' => $this->names($approverIds)]);
        $this->notifyCurrentApprover($document);

        return $document;
    }

    /** Ajukan ulang setelah dikembalikan: isi diperbarui, putaran baru dari approver pertama. */
    public function resubmit(Documents $document, Employee $submitter, array $content, array $approverIds, array $recipientIds, array $files, array $removeAttachmentIds): Documents
    {
        [$approverIds, $recipientIds] = $this->chain($submitter, $approverIds, $recipientIds);
        $stored = $this->storeFiles($files, $document);

        $round = activity()->withoutLogs(fn() => DB::transaction(function () use ($document, $submitter, $content, $approverIds, $recipientIds, $stored, $removeAttachmentIds) {
            $doc = Documents::whereKey($document->id)->lockForUpdate()->firstOrFail();
            if ($doc->status !== 'revision' || $doc->employee_id !== $submitter->id) {
                throw ValidationException::withMessages(['status' => 'Berita acara ini tidak sedang menunggu revisi Anda.']);
            }

            $contentRow = $doc->content()->lockForUpdate()->firstOrFail();
            $round = $contentRow->round + 1;

            // Tanda tangan putaran lama dibatalkan; yang dikembalikan tetap tercatat sebagai riwayat
            DocumentApproval::where('document_id', $doc->id)->whereIn('status', ['waiting', 'pending', 'approved'])
                ->update(['status' => 'voided', 'updated_at' => now()]);

            $contentRow->update($content + ['round' => $round, 'submitted_at' => now(), 'completed_at' => null]);
            $this->createApprovals($doc, $approverIds, $round, self::superiorOf($submitter)?->id);
            $this->syncRecipients($doc, $recipientIds);

            if ($removeAttachmentIds) {
                foreach ($doc->attachments()->whereIn('id', $removeAttachmentIds)->get() as $att) {
                    Storage::disk('s3')->delete($att->path);
                    $att->delete();
                }
            }
            $this->attachFiles($doc, $stored);

            $doc->update(['status' => 'pending']);

            return $round;
        }));

        $this->snapshotSubmitterSignature($document, $submitter, $round);
        $document->refresh();
        $this->record($document, 'resubmitted', "{$submitter->employee_name} mengajukan ulang berita acara {$document->document_number} setelah revisi (pengajuan ke-{$round}), persetujuan mulai lagi dari "
            . $this->chainText($approverIds), ['round' => $round, 'approvers' => $this->names($approverIds)]);
        $this->notifyCurrentApprover($document);

        return $document;
    }

    /** Approver menyetujui. $signatureDataUrl dipakai kalau approver belum punya tanda tangan di profil. */
    public function approve(Documents $document, Employee $approver, ?string $signatureDataUrl, ?string $note): void
    {
        // Cek giliran dulu, sebelum apa pun disimpan (termasuk tanda tangan baru)
        $this->assertTurn($document, $approver);

        // Tanda tangan baru disimpan ke profil (di luar transaksi: upload ke S3)
        if (!$approver->signature) {
            if (!$signatureDataUrl) {
                throw ValidationException::withMessages(['signature' => 'Gambar tanda tangan Anda dulu.']);
            }
            $path = $this->uploads->putSignatureDataUrl($signatureDataUrl, 'employees-signatures-photos', $approver->employee_name . '-signature', 'signature');
            $approver->forceFill(['signature' => $path])->save();
        }

        [$finished, $step, $next, $total] = activity()->withoutLogs(fn() => DB::transaction(function () use ($document, $approver, $note) {
            $doc = Documents::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $step = $this->currentStep($doc);
            if (!in_array($doc->status, Documents::BA_IN_APPROVAL, true) || !$step || $step->employee_id !== $approver->id) {
                throw ValidationException::withMessages(['status' => 'Berita acara ini tidak sedang menunggu persetujuan Anda.']);
            }

            $step->update([
                'status'         => 'approved',
                'signature_path' => $this->snapshotSignature($doc, $step, $approver),
                'note'           => $note ?: null,
                'acted_at'       => now(),
            ]);

            $next = DocumentApproval::where('document_id', $doc->id)->where('round', $step->round)
                ->where('status', 'waiting')->orderBy('sequence')->first();
            $total = DocumentApproval::where('document_id', $doc->id)->where('round', $step->round)->count();

            if ($next) {
                $next->update(['status' => 'pending']);
                // Sudah ada yang menyetujui, masih menunggu approver berikutnya
                $doc->update(['status' => 'in_review']);
                return [false, $step, $next, $total];
            }

            $doc->update(['status' => 'approved']);
            $doc->content()->update(['completed_at' => now()]);
            return [true, $step, null, $total];
        }));

        $document->refresh();
        $who = $approver->employee_name . ($this->isSuperior($document, $approver) ? ' (atasan pengaju)' : '');
        $this->record($document, 'step_approved',
            "{$who} menyetujui berita acara {$document->document_number} sebagai approver ke-{$step->sequence} dari {$total}"
                . ($next ? ', diteruskan ke ' . ($next->employee?->employee_name ?? 'approver berikutnya') : '')
                . ($note ? ". Catatan: {$note}" : ''),
            ['round' => $step->round, 'sequence' => $step->sequence, 'total' => $total, 'approver' => $approver->employee_name, 'note' => $note ?: null, 'next' => $next?->employee?->employee_name]);
        if ($finished) {
            $this->record($document, 'approved', "Berita acara {$document->document_number} disetujui semua approver ({$total} orang) dan sah", ['round' => $step->round, 'total' => $total]);
        }
        $finished ? $this->notifyApproved($document) : $this->notifyCurrentApprover($document);
    }

    /** Approver mengembalikan untuk revisi (alasan wajib). */
    public function returnForRevision(Documents $document, Employee $approver, string $note): void
    {
        [$step, $total] = activity()->withoutLogs(fn() => DB::transaction(function () use ($document, $approver, $note) {
            $doc = Documents::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $step = $this->currentStep($doc);
            if (!in_array($doc->status, Documents::BA_IN_APPROVAL, true) || !$step || $step->employee_id !== $approver->id) {
                throw ValidationException::withMessages(['status' => 'Berita acara ini tidak sedang menunggu persetujuan Anda.']);
            }

            $total = DocumentApproval::where('document_id', $doc->id)->where('round', $step->round)->count();
            $step->update(['status' => 'returned', 'note' => $note, 'acted_at' => now()]);
            DocumentApproval::where('document_id', $doc->id)->where('round', $step->round)->where('status', 'waiting')
                ->update(['status' => 'voided', 'updated_at' => now()]);
            $doc->update(['status' => 'revision']);

            return [$step, $total];
        }));

        $document->refresh();
        $who = $approver->employee_name . ($this->isSuperior($document, $approver) ? ' (atasan pengaju)' : '');
        $this->record($document, 'returned',
            "{$who} mengembalikan berita acara {$document->document_number} untuk revisi di langkah ke-{$step->sequence} dari {$total}. Alasan: {$note}",
            ['round' => $step->round, 'sequence' => $step->sequence, 'total' => $total, 'approver' => $approver->employee_name, 'note' => $note]);
        $this->mail($document->employee, 'returned', $document, $note);
    }

    /** Pengaju membatalkan (selama belum disetujui final). */
    public function cancel(Documents $document, Employee $submitter): void
    {
        $from = activity()->withoutLogs(fn() => DB::transaction(function () use ($document, $submitter) {
            $doc = Documents::whereKey($document->id)->lockForUpdate()->firstOrFail();
            if (!in_array($doc->status, [...Documents::BA_IN_APPROVAL, 'revision'], true) || $doc->employee_id !== $submitter->id) {
                throw ValidationException::withMessages(['status' => 'Berita acara ini tidak bisa dibatalkan.']);
            }
            $from = $doc->status_label;
            DocumentApproval::where('document_id', $doc->id)->whereIn('status', ['waiting', 'pending'])
                ->update(['status' => 'voided', 'updated_at' => now()]);
            $doc->update(['status' => 'cancelled']);

            return $from;
        }));

        $document->refresh();
        $this->record($document, 'cancelled', "{$submitter->employee_name} membatalkan berita acara {$document->document_number} (sebelumnya: " . strtolower($from) . ')');
    }

    /** Catat ke activity log (log_name "document"); gagal mencatat tidak membatalkan aksinya. */
    private function record(Documents $document, string $event, string $description, array $properties = []): void
    {
        try {
            activity('document')
                ->performedOn($document)
                ->causedBy(auth()->user())
                ->event($event)
                ->withProperties($properties + ['document_number' => $document->document_number, 'status' => $document->status])
                ->log($description);
        } catch (\Throwable $e) {
            Log::warning('Activity log berita acara gagal', ['document_id' => $document->id, 'event' => $event, 'error' => $e->getMessage()]);
        }
    }

    /** Apakah approver ini atasan pengaju (dari pivot employee_atasans). */
    private function isSuperior(Documents $document, Employee $approver): bool
    {
        return $document->employee && self::superiorOf($document->employee)?->id === $approver->id;
    }

    private function names(array $employeeIds): array
    {
        $names = Employee::whereIn('id', $employeeIds)->pluck('employee_name', 'id');
        return array_values(array_map(fn($id) => $names[$id] ?? '-', $employeeIds));
    }

    /** "1. Nama, 2. Nama" untuk deskripsi log. */
    private function chainText(array $employeeIds): string
    {
        return collect($this->names($employeeIds))->map(fn($n, $i) => ($i + 1) . ". {$n}")->implode(', ');
    }

    private function assertTurn(Documents $document, Employee $approver): void
    {
        $step = $this->currentStep($document);
        if (!in_array($document->status, Documents::BA_IN_APPROVAL, true) || !$step || $step->employee_id !== $approver->id) {
            throw ValidationException::withMessages(['status' => 'Berita acara ini tidak sedang menunggu persetujuan Anda.']);
        }
    }

    /** Langkah yang sedang menunggu persetujuan (putaran terakhir). */
    /**
     * Pengingat untuk dashboard: berita acara yang menunggu tanda tangan saya (giliran saya), yang
     * akan sampai ke saya, tembusan yang belum saya buka, dan ajuan saya yang dikembalikan.
     */
    public static function reminders(Employee $me, int $limit = 5): array
    {
        $myTurn = DocumentApproval::where('employee_id', $me->id)->where('status', 'pending')
            ->whereHas('document', fn($q) => $q->whereIn('status', Documents::BA_IN_APPROVAL));
        $cc = DocumentRecipient::where('employee_id', $me->id)->whereNull('read_at')
            ->whereHas('document', fn($q) => $q->whereNotIn('status', ['cancelled']));
        $upcoming = DocumentApproval::where('employee_id', $me->id)->where('status', 'waiting')
            ->whereHas('document', fn($q) => $q->whereIn('status', Documents::BA_IN_APPROVAL));
        $revisions = Documents::where('employee_id', $me->id)->where('status', 'revision')
            ->whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('requires_approval', true));
        // Ajuan saya yang masih berjalan (menunggu / disetujui sebagian)
        $mine = Documents::where('employee_id', $me->id)->whereIn('status', Documents::BA_IN_APPROVAL)
            ->whereHas('companydocumentconfigs.documenttypes', fn($q) => $q->where('requires_approval', true));

        $with = ['document.content', 'document.employee', 'document.companydocumentconfigs.documenttypes'];

        return [
            'turn'            => (clone $myTurn)->with($with)->orderBy('updated_at')->limit($limit)->get(),
            'turn_count'      => (clone $myTurn)->count(),
            'cc'              => (clone $cc)->with($with)->latest()->limit($limit)->get(),
            'cc_count'        => (clone $cc)->count(),
            'upcoming_count'  => $upcoming->count(),
            'revision_count'  => $revisions->count(),
            'mine'            => (clone $mine)->with(['content', 'companydocumentconfigs.documenttypes', 'approvals.employee:id,employee_name'])
                ->orderByDesc('issued_date')->limit($limit)->get(),
            'mine_count'      => (clone $mine)->count(),
        ];
    }

    public function currentStep(Documents $document): ?DocumentApproval
    {
        return DocumentApproval::where('document_id', $document->id)->where('status', 'pending')
            ->orderByDesc('round')->orderBy('sequence')->first();
    }

    // ── internal ─────────────────────────────────────────────────────────

    private function createApprovals(Documents $document, array $approverIds, int $round, ?string $superiorId = null): void
    {
        foreach (array_values($approverIds) as $i => $employeeId) {
            DocumentApproval::create([
                'document_id' => $document->id,
                'employee_id' => $employeeId,
                'sequence'    => $i + 1,
                // Atasan pengaju selalu langkah pertama: tampil "Mengetahui" di PDF
                'is_superior' => $i === 0 && $superiorId === $employeeId,
                'round'       => $round,
                'status'      => $i === 0 ? 'pending' : 'waiting',
            ]);
        }
    }

    private function syncRecipients(Documents $document, array $recipientIds): void
    {
        $document->recipients()->whereNotIn('employee_id', $recipientIds ?: ['-'])->delete();
        foreach (array_unique($recipientIds) as $employeeId) {
            DocumentRecipient::firstOrCreate(['document_id' => $document->id, 'employee_id' => $employeeId]);
        }
    }

    /**
     * Folder S3 satu berita acara: documents/berita-acara/{nomor}/ dengan subfolder lampiran/ dan
     * tanda-tangan/. Garis miring di nomor (004/BA-ABD/XI/2026) diganti "-" karena di S3 berarti subfolder.
     */
    public static function folder(Documents $document): string
    {
        $number = trim(preg_replace('/[^A-Za-z0-9-]+/', '-', (string) $document->document_number), '-') ?: $document->id;
        return 'documents/berita-acara/' . $number;
    }

    /** Hapus ajuan yang gagal di tengah jalan (dokumen, relasi, dan file di foldernya). */
    private function purge(Documents $document): void
    {
        try {
            Storage::disk('s3')->deleteDirectory(self::folder($document));
        } catch (\Throwable $e) {
            report($e);
        }
        DB::transaction(function () use ($document) {
            DocumentAttachment::where('document_id', $document->id)->delete();
            DocumentRecipient::where('document_id', $document->id)->delete();
            DocumentApproval::where('document_id', $document->id)->delete();
            DocumentContent::where('document_id', $document->id)->delete();
            activity()->withoutLogs(fn() => Documents::whereKey($document->id)->delete());
        });
    }

    /** Upload ke folder lampiran berita acara; foto dikompres & dibersihkan oleh UploadStorage. */
    private function storeFiles(array $files, Documents $document): array
    {
        $out = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;
            $path = $this->uploads->put($file, self::folder($document) . '/lampiran', 'lampiran',
                array_merge(UploadStorage::IMAGES, ['pdf']), 's3', false, 'attachments');
            // Jenis & ukuran dari file yang tersimpan (foto sudah dikompres, bisa berubah format), bukan file asli
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $out[] = [
                'path' => $path,
                'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 200),
                'mime' => ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'][$ext] ?? $file->getMimeType(),
                'size' => (int) (Storage::disk('s3')->size($path) ?: $file->getSize()),
            ];
        }
        return $out;
    }

    private function attachFiles(Documents $document, array $stored): void
    {
        foreach ($stored as $s) {
            DocumentAttachment::create($s + ['document_id' => $document->id]);
        }
    }

    /** Salin tanda tangan profil ke folder dokumen, supaya tidak berubah kalau profil diganti. */
    /**
     * Salinan tanda tangan pengaju saat (ulang) diajukan, disimpan di isi berita acara. Tanpa tanda
     * tangan di profil: dibiarkan kosong, PDF memakai tanda tangan profil terbaru kalau nanti ada.
     */
    private function snapshotSubmitterSignature(Documents $doc, Employee $submitter, int $round): void
    {
        $path = $this->copySignature($submitter->signature, self::folder($doc) . "/tanda-tangan/pengaju-putaran-{$round}.png");
        if ($path) {
            DocumentContent::where('document_id', $doc->id)->update(['submitter_signature_path' => $path]);
        }
    }

    private function snapshotSignature(Documents $doc, DocumentApproval $step, Employee $approver): ?string
    {
        return $this->copySignature($approver->signature, self::folder($doc) . "/tanda-tangan/approver-putaran-{$step->round}-ke-{$step->sequence}.png");
    }

    private function copySignature(?string $source, string $target): ?string
    {
        if (!$source) return null;
        $disk = Storage::disk('s3');
        try {
            if ($disk->exists($source)) {
                $disk->copy($source, $target);
                return $target;
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $source; // cadangan: pakai path profil
    }

    /**
     * Penerima yang bisa diberi pengingat (untuk ManageBeritaAcara), hanya selama masih berjalan:
     * current = approver yang sedang mendapat giliran, superior = atasan pengaju,
     * next = approver sesudah yang sekarang. Nilai: [Employee|null, alasan tidak bisa|null].
     */
    public function reminderTargets(Documents $document): array
    {
        $open = in_array($document->status, Documents::BA_IN_APPROVAL, true);
        $step = $open ? $this->currentStep($document)?->loadMissing('employee') : null;
        $next = $step ? DocumentApproval::with('employee')->where('document_id', $document->id)->where('round', $step->round)
            ->where('status', 'waiting')->where('sequence', '>', $step->sequence)->orderBy('sequence')->first() : null;
        $superior = $document->employee ? self::superiorOf($document->employee) : null;

        $check = function (?Employee $e, string $missing) {
            if (!$e) return [null, $missing];
            if (!filter_var(trim((string) $e->company_email), FILTER_VALIDATE_EMAIL)) return [$e, 'tidak punya email kantor'];
            return [$e, null];
        };

        return [
            'current'  => $open ? $check($step?->employee, 'tidak ada langkah yang menunggu') : [null, 'berita acara tidak sedang menunggu persetujuan'],
            'superior' => $open ? $check($superior, 'pengaju tidak punya atasan') : [null, 'berita acara tidak sedang menunggu persetujuan'],
            'next'     => $open ? $check($next?->employee, 'tidak ada approver sesudahnya') : [null, 'berita acara tidak sedang menunggu persetujuan'],
            'step'     => $step,
        ];
    }

    /**
     * Kirim ulang email (pengingat) oleh pemegang ManageBeritaAcara. Mengembalikan alamat tujuan.
     * Approver yang sedang mendapat giliran menerima email "perlu persetujuan" seperti biasa; penerima
     * lain menerima email pengingat yang menyebut siapa yang sedang ditunggu.
     */
    public function remind(Documents $document, string $target, ?string $email, ?string $note, Employee $actor): string
    {
        if ($document->status === 'cancelled') {
            throw ValidationException::withMessages(['target' => 'Berita acara yang dibatalkan tidak bisa dikirimi pengingat.']);
        }

        if ($target === 'email') {
            $address = strtolower(trim((string) $email));
            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['email' => 'Alamat email tidak valid.']);
            }
            $name = 'Bapak/Ibu';
            $event = in_array($document->status, Documents::BA_IN_APPROVAL, true) ? 'reminder' : 'shared';
        } else {
            $targets = $this->reminderTargets($document);
            [$employee, $problem] = $targets[$target] ?? [null, 'pilihan tidak dikenal'];
            if ($problem) {
                $who = ['current' => 'Approver yang sedang mendapat giliran', 'superior' => 'Atasan pengaju', 'next' => 'Approver berikutnya'][$target] ?? 'Penerima';
                throw ValidationException::withMessages(['target' => "{$who}" . ($employee ? " ({$employee->employee_name})" : '') . ": {$problem}."]);
            }
            $address = trim($employee->company_email);
            $name = $employee->employee_name;
            $event = $target === 'current' ? 'approval_needed' : 'reminder';
        }

        Mail::to($address)->queue(new BeritaAcaraMail($document->id, $name, $event, $note ?: null));
        $label = $target === 'email' ? $address : "{$name} ({$address})";
        $this->record($document, 'reminded', "{$actor->employee_name} mengirim ulang email berita acara {$document->document_number} ke {$label}" . ($note ? ". Pesan: {$note}" : ''),
            ['target' => $target, 'email' => $address]);

        return $label;
    }

    private function notifyCurrentApprover(Documents $document): void
    {
        $step = $this->currentStep($document);
        if (!$step) return;
        $step->loadMissing('employee');
        $this->mail($step->employee, 'approval_needed', $document);
        $step->update(['notified_at' => now()]);
    }

    private function notifyApproved(Documents $document): void
    {
        $this->mail($document->employee, 'approved', $document);
        foreach ($document->recipients()->with('employee')->get() as $r) {
            $this->mail($r->employee, 'cc', $document);
            $r->update(['notified_at' => now()]);
        }
    }

    private function mail(?Employee $to, string $event, Documents $document, ?string $note = null): void
    {
        $email = trim((string) $to?->company_email);
        if (!$to || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::info('BeritaAcara: email dilewati, company_email kosong', ['employee_id' => $to?->id, 'event' => $event, 'document_id' => $document->id]);
            return;
        }
        try {
            Mail::to($email)->queue(new BeritaAcaraMail($document->id, $to->employee_name, $event, $note));
        } catch (\Throwable $e) {
            report($e); // email gagal tidak boleh menggagalkan alur approval
        }
    }
}
