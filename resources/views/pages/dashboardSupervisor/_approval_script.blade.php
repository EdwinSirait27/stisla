{{-- Script approve/tolak cuti (dipanggil di dalam @push('scripts')). Butuh: $pendingCount --}}
    <script>
        // ── Approve / tolak cuti tim (alasan wajib di kedua aksi, sesuai LeaverequestController) ──
        (function() {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
            let remaining = {{ $pendingCount }};

            document.querySelectorAll('.js-leave-action').forEach(btn => btn.addEventListener('click', function() {
                const approve = this.dataset.action === 'approve';
                const { id, name, url } = this.dataset;
                Swal.fire({
                    title: approve ? `Setujui cuti ${name}?` : `Tolak cuti ${name}?`,
                    input: 'textarea',
                    inputLabel: approve ? 'Catatan persetujuan' : 'Alasan penolakan',
                    inputPlaceholder: approve ? 'mis. Disetujui, pastikan serah terima tugas' : 'Tulis alasan penolakan',
                    inputAttributes: { maxlength: 500 },
                    inputValidator: v => !v.trim() ? (approve ? 'Catatan persetujuan wajib diisi' : 'Alasan penolakan wajib diisi') : undefined,
                    icon: approve ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonText: approve ? 'Setujui' : 'Tolak',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: approve ? '#3085d6' : '#d33',
                    showLoaderOnConfirm: true,
                    preConfirm: reason => fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ approver_reason: reason.trim() }),
                    }).then(async res => {
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok || data.icon !== 'success') {
                            throw new Error(data.text || data.message || (res.status === 403 ? 'Anda tidak berhak memproses pengajuan ini.' : 'Gagal memproses pengajuan.'));
                        }
                        return data;
                    }).catch(err => Swal.showValidationMessage(err.message)),
                    allowOutsideClick: () => !Swal.isLoading(),
                }).then(result => {
                    if (!result.isConfirmed || !result.value) return;
                    Swal.fire({ icon: 'success', title: result.value.title || 'Berhasil', text: result.value.text, timer: 2200, showConfirmButton: false });
                    document.getElementById('leave-item-' + id)?.remove();
                    remaining = Math.max(0, remaining - 1);
                    document.getElementById('appr-count').textContent = remaining ? `${remaining} pengajuan menunggu Anda` : 'Tidak ada yang menunggu';
                });
            }));
        })();
    </script>
