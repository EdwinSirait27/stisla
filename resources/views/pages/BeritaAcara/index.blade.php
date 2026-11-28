@extends('layouts.app')
@section('title', 'Berita acara')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
    @include('pages.BeritaAcara._list_styles')
@endpush

@section('main')
    @php
        $tabLabels = ['approval' => 'Perlu persetujuan saya', 'mine' => 'Ajuan saya', 'cc' => 'Tembusan', 'all' => 'Semua berita acara'];
        $tabCount = ['approval' => $counts['approval'], 'mine' => $counts['mine_revision'], 'cc' => $counts['cc_unread'], 'all' => $overall['stuck'] ?? null];
        $tabHint = ['approval' => 'menunggu tanda tangan Anda', 'mine' => 'perlu revisi', 'cc' => 'belum dibaca', 'all' => "tertahan lebih dari {$stuckDays} hari"];
        $tabSub = [
            'approval' => 'Berita acara yang sedang menunggu persetujuan Anda, yang paling lama di atas bila diurutkan.',
            'mine'     => 'Semua yang Anda ajukan. Yang dikembalikan perlu direvisi lalu diajukan ulang.',
            'cc'       => 'Berita acara yang ditembuskan ke Anda; tembusan diberi tahu saat berita acara sah.',
            'all'      => 'Seluruh berita acara semua karyawan. Kirim pengingat ke approver yang tertahan dari halaman detailnya.',
        ];
        $empty = [
            'approval' => 'Tidak ada berita acara yang menunggu persetujuan Anda.',
            'mine'     => 'Anda belum pernah mengajukan berita acara.',
            'cc'       => 'Belum ada berita acara yang ditembuskan ke Anda.',
            'all'      => 'Belum ada berita acara yang cocok.',
        ];
        $fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d M Y') : '-';
        $ago = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->diffForHumans() : '';
        $days = fn($d) => $d ? (int) \Carbon\Carbon::parse($d)->diffInDays(now()) : 0;
        $filtered = request()->hasAny(['q', 'status', 'type', 'company']) && (request('q') || request('status') || request('type') || request('company'));
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li aria-current="page">Berita acara</li></ol>
                    <h1 class="pr-title">Berita acara</h1>
                    <p class="pr-lede">Disetujui berurutan, dimulai dari atasan pengaju. Setiap persetujuan disertai tanda tangan, dan tembusan diberi tahu saat berita acara sah.</p>
                </div>
                @if ($canCreate)
                    <div class="pr-actions">
                        <a href="{{ route('beritaacara.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Buat berita acara</a>
                    </div>
                @endif
            </header>

            @if (session('success'))<p class="bl-notice is-ok" role="status">{{ session('success') }}</p>@endif

            {{-- Ringkasan: pribadi, atau seluruh berita acara di tab Semua --}}
            <section class="pr-panel" aria-label="Ringkasan">
                @if ($tab === 'all' && $overall)
                    <dl class="bl-stats">
                        <a href="{{ route('beritaacara.index', ['tab' => 'all', 'status' => 'stuck', 'sort' => 'waiting']) }}" class="{{ $overall['stuck'] ? 'is-warn' : '' }}"><dt>Tertahan</dt><dd>{{ $overall['stuck'] }}</dd><span>menunggu lebih dari {{ $stuckDays }} hari</span></a>
                        <a href="{{ route('beritaacara.index', ['tab' => 'all', 'status' => 'pending']) }}"><dt>Berjalan</dt><dd>{{ $overall['open'] }}</dd><span>menunggu persetujuan</span></a>
                        <a href="{{ route('beritaacara.index', ['tab' => 'all', 'status' => 'revision']) }}"><dt>Perlu revisi</dt><dd>{{ $overall['revision'] }}</dd><span>dikembalikan ke pengaju</span></a>
                        <a href="{{ route('beritaacara.index', ['tab' => 'all', 'status' => 'approved']) }}"><dt>Sah</dt><dd>{{ $overall['approved'] }}</dd><span>disetujui semua approver</span></a>
                    </dl>
                @else
                    <dl class="bl-stats">
                        <a href="{{ route('beritaacara.index', ['tab' => 'approval']) }}" class="{{ $counts['approval'] ? 'is-warn' : '' }}"><dt>Perlu persetujuan saya</dt><dd>{{ $counts['approval'] }}</dd><span>giliran Anda menandatangani</span></a>
                        <a href="{{ route('beritaacara.index', ['tab' => 'mine', 'status' => 'pending']) }}"><dt>Ajuan saya berjalan</dt><dd>{{ $counts['mine_open'] }}</dd><span>dari {{ $counts['mine'] }} ajuan</span></a>
                        <a href="{{ route('beritaacara.index', ['tab' => 'mine', 'status' => 'revision']) }}" class="{{ $counts['mine_revision'] ? 'is-warn' : '' }}"><dt>Perlu revisi</dt><dd>{{ $counts['mine_revision'] }}</dd><span>ajuan Anda yang dikembalikan</span></a>
                        <a href="{{ route('beritaacara.index', ['tab' => 'cc']) }}"><dt>Tembusan belum dibaca</dt><dd>{{ $counts['cc_unread'] }}</dd><span>ditembuskan ke Anda</span></a>
                    </dl>
                @endif
            </section>

            <section class="pr-panel" aria-labelledby="ba-title">
                @if (count($tabs) > 1)
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
                @endif
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="ba-title">{{ $tabLabels[$tab] }}</h2>
                        <span class="pr-panel-sub">{{ $documents->total() }} berita acara{{ $filtered ? ' sesuai filter' : '' }}. {{ $tabSub[$tab] }}</span>
                    </div>
                </div>

                <form method="GET" class="bl-filters" role="search" aria-label="Saring berita acara">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <div class="bl-field bl-grow">
                        <label for="ba-q">Cari</label>
                        <input type="search" id="ba-q" name="q" value="{{ request('q') }}" placeholder="Nomor, perihal, atau nama pengaju">
                    </div>
                    <div class="bl-field">
                        <label for="ba-status">Status</label>
                        <select id="ba-status" name="status">
                            <option value="">Semua status</option>
                            @if ($tab === 'all')<option value="stuck" @selected($status === 'stuck')>Tertahan lebih dari {{ $stuckDays }} hari</option>@endif
                            @foreach (['pending', 'in_review', 'revision', 'approved', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected($status === $s)>{{ \App\Models\Documents::STATUS_LABELS[$s] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($tab === 'all')
                        <div class="bl-field">
                            <label for="ba-type">Jenis</label>
                            <select id="ba-type" name="type"><option value="">Semua jenis</option>@foreach ($types as $id => $name)<option value="{{ $id }}" @selected(request('type') === $id)>{{ $name }}</option>@endforeach</select>
                        </div>
                        <div class="bl-field">
                            <label for="ba-company">Perusahaan</label>
                            <select id="ba-company" name="company"><option value="">Semua perusahaan</option>@foreach ($companies as $id => $name)<option value="{{ $id }}" @selected(request('company') === $id)>{{ $name }}</option>@endforeach</select>
                        </div>
                    @endif
                    <div class="bl-field">
                        <label for="ba-sort">Urutkan</label>
                        <select id="ba-sort" name="sort">
                            <option value="status" @selected($sort === 'status')>Status, lalu terbaru</option>
                            <option value="waiting" @selected($sort === 'waiting')>Paling lama menunggu</option>
                        </select>
                    </div>
                    <div class="bl-actions">
                        <button type="submit" class="pr-btn">Terapkan</button>
                        @if ($filtered)<a href="{{ route('beritaacara.index', ['tab' => $tab]) }}" class="pr-btn">Reset</a>@endif
                    </div>
                </form>

                @if ($documents->isEmpty())
                    <div class="bl-empty">
                        <p>{{ $filtered ? 'Tidak ada berita acara yang cocok dengan filter ini.' : $empty[$tab] }}</p>
                        @if ($filtered)
                            <a href="{{ route('beritaacara.index', ['tab' => $tab]) }}" class="pr-btn pr-btn-sm">Hapus filter</a>
                        @elseif ($tab === 'mine' && $canCreate)
                            <a href="{{ route('beritaacara.create') }}" class="pr-btn pr-btn-sm pr-btn-primary">Buat berita acara</a>
                        @endif
                    </div>
                @else
                    <ul class="bl-list">
                        @foreach ($documents as $d)
                            @php
                                $cur = $current->get($d->id);
                                $prog = $progress->get($d->id);
                                $open = in_array($d->status, \App\Models\Documents::BA_IN_APPROVAL, true);
                                $waitDays = $cur ? $days($cur->updated_at) : 0;
                                $stuck = $open && $cur && $waitDays >= $stuckDays;
                                $mineTurn = $open && $cur && $cur->employee_id === $me->id;
                            @endphp
                            <li class="{{ $mineTurn ? 'is-turn' : ($stuck ? 'is-stuck' : '') }}">
                                <a href="{{ route('beritaacara.show', $d->id) }}" class="bl-row">
                                    <span class="bl-main">
                                        <span class="bl-title">
                                            {{ $d->content?->subject ?? '-' }}
                                            @if (isset($unread[$d->id]))<span class="ba-new">Belum dibaca</span>@endif
                                        </span>
                                        <span class="bl-meta">
                                            {{ $d->document_number }}, {{ $d->companydocumentconfigs?->documenttypes?->document_name }}.
                                            Diajukan {{ $d->employee_id === $me->id ? 'Anda' : ($d->employee?->employee_name ?? '-') }}{{ $tab === 'all' && $d->companydocumentconfigs?->company ? ' (' . $d->companydocumentconfigs->company->name . ')' : '' }}, {{ $fmtDate($d->content?->submitted_at ?? $d->issued_date) }}.
                                        </span>
                                        <span class="bl-extra">
                                            @if ($d->content?->location)<span><i class="fas fa-location-dot" aria-hidden="true"></i> {{ \Illuminate\Support\Str::limit($d->content->location, 60) }}</span>@endif
                                            @if ($d->attachments_count)<span><i class="fas fa-paperclip" aria-hidden="true"></i> {{ $d->attachments_count }} lampiran</span>@endif
                                            @if ($d->recipients_count)<span><i class="fas fa-users" aria-hidden="true"></i> {{ $d->recipients_count }} tembusan</span>@endif
                                            @if (($d->content?->round ?? 1) > 1)<span>Pengajuan ke-{{ $d->content->round }}</span>@endif
                                        </span>
                                    </span>
                                    <span class="bl-side">
                                        <span class="ba-st is-{{ $d->status }}">{{ $d->status_label }}</span>
                                        @if ($prog && $prog->total)
                                            <span class="bl-progress" aria-label="{{ (int) $prog->done }} dari {{ $prog->total }} approver menyetujui">
                                                <span class="bl-bar"><i style="transform:scaleX({{ number_format($prog->done / $prog->total, 3, '.', '') }})"></i></span>
                                                {{ (int) $prog->done }} dari {{ $prog->total }} menyetujui
                                            </span>
                                        @endif
                                        @if ($open && $cur)
                                            <span class="bl-wait {{ $stuck ? 'is-stuck' : '' }}">
                                                {{ $mineTurn ? 'Giliran Anda' : 'Menunggu ' . ($cur->employee?->employee_name ?? '-') }}, {{ $ago($cur->updated_at) }}
                                            </span>
                                        @elseif ($d->status === 'approved')
                                            <span class="bl-wait">Sah {{ $fmtDate($d->content?->completed_at) }}</span>
                                        @elseif ($d->status === 'revision')
                                            <span class="bl-wait is-stuck">{{ $d->employee_id === $me->id ? 'Perlu Anda revisi' : 'Menunggu revisi pengaju' }}</span>
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
    <script>
        // Filter langsung diterapkan saat pilihan diganti (tombol Terapkan tetap ada tanpa JavaScript)
        document.querySelectorAll('.bl-filters select').forEach(el => el.addEventListener('change', () => el.form.submit()));
    </script>
@endpush
