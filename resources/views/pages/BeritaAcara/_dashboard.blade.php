{{--
    Pengingat berita acara untuk dashboard (karyawan, supervisor, manager).
    Hanya tampil kalau ada yang perlu diperhatikan: giliran saya menyetujui, tembusan belum dibuka,
    ajuan saya dikembalikan, atau ajuan saya masih menunggu persetujuan.
    Gaya ada di partial ini sendiri (dashboard karyawan tidak punya gaya pr-appr).
--}}
@php
    $baMe = auth()->user()?->employee;
    $ba = $baMe ? \App\Services\BeritaAcaraService::reminders($baMe) : null;
    $baShow = $ba && ($ba['turn_count'] || $ba['cc_count'] || $ba['revision_count'] || $ba['mine_count']);
    $baTitle = fn($doc) => $doc->content?->subject ?: ($doc->companydocumentconfigs?->documenttypes?->document_name ?? 'Berita acara');
    $baAgo = fn($t) => $t ? \Carbon\Carbon::parse($t)->locale('id')->diffForHumans() : '';
@endphp

@if ($baShow)
    <section class="pr-panel ba-dash" aria-labelledby="ba-dash-title">
        <div class="pr-panel-head">
            <div>
                <h2 class="pr-panel-title" id="ba-dash-title">Berita acara untuk Anda</h2>
                <span class="pr-panel-sub">
                    {{ collect([
                        $ba['turn_count'] ? $ba['turn_count'] . ' menunggu persetujuan Anda' : null,
                        $ba['cc_count'] ? $ba['cc_count'] . ' tembusan belum dibuka' : null,
                        $ba['upcoming_count'] ? $ba['upcoming_count'] . ' akan sampai ke Anda setelah approver sebelumnya' : null,
                        $ba['mine_count'] ? $ba['mine_count'] . ' ajuan Anda masih berjalan' : null,
                    ])->filter()->implode(', ') ?: 'Tidak ada yang menunggu persetujuan Anda' }}
                </span>
            </div>
            <a href="{{ route('beritaacara.index') }}" class="pr-link">Buka berita acara</a>
        </div>

        @if ($ba['revision_count'])
            <article class="pr-appr">
                <div class="pr-appr-main">
                    <span class="pr-appr-name">{{ $ba['revision_count'] }} ajuan Anda dikembalikan untuk revisi</span>
                    <span class="pr-appr-meta">Perbaiki lalu ajukan ulang; persetujuan dimulai lagi dari approver pertama.</span>
                </div>
                <div class="pr-appr-actions">
                    <a href="{{ route('beritaacara.index', ['tab' => 'mine', 'status' => 'revision']) }}" class="pr-btn pr-btn-sm">Lihat ajuan</a>
                </div>
            </article>
        @endif

        @foreach ($ba['turn'] as $step)
            @php
                $doc = $step->document;
            @endphp
            <article class="pr-appr">
                <div class="pr-appr-main">
                    <span class="pr-appr-name">{{ $baTitle($doc) }}</span>
                    <span class="pr-appr-meta">
                        {{ $doc->document_number }}, dari {{ $doc->employee?->employee_name ?? '-' }}.
                        Anda approver ke-{{ $step->sequence }}, sampai ke Anda {{ $baAgo($step->updated_at) }}.
                    </span>
                </div>
                <div class="pr-appr-actions">
                    <a href="{{ route('beritaacara.show', $doc->id) }}" class="pr-btn pr-btn-sm pr-btn-primary">Periksa dan setujui</a>
                </div>
            </article>
        @endforeach
        @if ($ba['turn_count'] > $ba['turn']->count())
            <p class="pr-empty-note"><a href="{{ route('beritaacara.index', ['tab' => 'approval']) }}" class="pr-link">Lihat semua {{ $ba['turn_count'] }} yang menunggu persetujuan Anda</a></p>
        @endif

        @foreach ($ba['mine'] as $doc)
            @php
                $baRound = $doc->content?->round ?? 1;
                $baSteps = $doc->approvals->where('round', $baRound);
                $baNow = $baSteps->firstWhere('status', 'pending');
            @endphp
            <article class="pr-appr pr-appr-mine">
                <div class="pr-appr-main">
                    <span class="pr-appr-name">{{ $baTitle($doc) }}</span>
                    <span class="pr-appr-meta">
                        Ajuan Anda, {{ $doc->document_number }}. {{ $baSteps->where('status', 'approved')->count() }} dari {{ $baSteps->count() }} menyetujui{{ $baNow ? ', menunggu ' . ($baNow->employee?->employee_name ?? '-') . ' sejak ' . $baAgo($baNow->updated_at) : '' }}.
                    </span>
                </div>
                <div class="pr-appr-actions">
                    <a href="{{ route('beritaacara.show', $doc->id) }}" class="pr-btn pr-btn-sm">Lihat</a>
                </div>
            </article>
        @endforeach
        @if ($ba['mine_count'] > $ba['mine']->count())
            <p class="pr-empty-note"><a href="{{ route('beritaacara.index', ['tab' => 'mine']) }}" class="pr-link">Lihat semua {{ $ba['mine_count'] }} ajuan Anda</a></p>
        @endif

        @foreach ($ba['cc'] as $rcp)
            @php
                $doc = $rcp->document;
            @endphp
            <article class="pr-appr pr-appr-cc">
                <div class="pr-appr-main">
                    <span class="pr-appr-name">{{ $baTitle($doc) }}</span>
                    <span class="pr-appr-meta">
                        Tembusan untuk Anda. {{ $doc->document_number }}, dari {{ $doc->employee?->employee_name ?? '-' }}, {{ strtolower($doc->status_label) }}.
                    </span>
                </div>
                <div class="pr-appr-actions">
                    <a href="{{ route('beritaacara.show', $doc->id) }}" class="pr-btn pr-btn-sm">Lihat</a>
                </div>
            </article>
        @endforeach
        @if ($ba['cc_count'] > $ba['cc']->count())
            <p class="pr-empty-note"><a href="{{ route('beritaacara.index', ['tab' => 'cc']) }}" class="pr-link">Lihat semua {{ $ba['cc_count'] }} tembusan</a></p>
        @endif
    </section>
    @once
        @push('styles')
            <style>
                /* Mandiri: dashboard karyawan tidak memuat gaya pr-appr milik dashboard supervisor/manager */
                .ba-dash .pr-appr { display: flex; align-items: flex-start; justify-content: space-between; gap: var(--pr-space-md); padding: var(--pr-space-sm) var(--pr-space-lg); border-bottom: 1px solid var(--pr-rule); border-left: 3px solid var(--pr-warn); }
                .ba-dash .pr-appr:last-of-type { border-bottom: 0; }
                .ba-dash .pr-appr-main { flex: 1; min-width: 0; }
                .ba-dash .pr-appr-name { display: block; font-size: var(--pr-text-md); font-weight: 700; color: var(--pr-ink); }
                .ba-dash .pr-appr-meta { display: block; font-size: var(--pr-text-sm); color: var(--pr-ink-2); }
                .ba-dash .pr-appr-actions { display: flex; gap: var(--pr-space-xs); flex-shrink: 0; }
                .ba-dash .pr-empty-note { padding: var(--pr-space-xs) var(--pr-space-lg) var(--pr-space-sm); margin: 0; font-size: var(--pr-text-sm); }
                /* Tembusan & ajuan sendiri: pemberitahuan, bukan tindakan, jadi garis kirinya netral/aksen */
                .ba-dash .pr-appr.pr-appr-cc { border-left-color: var(--pr-rule); }
                .ba-dash .pr-appr.pr-appr-mine { border-left-color: var(--pr-accent); }
                @media (max-width: 40rem) { .ba-dash .pr-appr { flex-direction: column; padding-left: var(--pr-space-md); padding-right: var(--pr-space-md); } }
            </style>
        @endpush
    @endonce
@endif
