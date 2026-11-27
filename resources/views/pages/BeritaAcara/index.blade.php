@extends('layouts.app')
@section('title', 'Berita acara')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    @php
        $tabLabels = ['approval' => 'Perlu persetujuan saya', 'mine' => 'Ajuan saya', 'cc' => 'Tembusan', 'all' => 'Semua'];
        $tabCount = ['approval' => $counts['approval'], 'mine' => $counts['mine_revision'], 'cc' => $counts['cc_unread'], 'all' => null];
        $tabHint = ['approval' => 'Menunggu tanda tangan Anda', 'mine' => 'perlu revisi', 'cc' => 'belum dibaca'];
        $empty = [
            'approval' => 'Tidak ada berita acara yang menunggu persetujuan Anda.',
            'mine'     => 'Anda belum pernah mengajukan berita acara.',
            'cc'       => 'Belum ada berita acara yang ditembuskan ke Anda.',
            'all'      => 'Belum ada berita acara.',
        ];
        $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : '-';
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li aria-current="page">Berita acara</li></ol>
                    <h1 class="pr-title">Berita acara</h1>
                    <p class="pr-lede">Disetujui berurutan oleh approver yang dipilih pengaju. Setiap persetujuan disertai tanda tangan; tembusan ikut diberi tahu saat berita acara sah.</p>
                </div>
                @if ($canCreate)
                    <div class="pr-actions">
                        <a href="{{ route('beritaacara.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Buat berita acara</a>
                    </div>
                @endif
            </header>

            <section class="pr-panel" aria-labelledby="ba-title">
                <nav class="ba-tabs" aria-label="Kelompok berita acara">
                    @foreach ($tabs as $t)
                        <a href="{{ route('beritaacara.index', ['tab' => $t]) }}" class="ba-tab" @if ($tab === $t) aria-current="page" @endif>
                            {{ $tabLabels[$t] }}
                            @if ($tabCount[$t])
                                <span class="ba-count is-hot" title="{{ $tabHint[$t] }}">{{ $tabCount[$t] }}<span class="sr-only"> {{ $tabHint[$t] }}</span></span>
                            @endif
                        </a>
                    @endforeach
                </nav>
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="ba-title">{{ $tabLabels[$tab] }}</h2>
                        <span class="pr-panel-sub">{{ $documents->total() }} berita acara</span>
                    </div>
                    <form method="GET" class="ba-filter" role="search">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <label for="ba-status" class="sr-only">Status</label>
                        <select id="ba-status" name="status" class="dt-select" onchange="this.form.submit()">
                            <option value="">Semua status</option>
                            @foreach (['pending', 'in_review', 'revision', 'approved', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected($status === $s)>{{ \App\Models\Documents::STATUS_LABELS[$s] }}</option>
                            @endforeach
                        </select>
                        <div class="pr-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <label for="ba-q" class="sr-only">Cari nomor atau perihal</label>
                            <input type="search" id="ba-q" name="q" value="{{ request('q') }}" placeholder="Cari nomor atau perihal">
                        </div>
                    </form>
                </div>

                @if ($documents->isEmpty())
                    <p class="ba-empty">{{ $empty[$tab] }}</p>
                @else
                    <ul class="ba-list">
                        @foreach ($documents as $d)
                            @php $cur = $current->get($d->id); @endphp
                            <li>
                                <a href="{{ route('beritaacara.show', $d->id) }}" class="ba-row">
                                    <span>
                                        <span class="ba-row-title">
                                            {{ $d->content?->subject ?? '-' }}
                                            @if (isset($unread[$d->id]))<span class="ba-new">Belum dibaca</span>@endif
                                        </span>
                                        <span class="ba-row-meta">
                                            {{ $d->document_number }}, {{ $d->companydocumentconfigs?->documenttypes?->document_name }}, diajukan {{ $d->employee?->employee_name }} {{ $fmtDate($d->content?->submitted_at ?? $d->issued_date) }}
                                        </span>
                                    </span>
                                    <span class="ba-row-side">
                                        <span class="ba-st is-{{ $d->status }}">{{ $d->status_label }}</span>
                                        @if (in_array($d->status, \App\Models\Documents::BA_IN_APPROVAL, true) && $cur)
                                            <span>{{ $cur->employee_id === $me->id ? 'Giliran Anda' : 'Menunggu ' . ($cur->employee?->employee_name ?? '-') }}</span>
                                        @elseif ($d->status === 'approved')
                                            <span>Sah {{ $fmtDate($d->content?->completed_at) }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    @if ($documents->hasPages())
                        <div class="ba-pager">{{ $documents->links() }}</div>
                    @endif
                @endif
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')) });
        @endif
    </script>
@endpush
