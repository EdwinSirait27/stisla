{{-- Antrian cuti bawahan (supervisor & manager). Butuh: $pendingLeaves, $pendingCount, $teamStats --}}
@php
    $d = $d ?? fn($v) => $v ? \Carbon\Carbon::parse($v)->setTimezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y') : '-';
    $canApprove = auth()->user()->canAny(['dashboardManager', 'dashboardSupervisor']);
@endphp
            {{-- Approvals (signature: what my team needs from me) --}}
            <section class="pr-panel" aria-labelledby="appr-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="appr-title">Cuti tim menunggu approval</h2>
                        <span class="pr-panel-sub" id="appr-count">{{ $pendingCount ? $pendingCount . ' pengajuan menunggu Anda' : 'Tidak ada yang menunggu' }}</span>
                    </div>
                    @if ($pendingCount > $pendingLeaves->count())
                        <a href="{{ route('leaverequest.index') }}" class="pr-link">Lihat semua {{ $pendingCount }}</a>
                    @endif
                </div>
                @forelse ($pendingLeaves as $leave)
                    @php
                        $who = $leave->leavebalance?->employees;
                        $type = $leave->leavebalance?->leaves?->name ?? 'Cuti';
                        $start = \Carbon\Carbon::parse($leave->start_date);
                        $end = \Carbon\Carbon::parse($leave->end_date);
                        $range = $start->isSameDay($end) ? $d($start) : $d($start) . ' sampai ' . $d($end);
                    @endphp
                    <article class="pr-appr" id="leave-item-{{ $leave->id }}">
                        <div class="pr-appr-main">
                            <span class="pr-appr-name">{{ $who->employee_name ?? '-' }}</span>
                            <span class="pr-appr-meta">{{ $type }}, {{ $range }} ({{ $leave->total_days }} hari). Diajukan {{ \Carbon\Carbon::parse($leave->created_at)->locale('id')->diffForHumans() }}.</span>
                            @if ($leave->employee_reason)
                                <span class="pr-sub-note"><span>Alasan:</span> {{ $leave->employee_reason }}</span>
                            @endif
                        </div>
                        @if ($canApprove)
                            <div class="pr-appr-actions">
                                <button type="button" class="pr-btn pr-btn-sm pr-btn-primary js-leave-action" data-action="approve"
                                    data-id="{{ $leave->id }}" data-name="{{ $who->employee_name ?? '-' }}" data-url="{{ route('leaverequest.approve', $leave->id) }}">
                                    Setujui
                                </button>
                                <button type="button" class="pr-btn pr-btn-sm pr-btn-danger js-leave-action" data-action="reject"
                                    data-id="{{ $leave->id }}" data-name="{{ $who->employee_name ?? '-' }}" data-url="{{ route('leaverequest.reject', $leave->id) }}">
                                    Tolak
                                </button>
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="pr-empty-note" id="leave-empty-state">
                        @if ($teamStats['total'] === 0)
                            Belum ada bawahan yang tercatat atas nama Anda, jadi belum ada pengajuan yang perlu disetujui. Hubungi HR untuk mengatur atasan di data karyawan.
                        @else
                            Semua pengajuan cuti tim sudah ditangani.
                        @endif
                    </p>
                @endforelse
            </section>

