{{-- Script dashboard pribadi (kalender AJAX, pengumuman, form cuti). Dipanggil di dalam @push('scripts'). --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 3000, showConfirmButton: false });
        @endif
        @if (session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) });
        @endif

        // ── Kalender: muat bulan lain via AJAX ──
        function loadCalendar(month, year) {
            const container = document.getElementById('calendarContainer');
            container.setAttribute('aria-busy', 'true');
            container.style.opacity = '0.5';
            fetch(`{{ $calendarUrl }}?month=${month}&year=${year}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.text())
                .then(html => { container.innerHTML = html; })
                .catch(() => { window.location.href = `{{ $calendarUrl }}?month=${month}&year=${year}`; })
                .finally(() => { container.style.opacity = '1'; container.removeAttribute('aria-busy'); });
        }

        // ── Pratinjau pengumuman ──
        $('#announcementModal').on('show.bs.modal', function(e) {
            const t = $(e.relatedTarget);
            $('#modalTitle').text(t.data('title'));
            $('#modalAuthor').text(t.data('author'));
            $('#modalDate').text(t.data('date'));
            $('#modalContent').html(t.data('content'));
        });

        // ── Form pengajuan cuti ──
        document.addEventListener('DOMContentLoaded', function() {
            $('#requestLeaveModal').on('shown.bs.modal', function() {
                $('#leave_balance_id').select2({ dropdownParent: $('#requestLeaveModal'), placeholder: 'Pilih jenis cuti', allowClear: true, width: '100%' });
            });
            $('#requestLeaveModal').on('hidden.bs.modal', function() {
                if ($('#leave_balance_id').data('select2')) $('#leave_balance_id').select2('destroy');
            });
            $('#leave_balance_id').on('select2:select select2:clear', function() {
                this.dispatchEvent(new Event('change', { bubbles: true }));
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';
            const leaveSelect = document.getElementById('leave_balance_id');
            const balanceInfo = document.getElementById('leaveBalanceInfo');
            const availableBalance = document.getElementById('availableBalance');
            const selectedType = document.getElementById('selectedLeaveType');
            const fixedInfo = document.getElementById('fixedDaysInfo');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const durationDisplay = document.getElementById('calculatedDuration');
            const balanceWarning = document.getElementById('balanceWarning');
            const submitBtn = document.getElementById('submitLeaveBtn');
            const submitLabel = submitBtn.innerHTML;
            const form = document.getElementById('leaveRequestForm');
            const attachmentGroup = document.getElementById('attachmentGroup');
            const attachmentInput = document.getElementById('attachment');

            let maxDays = 0;
            let fixedDays = 0;
            let requireAttachment = false;

            function applyFixedDuration() {
                if (fixedDays > 0 && startDate.value) {
                    const s = new Date(startDate.value + 'T00:00:00');
                    s.setDate(s.getDate() + (fixedDays - 1));
                    endDate.value = [s.getFullYear(), String(s.getMonth() + 1).padStart(2, '0'), String(s.getDate()).padStart(2, '0')].join('-');
                }
            }

            function calculateDuration() {
                if (!startDate.value || !endDate.value) {
                    durationDisplay.textContent = '0 hari';
                    balanceWarning.style.display = 'none';
                    submitBtn.disabled = false;
                    return;
                }
                const s = new Date(startDate.value + 'T00:00:00');
                const e = new Date(endDate.value + 'T00:00:00');
                if (e < s) {
                    durationDisplay.textContent = 'Tanggal selesai sebelum tanggal mulai';
                    durationDisplay.classList.replace('text-primary', 'text-danger');
                    submitBtn.disabled = true;
                    return;
                }
                const days = Math.round((e - s) / 86400000) + 1;
                durationDisplay.textContent = days + ' hari';
                durationDisplay.classList.replace('text-danger', 'text-primary');
                const over = maxDays > 0 && days > maxDays;
                balanceWarning.style.display = over ? 'block' : 'none';
                submitBtn.disabled = over;
            }

            leaveSelect?.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                fixedDays = parseInt(opt.dataset.fixedDays || '0') || 0;
                requireAttachment = opt.dataset.requireAttachment === '1';
                if (this.value) {
                    maxDays = parseFloat(opt.dataset.days ?? 0);
                    availableBalance.textContent = maxDays + ' hari';
                    selectedType.textContent = opt.dataset.name ?? '-';
                    balanceInfo.style.display = 'block';
                    attachmentGroup.style.display = requireAttachment ? 'block' : 'none';
                    fixedInfo.style.display = fixedDays > 0 ? 'block' : 'none';
                    endDate.readOnly = fixedDays > 0;
                    applyFixedDuration();
                } else {
                    balanceInfo.style.display = 'none';
                    attachmentGroup.style.display = 'none';
                    endDate.readOnly = false;
                    maxDays = 0;
                    fixedDays = 0;
                    requireAttachment = false;
                }
                calculateDuration();
            });

            startDate?.addEventListener('change', function() {
                endDate.min = this.value;
                applyFixedDuration();
                calculateDuration();
            });
            endDate?.addEventListener('change', calculateDuration);

            form?.addEventListener('submit', function(e) {
                e.preventDefault();
                if (!leaveSelect.value) {
                    Swal.fire({ icon: 'warning', title: 'Pilih jenis cuti', text: 'Pilih jenis cuti terlebih dahulu.' });
                    return;
                }
                if (!startDate.value || !endDate.value) {
                    Swal.fire({ icon: 'warning', title: 'Tanggal belum lengkap', text: 'Isi tanggal mulai dan tanggal selesai.' });
                    return;
                }
                if (!document.getElementById('employee_reason').value.trim()) {
                    Swal.fire({ icon: 'warning', title: 'Alasan wajib diisi', text: 'Tulis alasan pengajuan cuti.' });
                    return;
                }
                // Lampiran wajib dicek SEBELUM konfirmasi (dulu tombol bisa macet di "Submitting...")
                if (requireAttachment && !attachmentInput.files.length) {
                    Swal.fire({ icon: 'warning', title: 'Lampiran wajib', text: 'Jenis cuti ini memerlukan lampiran bukti.' });
                    return;
                }

                Swal.fire({
                    title: 'Kirim pengajuan cuti?',
                    html: `${$('<div>').text(selectedType.textContent).html()}, ${durationDisplay.textContent}.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#3085d6',
                }).then(result => {
                    if (!result.isConfirmed) return;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mengirim...';

                    const formData = new FormData();
                    formData.append('leave_balance_id', leaveSelect.value);
                    formData.append('start_date', startDate.value);
                    formData.append('end_date', endDate.value);
                    formData.append('employee_reason', document.getElementById('employee_reason').value);
                    if (attachmentInput.files.length) formData.append('attachment', attachmentInput.files[0]);

                    const reset = () => { submitBtn.disabled = false; submitBtn.innerHTML = submitLabel; };

                    fetch("{{ route('Leaverequest.store') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    })
                        .then(async res => ({ ok: res.ok, data: await res.json().catch(() => ({})) }))
                        .then(({ ok, data }) => {
                            if (ok && data.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'Terkirim', text: data.message ?? 'Pengajuan cuti berhasil dikirim.' })
                                    .then(() => { $('#requestLeaveModal').modal('hide'); window.location.reload(); });
                            } else {
                                const msg = data.message ?? (data.errors ? Object.values(data.errors).flat().join(' ') : 'Terjadi kesalahan saat mengirim pengajuan.');
                                Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                                reset();
                            }
                        })
                        .catch(() => {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan saat mengirim pengajuan.' });
                            reset();
                        });
                });
            });
        });
    </script>
