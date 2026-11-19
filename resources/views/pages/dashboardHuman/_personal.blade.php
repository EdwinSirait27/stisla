{{-- Bagian pribadi dashboard karyawan: dipakai dashboardHuman & dashboardSupervisor (data dari EmployeeDashboardData) --}}
@php
    $d = fn($v) => $v ? \Carbon\Carbon::parse($v)->setTimezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y') : '-';
    $dt = fn($v) => $v ? \Carbon\Carbon::parse($v)->locale('id')->translatedFormat('d M Y, H:i') : '-';
    $num = fn($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $p = $periodSummary;
    $scanStale = $p['last_scan'] && \Carbon\Carbon::parse($p['last_scan'])->lt(today());
    $pendingSub = collect($submissions)->firstWhere('statusClass', 'pending');
@endphp
            {{-- Today + leave + TOIL (signature: what matters to me today) --}}
            <div class="pr-today">
                <section class="pr-panel" aria-labelledby="today-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="today-title">Jadwal hari ini</h2>
                    </div>
                    @if ($todayRoster)
                        <div class="pr-big">
                            <strong>{{ $todayRoster->day_type }}</strong>
                            <span>
                                @if ($todayRoster->shift)
                                    Shift {{ $todayRoster->shift->shift_name }}, {{ substr($todayRoster->shift->start_time, 0, 5) }} sampai {{ substr($todayRoster->shift->end_time, 0, 5) }}
                                @elseif ($todayRoster->notes)
                                    {{ $todayRoster->notes }}
                                @else
                                    Tanpa shift
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="pr-big">
                            <strong class="is-muted">Belum ada jadwal</strong>
                            <span>Roster hari ini belum dibuat. Hubungi atasan atau HR.</span>
                        </div>
                    @endif
                    <p class="pr-note {{ !$scannedToday && $todayRoster?->day_type === 'Work' ? 'is-warn' : '' }}">
                        @if ($scannedToday)
                            Scan pertama hari ini pukul {{ \Carbon\Carbon::parse($scannedToday)->format('H:i') }}.
                        @else
                            Belum ada scan fingerprint hari ini.
                            @if ($scanStale)
                                Data fingerprint terakhir tersinkron {{ $dt($p['last_scan']) }}.
                            @endif
                        @endif
                    </p>
                </section>

                <section class="pr-panel" aria-labelledby="leave-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="leave-title">Saldo cuti {{ $annualLeave->year ?? date('Y') }}</h2>
                    </div>
                    <div class="pr-big">
                        <strong>{{ $num($displayBalance) }} <small>hari</small></strong>
                        <span>cuti tahunan tersisa</span>
                    </div>
                    @if ($leaveBalances->where('id', '!=', $annualLeave?->id)->isNotEmpty())
                        <dl class="pr-mini-stats">
                            @foreach ($leaveBalances->where('id', '!=', $annualLeave?->id) as $bal)
                                <div><dt>{{ $bal->leaves->name ?? 'Cuti' }}</dt><dd>{{ $num($bal->balance_days) }} hari</dd></div>
                            @endforeach
                        </dl>
                    @endif
                    @unless ($annualLeave)
                        <p class="pr-note is-warn">
                            @if ($isNewbie)
                                Anda belum genap 1 tahun bekerja, jadi saldo cuti tahunan belum tersedia.
                            @else
                                Saldo cuti tahunan belum tersedia untuk tahun ini. Hubungi HR.
                            @endif
                        </p>
                    @endunless
                </section>

                <section class="pr-panel" aria-labelledby="toil-title">
                    <div class="pr-panel-head">
                        <h2 class="pr-panel-title" id="toil-title">Saldo TOIL</h2>
                    </div>
                    <div class="pr-big">
                        <strong>{{ $num($toilSummary['hours']) }} <small>jam</small></strong>
                        <span>bisa dipakai sebagai hari libur pengganti lembur</span>
                    </div>
                    <p class="pr-note">
                        @if ($toilSummary['next_expiry'])
                            {{ $num($toilSummary['next_hours']) }} jam kedaluwarsa {{ $d($toilSummary['next_expiry']) }}.
                        @else
                            Belum ada saldo TOIL aktif.
                        @endif
                    </p>
                </section>
            </div>

            {{-- Attendance this period --}}
            <section class="pr-panel" aria-labelledby="period-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="period-title">Kehadiran periode {{ $p['label'] }}</h2>
                        <span class="pr-panel-sub">{{ $d($p['start']) }} sampai {{ $d($p['end']) }}, sesuai periode gaji</span>
                    </div>
                </div>
                @if ($p['has_roster'])
                    <dl class="pr-period">
                        <div>
                            <dt>Tercatat hadir</dt>
                            <dd>{{ $p['present_days'] }} <small>/ {{ $p['work_so_far'] }} hari</small></dd>
                            <span class="pr-period-note">hari dengan scan, dibanding jadwal kerja sampai hari ini</span>
                        </div>
                        <div>
                            <dt>Jadwal kerja</dt>
                            <dd>{{ $p['work_total'] }} <small>hari</small></dd>
                            <span class="pr-period-note">sepanjang periode ini</span>
                        </div>
                        <div><dt>Cuti</dt><dd>{{ $p['leave'] }} <small>hari</small></dd></div>
                        <div><dt>Sakit</dt><dd>{{ $p['sick'] }} <small>hari</small></dd></div>
                        <div><dt>Off dan libur</dt><dd>{{ $p['off'] + $p['holiday'] }} <small>hari</small></dd></div>
                    </dl>
                @else
                    <p class="pr-empty-note">Roster untuk periode ini belum dibuat, jadi kehadiran belum bisa dihitung.</p>
                @endif
                @if ($latestRecap)
                    <p class="pr-note">
                        Rekap resmi terakhir ({{ $d($latestRecap->period_start) }} sampai {{ $d($latestRecap->period_end) }}):
                        <strong>{{ $latestRecap->total_hari_kerja }} hari kerja</strong> dihitung untuk gaji,
                        <strong>{{ $latestRecap->total_hari_telat }} kali telat</strong>.
                    </p>
                @endif
            </section>

            <div class="pr-grid-2">
                {{-- Submissions --}}
                <section class="pr-panel" aria-labelledby="sub-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="sub-title">Pengajuan cuti saya</h2>
                            <span class="pr-panel-sub">{{ count($submissions) }} pengajuan{{ $pendingSub ? ', 1 menunggu approval' : '' }}</span>
                        </div>
                    </div>
                    @forelse ($submissions as $sub)
                        <article class="pr-sub">
                            <div class="pr-sub-top">
                                <span class="pr-sub-name">{{ $sub['leave_name'] }}</span>
                                <span class="status-badge is-{{ $sub['statusClass'] }}">{{ $sub['status'] }}</span>
                            </div>
                            <p class="pr-sub-meta">{{ $sub['dateLabel'] }}, {{ $sub['totalDays'] }} hari. Diajukan {{ $sub['ago'] }}.</p>
                            @if ($sub['employeeReason'])
                                <p class="pr-sub-note"><span>Alasan:</span> {{ $sub['employeeReason'] }}</p>
                            @endif
                            @if (!empty($sub['approverReason']))
                                <p class="pr-sub-note {{ $sub['isRejected'] ? 'is-rejected' : '' }}">
                                    <span>{{ $sub['isRejected'] ? 'Alasan ditolak:' : 'Catatan approver:' }}</span> {{ $sub['approverReason'] }}
                                </p>
                            @endif
                        </article>
                    @empty
                        <p class="pr-empty-note">Belum ada pengajuan cuti. Klik Ajukan cuti di atas untuk membuat pengajuan.</p>
                    @endforelse
                </section>

                {{-- Announcements --}}
                <section class="pr-panel" aria-labelledby="ann-title">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="ann-title">Pengumuman</h2>
                            <span class="pr-panel-sub">{{ $announcements->count() }} sedang tayang</span>
                        </div>
                    </div>
                    @forelse ($announcements as $announcement)
                        <button type="button" class="pr-ann" data-toggle="modal" data-target="#announcementModal"
                            data-id="{{ $announcement->id }}"
                            data-title="{{ $announcement->title }}"
                            data-content="{{ $announcement->content }}"
                            data-author="{{ $announcement->user->employee->employee_name ?? 'HR' }}"
                            data-date="{{ $d($announcement->publish_date ?? $announcement->created_at) }}">
                            <span class="pr-ann-title">
                                {{ $announcement->title }}
                                @if ($announcement->created_at->diffInDays(now()) <= 3)
                                    <span class="status-badge is-paid">Baru</span>
                                @endif
                            </span>
                            <span class="pr-ann-excerpt">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($announcement->content), ENT_QUOTES | ENT_HTML5, 'UTF-8'))), 120) }}</span>
                            <span class="pr-ann-date">Tayang {{ $d($announcement->publish_date ?? $announcement->created_at) }}</span>
                        </button>
                    @empty
                        <p class="pr-empty-note">Tidak ada pengumuman yang sedang tayang.</p>
                    @endforelse
                </section>
            </div>

            {{-- Calendar --}}
            <section class="pr-panel" aria-labelledby="cal-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="cal-title">Jadwal dan kehadiran</h2>
                        <span class="pr-panel-sub">Jadwal dari roster, ditandai kalau ada scan fingerprint di hari itu</span>
                    </div>
                </div>
                <div class="pk-cal-wrap" id="calendarContainer" aria-live="polite">
                    @include('pages.dashboardHuman._calendar')
                </div>
            </section>
