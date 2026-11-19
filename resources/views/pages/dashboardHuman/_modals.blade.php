{{-- Modal form cuti & pratinjau pengumuman (dashboardHuman & dashboardSupervisor) --}}
@php $num = $num ?? fn($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ','); @endphp
    {{-- Request Leave Modal --}}
    <div class="modal fade" id="requestLeaveModal" tabindex="-1" aria-labelledby="requestLeaveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="leaveRequestForm" novalidate>
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title pr-modal-title" id="requestLeaveLabel">Ajukan cuti</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label" for="leave_balance_id">Jenis cuti</label>
                            <select name="leave_balance_id" id="leave_balance_id" class="form-control" required>
                                <option value="">Pilih jenis cuti</option>
                                @forelse (($leaveBalances ?? []) as $balance)
                                    <option value="{{ $balance->id }}"
                                        data-days="{{ $balance->balance_days }}"
                                        data-name="{{ $balance->leaves->name ?? 'Cuti' }}"
                                        data-fixed-days="{{ $balance->leaves->fixed_days ?? '' }}"
                                        data-require-attachment="{{ ($balance->leaves->require_attachment ?? false) ? '1' : '0' }}">
                                        {{ $balance->leaves->name ?? 'Cuti' }}, sisa {{ $num($balance->balance_days) }} hari
                                    </option>
                                @empty
                                    <option value="" disabled>Belum ada saldo cuti</option>
                                @endforelse
                            </select>
                        </div>

                        <div class="alert alert-info mb-4" id="leaveBalanceInfo" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div><strong>Saldo tersedia:</strong> <span id="availableBalance">-</span></div>
                                <div><strong>Jenis:</strong> <span id="selectedLeaveType">-</span></div>
                            </div>
                            <div id="fixedDaysInfo" class="mt-1" style="display:none;font-size:.85rem;">Durasi jenis cuti ini tetap, tanggal selesai diisi otomatis.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="start_date">Tanggal mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="end_date">Tanggal selesai</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <div class="alert alert-light border mb-4" id="durationInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Durasi</strong>
                                <span id="calculatedDuration" class="text-primary font-weight-bold">0 hari</span>
                            </div>
                        </div>

                        <div class="alert alert-danger mb-4" id="balanceWarning" style="display:none;">
                            <strong>Saldo cuti tidak cukup.</strong> Durasi yang diajukan melebihi sisa saldo.
                        </div>

                        <div class="mb-4" id="attachmentGroup" style="display:none;">
                            <label class="form-label" for="attachment">Lampiran bukti <span class="text-danger">*</span></label>
                            <input type="file" name="attachment" id="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="text-muted">Wajib untuk jenis cuti ini. Format JPG, PNG, atau PDF, maksimal 5 MB.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="employee_reason">Alasan</label>
                            <textarea name="employee_reason" id="employee_reason" class="form-control" rows="4" maxlength="1000"
                                placeholder="Tulis alasan pengajuan cuti" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitLeaveBtn">Kirim pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Announcement Preview Modal --}}
    <div class="modal fade" id="announcementModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title pr-modal-title" id="modalTitle"></h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="pr-ann-byline">Dari <span id="modalAuthor"></span>, tayang <span id="modalDate"></span></p>
                    <div id="modalContent" class="pr-preview-content"></div>
                </div>
            </div>
        </div>
    </div>
