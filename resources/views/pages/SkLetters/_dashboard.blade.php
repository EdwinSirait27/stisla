{{--
    Pengingat SK untuk dashboard: SK yang sedang menunggu persetujuan saya (approver 2/3 yang dipilih di SK).
    Hanya tampil kalau ada. Approver sering tidak punya menu SK, jadi ini jalan masuknya.
--}}
@php
    $skMe = auth()->user()?->employee;
    $skTurn = $skMe ? \App\Models\SkLetter::with(['sktype:id,sk_name', 'employees:id,employee_name'])
        ->where(fn($q) => $q->where(fn($w) => $w->where('status', 'Approved HR')->where('approver_2', $skMe->id))
            ->orWhere(fn($w) => $w->where('status', 'Approved Director')->where('approver_3', $skMe->id)))
        ->orderBy('approver_1_at')->limit(5)->get() : collect();
@endphp

@if ($skTurn->isNotEmpty())
    <section class="pr-panel sk-dash" aria-labelledby="sk-dash-title">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="sk-dash-title">SK menunggu persetujuan Anda</h2>
                <span class="pr-panel-sub">{{ $skTurn->count() }} SK. Periksa karyawan, perubahan jabatan, dan gaji sebelum menyetujui.</span>
            </div>
            <a href="{{ route('SkLetters', ['status' => 'my_turn']) }}" class="pr-link">Lihat semua</a>
        </div>
        @foreach ($skTurn as $sk)
            <article class="pr-appr">
                <div class="pr-appr-main">
                    <span class="pr-appr-name">{{ $sk->title ?: $sk->sktype?->sk_name }}</span>
                    <span class="pr-appr-meta">{{ $sk->sk_number }}, {{ $sk->sktype?->sk_name }}, untuk {{ $sk->employees->pluck('employee_name')->take(3)->implode(', ') }}{{ $sk->employees->count() > 3 ? ' dan ' . ($sk->employees->count() - 3) . ' lainnya' : '' }}.</span>
                </div>
                <div class="pr-appr-actions">
                    <a href="{{ route('SkLetters.show', $sk->id) }}" class="pr-btn pr-btn-sm pr-btn-primary">Periksa dan setujui</a>
                </div>
            </article>
        @endforeach
    </section>
    @once
        @push('styles')
            <style>
                /* Mandiri: dashboard karyawan tidak memuat gaya pr-appr */
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
