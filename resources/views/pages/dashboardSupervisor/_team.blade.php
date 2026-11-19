{{-- Status tim hari ini. Butuh: $teamToday (rows), $teamStats. Opsional: $teamTitle, $teamSub, $teamEmpty, $teamId --}}
@php
    $teamTitle = $teamTitle ?? 'Tim hari ini';
    $teamSub = $teamSub ?? ($teamStats['total'] . ' bawahan langsung');
    $teamEmpty = $teamEmpty ?? 'Belum ada bawahan yang tercatat atas nama Anda di data atasan karyawan.';
    $teamId = $teamId ?? 'team';
    $stateLabel = [
        'present' => ['is-approved', 'Sudah absen'],
        'not_in' => ['is-pending', 'Belum absen'],
        'not_working' => ['is-draft', null],
        'no_roster' => ['is-rejected', 'Belum ada roster'],
    ];
@endphp
            {{-- Team today --}}
            <section class="pr-panel" aria-labelledby="{{ $teamId }}-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="{{ $teamId }}-title">{{ $teamTitle }}</h2>
                        <span class="pr-panel-sub">{{ $teamSub }}</span>
                    </div>
                </div>
                @if ($teamStats['total'] > 0)
                    <dl class="pr-period">
                        <div><dt>Jadwal kerja</dt><dd>{{ $teamStats['scheduled'] }} <small>orang</small></dd></div>
                        <div><dt>Sudah absen</dt><dd>{{ $teamStats['present'] }} <small>orang</small></dd></div>
                        <div><dt>Belum absen</dt><dd class="{{ $teamStats['not_in'] ? 'is-warn-text' : '' }}">{{ $teamStats['not_in'] }} <small>orang</small></dd></div>
                        <div><dt>Off, cuti, libur</dt><dd>{{ $teamStats['away'] }} <small>orang</small></dd></div>
                        <div><dt>Tanpa roster</dt><dd class="{{ $teamStats['no_roster'] ? 'is-warn-text' : '' }}">{{ $teamStats['no_roster'] }} <small>orang</small></dd></div>
                    </dl>
                    <ul class="pr-team">
                        @foreach ($teamToday->sortBy(fn($m) => ['not_in' => 0, 'no_roster' => 1, 'present' => 2, 'not_working' => 3][$m['state']]) as $m)
                            @php $st = $stateLabel[$m['state']]; @endphp
                            <li>
                                <span class="pr-team-main">
                                    <span class="pr-team-name">{{ $m['name'] }}</span>
                                    <span class="pr-team-sub">{{ collect([$m['position'], $m['day_type'], $m['shift']])->filter()->implode(', ') ?: '-' }}</span>
                                </span>
                                <span class="pr-team-state">
                                    <span class="status-badge {{ $st[0] }}">{{ $st[1] ?? $m['day_type'] }}</span>
                                    @if ($m['scan'])
                                        <span class="pr-team-sub">scan {{ $m['scan'] }}</span>
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="pr-empty-note">{{ $teamEmpty }}</p>
                @endif
            </section>

