  <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card primary">
                        <div class="mini-stat-icon primary">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="mini-stat-value">22</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->present ?? 22 }}</div> --}}
                        <div class="mini-stat-label">Days Present</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card success">
                        <div class="mini-stat-icon success">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="mini-stat-value">95%</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->rate ?? 95 }}%</div> --}}
                        <div class="mini-stat-label">Attendance Rate</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card warning">
                        <div class="mini-stat-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="mini-stat-value">2</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->late ?? 2 }}</div> --}}
                        <div class="mini-stat-label">Times Late</div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12 mb-4">
                    <div class="mini-stat-card danger">
                        <div class="mini-stat-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="mini-stat-value">1</div>
                        {{-- <div class="mini-stat-value">{{ $attendanceData->absent ?? 1 }}</div> --}}
                        <div class="mini-stat-label">Days Absent</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-12 col-6 mb-4">
                <div class="announcements-card">
                    <div class="announcements-header">
                        <h4>
                            <i class="fas fa-bullhorn me-2"></i>
                            Company Announcements
                        </h4>
                    </div>
                    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                        <!-- Announcement 1 -->
                        <div class="announcement-item" data-toggle="modal" data-target="#announcementModal">
                            <div class="announcement-title">
                                <i class="fas fa-star text-warning"></i>
                                Holiday Schedule for December
                                <span class="announcement-badge-new">New</span>
                            </div>
                            <div class="announcement-excerpt">
                                Dear Team, Please note the following holiday schedule for December 2024. The office will
                                be closed from December 24-26 and December 31 - January 1...
                            </div>
                            <div class="announcement-date">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Posted 1 day ago
                            </div>
                        </div>

                        <!-- Announcement 2 -->
                        <div class="announcement-item">
                            <div class="announcement-title">
                                <i class="fas fa-gift text-danger"></i>
                                Year-End Bonus Announcement
                                <span class="announcement-badge-new">New</span>
                            </div>
                            <div class="announcement-excerpt">
                                We're pleased to announce that year-end bonuses will be distributed on December 15,
                                2024. The amount will be based on individual performance...
                            </div>
                            <div class="announcement-date">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Posted 2 days ago
                            </div>
                        </div>

                        <!-- Announcement 3 -->
                        <div class="announcement-item">
                            <div class="announcement-title">
                                <i class="fas fa-laptop-code text-primary"></i>
                                New HR System Implementation
                            </div>
                            <div class="announcement-excerpt">
                                Starting January 2025, we will be implementing a new HR management system. All employees
                                are required to attend training sessions...
                            </div>
                            <div class="announcement-date">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Posted 5 days ago
                            </div>
                        </div>

                        <!-- Announcement 4 -->
                        <div class="announcement-item">
                            <div class="announcement-title">
                                <i class="fas fa-heartbeat text-success"></i>
                                Health Insurance Update
                            </div>
                            <div class="announcement-excerpt">
                                Our company health insurance coverage has been upgraded to include dental and vision
                                care for all employees and their families...
                            </div>
                            <div class="announcement-date">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Posted 1 week ago
                            </div>
                        </div>

                        <!-- Announcement 5 -->
                        <div class="announcement-item">
                            <div class="announcement-title">
                                <i class="fas fa-users text-info"></i>
                                Team Building Event - December
                            </div>
                            <div class="announcement-excerpt">
                                Join us for our annual team building event on December 18, 2024 at Nusa Dua Beach
                                Resort. Activities include team games, BBQ dinner...
                            </div>
                            <div class="announcement-date">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Posted 1 week ago
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-center">
                        <a href="#" class="text-decoration-none">
                            View All Announcements
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <br>
            <!-- Main Content Grid -->
            <div class="row">
                <!-- Clock In/Out Section -->
                <div class="col-lg-4 col-12 mb-4">
                    {{-- <div class="clock-card">
                        <h4 class="mb-3">
                            <i class="fas fa-clock me-2"></i>
                            Attendance Clock
                        </h4>
                        <div class="clock-display" id="currentTime">00:00:00</div>
                        <div class="clock-date" id="currentDate">Monday, January 01, 2024</div>

                        @if (true)
                            <button class="btn clock-in-btn pulse-animation" id="clockInBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Clock In
                            </button>
                        @else
                            <button class="btn clock-out-btn" id="clockOutBtn">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Clock Out
                            </button>
                            <div class="clock-status">
                                <strong>Clocked in at:</strong>08:00 AM
                            </div>
                        @endif
                    </div> --}}

                    <!-- Leave Balance -->
                    <div class="leave-balance-card mt-1">
                        <div class="leave-balance-header">
                            <h4>
                                <i class="fas fa-umbrella-beach me-2"></i>
                                Leave Balance -
                                {{ Auth::user()->employee->employee_name ?? Auth::user()->employee->employee_name }}
                            </h4>
                        </div>
                        <div class="leave-balance-body">
                            <div class="leave-item">
                                <div class="leave-type">
                                    <div class="leave-type-icon annual">
                                        <i class="fas fa-umbrella-beach"></i>
                                    </div>
                                    <div>
                                        <div class="leave-type-name">Annual Leave</div>
                                        <div class="leave-type-period">{{ $annualLeave->year ?? date('Y') }}</div>
                                    </div>
                                </div>
                                <div class="leave-days">
                                    <div class="leave-days-value">
                                        {{ rtrim(rtrim(number_format($displayBalance, 2), '0'), '.') }}
                                    </div>
                                    <div class="leave-days-label">days remaining</div>
                                </div>
                            </div>

                            @unless ($annualLeave)
                                <div class="alert alert-warning mt-3 mb-0" style="font-size: 0.85rem;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    @if ($isNewbie)
                                        Anda belum genap 1 tahun berada di perusahaan ini, sehingga saldo cuti belum tersedia.
                                    @else
                                        Saldo cuti tahunan belum tersedia untuk periode ini.
                                    @endif
                                </div>
                            @endunless
                        </div>
                    </div>
                </div>

                <!-- My Submissions -->
                <div class="col-lg-8 col-12 mb-4">
                    <div class="submissions-card">
                        <div class="submissions-header">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <h4>
                                    <i class="fas fa-file-alt me-2"></i>
                                    My Submissions
                                </h4>
                                @if ($hasPending)
                                    <button type="button" class="btn btn-secondary btn-sm" disabled
                                        title="Anda masih memiliki pengajuan yang menunggu persetujuan">
                                        <i class="fas fa-clock me-1"></i>
                                        Pending Approval
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary btn-sm" id="newSubmissionBtn"
                                        data-toggle="modal" data-target="#requestLeaveModal">
                                        <i class="fas fa-plus me-1"></i>
                                        New Request
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @forelse ($submissions as $sub)
                                <div class="submission-item">
                                    <div class="submission-header-row">
                                        <span class="submission-type-badge annual-leave">
                                            <i class="fas fa-umbrella-beach me-1"></i>
                                            {{ $sub['leave_name'] }}
                                        </span>
                                        <span class="submission-status {{ $sub['statusClass'] }}">
                                            <i class="fas {{ $sub['statusIcon'] }} me-1"></i>
                                            {{ $sub['status'] }}
                                        </span>
                                    </div>
                                    <div class="submission-meta">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            {{ $sub['dateLabel'] }}
                                        </span>
                                        <span>
                                            <i class="fas fa-hourglass-half"></i>
                                            {{ $sub['totalDays'] }} days
                                        </span>
                                        <span>
                                            <i class="fas fa-clock"></i>
                                            {{ $sub['ago'] }}
                                        </span>
                                    </div>
                                    @if ($sub['employeeReason'])
                                        <div class="submission-notes">
                                            <i class="fas fa-sticky-note me-2"></i>
                                            <strong>Note:</strong>
                                            {{ $sub['employeeReason'] }}
                                        </div>
                                    @endif

                                    @if (!empty($sub['approverReason']))
                                        <div class="submission-notes"
                                            style="border-top: 1px dashed #e9ecef; margin-top: 6px; padding-top: 6px;">
                                            <i
                                                class="fas {{ $sub['isRejected'] ? 'fa-times-circle' : 'fa-user-check' }} me-2"></i>
                                            <strong>{{ $sub['isRejected'] ? 'Rejection reason:' : 'Approver:' }}</strong>
                                            {{ $sub['approverReason'] }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h6>No submissions yet</h6>
                                    <p>You haven't submitted any leave requests.</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="card-footer bg-light text-center">
                            <a href="#" class="text-decoration-none">
                                View All Submissions
                                <i class="fas fa-arrow-right ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements & Attendance History Row -->
            <div class="row">
                <div class="col-lg-12 col-12 mb-4">
                    <div class="attendance-history-card">
                        <div class="attendance-history-header">
                            <h4>
                                <i class="fas fa-calendar-check me-2"></i>
                                Schedule History
                            </h4>
                        </div>
                        <div class="attendance-calendar" id="calendarContainer">
                            @include('pages.Dashboard.calendar')
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ═════════════════════════════════════════════════════════════
          MODAL DIUBAH: Request Leave Modal (DINAMIS DARI DB)
         - action: route('Leaverequest.store') (bukan Submissions.store)
         - Dropdown jenis cuti dari $leaveBalances
         - Field name: leave_balance_id, start_date, end_date, employee_reason
         ═════════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="requestLeaveModal" tabindex="-1" aria-labelledby="requestLeaveLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="leaveRequestForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="requestLeaveLabel">
                            <i class="fas fa-paper-plane me-2"></i>
                            Apply Leave
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <!-- Jenis Cuti (DINAMIS dari leave_balances_tables) -->
                        <div class="mb-4">
                            <label class="form-label" for="leave_balance_id">
                                <i class="fas fa-clipboard-list me-1"></i> Type Leave
                            </label>
                            <select name="leave_balance_id" id="leave_balance_id" class="form-control" required>
                                <option value="">-- Select type of leave --</option>
                                @forelse(($leaveBalances ?? []) as $balance)
                                    <option value="{{ $balance->id }}" data-days="{{ $balance->balance_days }}"
                                        data-name="{{ $balance->leaves->name ?? 'Leave' }}">
                                        {{ $balance->leaves->name ?? 'Leave' }}
                                        — Sisa: {{ $balance->balance_days }} days
                                    </option>
                                @empty
                                    <option value="" disabled>No leave balance available</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- Available Balance Info (auto-update) -->
                        <div class="alert alert-info mb-4" id="leaveBalanceInfo" style="display:none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Balance Available:</strong>
                                    <span id="availableBalance">- days</span>
                                </div>
                                <div>
                                    <strong>Leave Type:</strong>
                                    <span id="selectedLeaveType">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="start_date">
                                    <i class="fas fa-calendar-alt me-1"></i> Start Date
                                </label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    min="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="end_date">
                                    <i class="fas fa-calendar-check me-1"></i> End Date
                                </label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    min="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Duration Display -->
                        <div class="alert alert-light border mb-4" id="durationInfo">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-hourglass-half me-2"></i><strong>Duration:</strong></span>
                                <span id="calculatedDuration" class="text-primary font-weight-bold">0 days</span>
                            </div>
                        </div>

                        <!-- Warning kalau durasi > saldo -->
                        <div class="alert alert-danger mb-4" id="balanceWarning" style="display:none;">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Insufficient leave balance!</strong>
                            The requested duration exceeds your remaining leave days.
                        </div>

                        <!-- Alasan -->
                        <div class="mb-3">
                            <label class="form-label" for="employee_reason">
                                <i class="fas fa-sticky-note me-1"></i> Reason for Application
                            </label>
                            <textarea name="employee_reason" id="employee_reason" class="form-control" rows="4"
                                placeholder="Write the reason for your leave application..." required></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="submitLeaveBtn">
                            <i class="fas fa-paper-plane me-1"></i> Apply Leave
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Announcement Preview Modal -->
    <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-bullhorn me-2"></i>
                        Holiday Schedule for December
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Published By</small>
                                <p class="mb-0 font-weight-bold">HR Department</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Date</small>
                                <p class="mb-0 font-weight-bold">December 1, 2024</p>
                            </div>
                        </div>
                    </div>
                    <div style="line-height: 1.8;">
                        <p>Dear Team,</p>
                        <p>Please note the following holiday schedule for December 2024:</p>
                        <ul>
                            <li><strong>Christmas Eve & Christmas:</strong> December 24-26, 2024 (Office Closed)</li>
                            <li><strong>New Year's Eve & New Year:</strong> December 31, 2024 - January 1, 2025 (Office
                                Closed)</li>
                        </ul>
                        <p>Regular office hours will resume on January 2, 2025.</p>
                        <p>For urgent matters during the holiday period, please contact the emergency hotline.</p>
                        <p>Wishing you and your families a wonderful holiday season!</p>
                        <p>Best regards,<br>HR Department</p>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <small class="text-muted text-center w-100">
                        <i class="fas fa-shield-alt me-2"></i>
                        Official announcement from HR Department
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- ═════════════════════════════════════════════════════════════
       modal pengajuan cuti
     ═════════════════════════════════════════════════════════════ --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function loadCalendar(month, year) {
            const container = document.getElementById('calendarContainer');
            container.style.opacity = '0.5';

            fetch(`{{ url('/Dashboard') }}?month=${month}&year=${year}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                })
                .catch(() => {
                    window.location.href = `{{ url('/Dashboard') }}?month=${month}&year=${year}`;
                });
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#requestLeaveModal').on('shown.bs.modal', function() {
                $('#leave_balance_id').select2({
                    dropdownParent: $('#requestLeaveModal'),
                    placeholder: '-- select the type of leave --',
                    allowClear: true,
                    width: '100%',
                });
            });

            // ✅ Destroy Select2 saat modal ditutup
            $('#requestLeaveModal').on('hidden.bs.modal', function() {
                $('#leave_balance_id').select2('destroy');
            });

            // ✅ Satu saja, tidak perlu dua
            $('#leave_balance_id').on('select2:select select2:clear', function() {
                this.dispatchEvent(new Event('change', {
                    bubbles: true
                }));
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const leaveSelect = document.getElementById('leave_balance_id');
            const balanceInfo = document.getElementById('leaveBalanceInfo');
            const availableBalance = document.getElementById('availableBalance');
            const selectedType = document.getElementById('selectedLeaveType');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const durationDisplay = document.getElementById('calculatedDuration');
            const balanceWarning = document.getElementById('balanceWarning');
            const submitBtn = document.getElementById('submitLeaveBtn');
            const form = document.getElementById('leaveRequestForm');

            let maxDays = 0;

            // ── Saat pilih jenis cuti → tampilkan saldo tersedia ──
            leaveSelect?.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                const days = parseInt(opt.dataset.days ?? 0);
                const name = opt.dataset.name ?? '-';

                if (this.value) {
                    maxDays = days;
                    availableBalance.textContent = days + ' days';
                    selectedType.textContent = name;
                    balanceInfo.style.display = 'block';
                } else {
                    balanceInfo.style.display = 'none';
                    maxDays = 0;
                }
                calculateDuration();
            });

            // ── Hitung durasi otomatis ──
            function calculateDuration() {
                const start = startDate.value;
                const end = endDate.value;

                if (!start || !end) {
                    durationDisplay.textContent = '0 days';
                    balanceWarning.style.display = 'none';
                    submitBtn.disabled = false;
                    return;
                }

                const startObj = new Date(start);
                const endObj = new Date(end);

                if (endObj < startObj) {
                    durationDisplay.textContent = 'End date cannot be before start date';
                    durationDisplay.classList.add('text-danger');
                    durationDisplay.classList.remove('text-primary');
                    submitBtn.disabled = true;
                    return;
                }

                const diffTime = endObj - startObj;
                const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24)) + 1;

                durationDisplay.textContent = diffDays + ' days';
                durationDisplay.classList.remove('text-danger');
                durationDisplay.classList.add('text-primary');

                if (maxDays > 0 && diffDays > maxDays) {
                    balanceWarning.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    balanceWarning.style.display = 'none';
                    submitBtn.disabled = false;
                }
            }

            startDate?.addEventListener('change', function() {
                if (endDate) endDate.min = this.value;
                calculateDuration();
            });
            endDate?.addEventListener('change', calculateDuration);

            // ── Submit via AJAX ──
            form?.addEventListener('submit', function(e) {
                e.preventDefault();

                if (!leaveSelect.value) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Select the type of leave',
                        text: 'Please select the type of leave first.',
                    });
                    return;
                }

                Swal.fire({
                    title: 'Submit Leave Request?',
                    text: 'Please ensure the data is correct before submitting.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Submit',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#1976D2',
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';

                    const payload = {
                        leave_balance_id: document.getElementById('leave_balance_id').value,
                        start_date: document.getElementById('start_date').value,
                        end_date: document.getElementById('end_date').value,
                        employee_reason: document.getElementById('employee_reason').value,
                    };

                    fetch("{{ route('Leaverequest.store') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify(payload),
                        })
                        .then(async (res) => {
                            const data = await res.json();
                            return {
                                ok: res.ok,
                                data
                            };
                        })
                        .then(({
                            ok,
                            data
                        }) => {
                            if (ok && data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message ??
                                        'Leave request submitted successfully.',
                                    confirmButtonText: 'OK',
                                }).then(() => {
                                    $('#requestLeaveModal').modal('hide');
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: data.message ??
                                        'An error occurred while submitting the request.',
                                });
                                submitBtn.disabled = false;
                                submitBtn.innerHTML =
                                    '<i class="fas fa-paper-plane me-1"></i> Submit Leave Request';
                            }
                        })
                        .catch((err) => {
                            console.error(err);
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'An error occurred while submitting the request.',
                            });
                            submitBtn.disabled = false;
                            submitBtn.innerHTML =
                                '<i class="fas fa-paper-plane me-1"></i> Submit Leave Request';
                        });
                });
            });
        });
    </script>
@endpush
