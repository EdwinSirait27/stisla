{{--
    Pengingat kontrak untuk dashboard: kontrak yang sedang menunggu persetujuan saya (approver 2/3 yang dipilih).
    Hanya tampil kalau ada. Gaya pr-appr memakai yang sudah dimuat panel SK / berita acara, ditambah cadangan di sini.
--}}
@php
    $ctMe = auth()->user()?->employee;
    $ctTurn = $ctMe ? \App\Models\Contract::with('employee:id,employee_name')
        ->where('contract_status', 'Pending')
        ->where(fn($w) => $w
            ->where(fn($x) => $x->whereNull('approver_2_at')->where('approver_2', $ctMe->id))
            ->orWhere(fn($x) => $x->whereNotNull('approver_2_at')->whereNull('approver_3_at')->where('approver_3', $ctMe->id)))
        ->orderBy('approver_1_at')->limit(5)->get() : collect();
@endphp

@if ($ctTurn->isNotEmpty())
    <section class="pr-panel sk-dash" aria-labelledby="ct-dash-title">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="ct-dash-title">Kontrak menunggu persetujuan Anda</h2>
                <span class="pr-panel-sub">{{ $ctTurn->count() }} kontrak. Periksa masa kontrak, penempatan, dan gaji sebelum menyetujui.</span>
            </div>
            <a href="{{ route('contract', ['view' => 'my_turn']) }}" class="pr-link">Lihat semua</a>
        </div>
        @foreach ($ctTurn as $ct)
            <article class="pr-appr">
                <div class="pr-appr-main">
                    <span class="pr-appr-name">Kontrak {{ $ct->contract_type }} {{ $ct->employee?->employee_name }}</span>
                    <span class="pr-appr-meta">{{ $ct->start_date?->locale('id')->translatedFormat('d M Y') }} sampai {{ $ct->end_date?->locale('id')->translatedFormat('d M Y') ?? 'tanpa batas waktu' }}.</span>
                </div>
                <div class="pr-appr-actions">
                    <a href="{{ route('showcontract', $ct->id) }}" class="pr-btn pr-btn-sm pr-btn-primary">Periksa dan setujui</a>
                </div>
            </article>
        @endforeach
    </section>
    @once
        @push('styles')
            <style>
                .sk-dash .pr-appr { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--pr-space-md); padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); border-left: 3px solid var(--pr-warn); }
                .sk-dash .pr-appr:last-of-type { border-bottom: 0; }
                .sk-dash .pr-appr-main { flex: 1; min-width: 0; }
                .sk-dash .pr-appr-name { display: block; font-size: var(--pr-text-md); font-weight: 700; color: var(--pr-ink); }
                .sk-dash .pr-appr-meta { display: block; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
                .sk-dash .pr-appr-actions { display: flex; gap: var(--pr-space-xs); flex-shrink: 0; }
                @media (max-width: 40rem) { .sk-dash .pr-appr { flex-direction: column; padding-left: var(--pr-space-md); padding-right: var(--pr-space-md); } }
            </style>
        @endpush
    @endonce
@endif
