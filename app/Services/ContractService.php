<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Approval bertingkat kontrak (sama seperti SK):
 *   Draft (HeadHR menyusun & bisa mengubah)
 *     → HeadHR mengajukan: approver_1 = HeadHR, status Pending, menunggu approver_2
 *     → approver_2 menyetujui → approver_3 (kalau dipilih) → disetujui penuh: status Active
 *       (atau Expired kalau tanggal berakhir sudah lewat).
 *   Approver yang sedang mendapat giliran bisa mengembalikan ke Draft (alasan wajib).
 *   HeadHR bisa membatalkan selama belum berlaku.
 * Satu karyawan hanya boleh punya satu kontrak terbuka (Draft / Pending / Active).
 */
class ContractService
{
    public function submit(Contract $contract, Employee $hr): Contract
    {
        DB::transaction(function () use ($contract, $hr) {
            $c = Contract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
            if ($c->contract_status !== 'Draft') {
                throw ValidationException::withMessages(['status' => 'Kontrak ini sudah diajukan.']);
            }
            if (!$c->approver_2) {
                throw ValidationException::withMessages(['approver_2' => 'Pilih approver 2 dulu lewat Ubah.']);
            }
            if (in_array($hr->id, array_filter([$c->approver_2, $c->approver_3]), true)) {
                throw ValidationException::withMessages(['approver_2' => 'Pengaju tidak boleh menjadi approver 2 atau 3.']);
            }
            if ($c->employee_id === $hr->id) {
                throw ValidationException::withMessages(['status' => 'Anda tidak bisa mengajukan kontrak untuk diri sendiri.']);
            }

            $c->update([
                'contract_status'      => 'Pending',
                'approver_1'           => $hr->id,
                'approver_1_at'        => now(),
                'approver_1_signature' => $this->snapshotSignature($c, $hr, 1),
                'approver_2_at'        => null,
                'approver_3_at'        => null,
                'returned_note'        => null,
                'returned_by'          => null,
                'returned_at'          => null,
            ]);
        });

        return $contract->fresh();
    }

    public function approve(Contract $contract, Employee $approver): Contract
    {
        DB::transaction(function () use ($contract, $approver) {
            $c = Contract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
            $step = $c->currentStep();
            if (!$step || $c->{$step[0]} !== $approver->id) {
                throw ValidationException::withMessages(['status' => 'Kontrak ini tidak sedang menunggu persetujuan Anda.']);
            }

            $n = $step[0] === 'approver_2' ? 2 : 3;
            $final = $n === 3 || !$c->approver_3;
            $values = [$step[1] => now(), "approver_{$n}_signature" => $this->snapshotSignature($c, $approver, $n)];

            if ($final) {
                // Satu kontrak berlaku per karyawan
                $active = Contract::where('employee_id', $c->employee_id)->where('contract_status', 'Active')
                    ->where('id', '!=', $c->id)->lockForUpdate()->first();
                if ($active) {
                    throw ValidationException::withMessages(['status' => 'Karyawan ini masih punya kontrak berlaku (sampai ' . ($active->end_date?->format('d/m/Y') ?? 'tanpa batas') . '). Minta HR mengakhirinya dulu.']);
                }
                $values += [
                    'contract_status' => $c->end_date && Carbon::parse($c->end_date)->lt(today()) ? 'Expired' : 'Active',
                    'approved_at'     => now(),
                ];
            }

            $c->update($values);
        });

        return $contract->fresh();
    }

    public function returnToDraft(Contract $contract, Employee $approver, string $note): Contract
    {
        DB::transaction(function () use ($contract, $approver, $note) {
            $c = Contract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
            if (!$c->currentStep() || $c->currentApproverId() !== $approver->id) {
                throw ValidationException::withMessages(['status' => 'Kontrak ini tidak sedang menunggu persetujuan Anda.']);
            }
            $c->update([
                'contract_status' => 'Draft',
                'approver_1_at'   => null,
                'approver_2_at'   => null,
                'approver_3_at'   => null,
                'returned_note'   => $note,
                'returned_by'     => $approver->id,
                'returned_at'     => now(),
            ]);
        });

        return $contract->fresh();
    }

    public function cancel(Contract $contract): Contract
    {
        DB::transaction(function () use ($contract) {
            $c = Contract::whereKey($contract->id)->lockForUpdate()->firstOrFail();
            if (!in_array($c->contract_status, ['Draft', 'Pending'], true)) {
                throw ValidationException::withMessages(['status' => 'Hanya kontrak Draft atau yang menunggu persetujuan yang bisa dibatalkan. Kontrak berlaku diakhiri lewat "Akhiri lebih awal".']);
            }
            $c->update(['contract_status' => 'Cancelled']);
        });

        return $contract->fresh();
    }

    /** Salinan tanda tangan profil saat menyetujui (S3). */
    private function snapshotSignature(Contract $c, Employee $employee, int $n): ?string
    {
        $source = $employee->signature;
        if (!$source) return null;
        $disk = Storage::disk('s3');
        try {
            if ($disk->exists($source)) {
                $target = "contracts/signatures/{$c->id}/approver-{$n}.png";
                $disk->copy($source, $target);
                return $target;
            }
        } catch (\Throwable $e) {
            report($e);
        }
        return $source;
    }
}
