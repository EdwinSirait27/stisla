@extends('layouts.app')
@section('title', 'Pengaturan dokumen perusahaan')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
@endpush

@section('main')
    @php $fmt = fn($v) => number_format((int) $v, 0, ',', '.'); @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li aria-current="page">Pengaturan per perusahaan</li></ol>
                    <h1 class="pr-title">Pengaturan dokumen perusahaan</h1>
                    <p class="pr-lede">Jenis dokumen mana yang bisa diterbitkan untuk tiap perusahaan, beserta data tambahan yang dipakai templatenya (bank, PIC). Klik sel untuk mengubah atau mengaktifkan.</p>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('documenttype.index') }}" class="pr-btn">Jenis dokumen</a>
                    <a href="{{ route('documentconfig.create') }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Tambah pengaturan</a>
                </div>
            </header>

            <section class="pr-panel" aria-label="Ringkasan">
                <dl class="tl-stats">
                    <div><dt>Pengaturan aktif</dt><dd>{{ $fmt($stats['active']) }}</dd><span>dari {{ $fmt($stats['total']) }} pengaturan</span></div>
                    <div class="{{ $stats['ambiguous'] ? 'is-warn' : '' }}"><dt>Pengaturan ganda</dt><dd>{{ $fmt($stats['ambiguous']) }}</dd><span>perusahaan dan jenis yang sama punya lebih dari satu pengaturan aktif; sistem memakai yang pertama ditemukan</span></div>
                    <div><dt>Dokumen terbit</dt><dd>{{ $fmt($stats['documents']) }}</dd><span>lewat pengaturan ini</span></div>
                </dl>
            </section>

            <section class="pr-panel" aria-labelledby="dc-title">
                <div class="pr-panel-head">
                    <div>
                        <h2 class="pr-panel-title" id="dc-title">Jenis dokumen per perusahaan</h2>
                        <span class="pr-panel-sub">{{ $types->count() }} jenis dokumen, {{ $companies->count() }} perusahaan.</span>
                    </div>
                </div>
                <ul class="dc-legend" aria-label="Keterangan">
                    <li><span class="dc-swatch is-on"></span>Aktif, bisa diterbitkan</li>
                    <li><span class="dc-swatch is-off"></span>Nonaktif</li>
                    <li><span class="dc-swatch is-dup"></span>Aktif ganda, perlu dicek</li>
                </ul>
                <div class="dc-wrap">
                    <table class="dc-grid">
                        <thead>
                            <tr>
                                <th scope="col">Jenis dokumen</th>
                                @foreach ($companies as $co)
                                    <th scope="col">{{ $co->nickname }}<small>{{ $co->name }}</small></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($types as $t)
                                <tr>
                                    <th scope="row">
                                        {{ $t->document_name }} <span class="dt-code">{{ $t->nickname }}</span>
                                        @if (!$t->is_active)
                                            <span class="pr-cell-sub"><span class="dt-status is-off">Jenis nonaktif</span></span>
                                        @endif
                                        @if (!($templateOk[$t->id] ?? false))
                                            <span class="pr-cell-sub"><span class="dt-warn">Template belum ada</span></span>
                                        @endif
                                    </th>
                                    @foreach ($companies as $co)
                                        @php
                                            $list = $matrix[$t->id][$co->id] ?? [];
                                            $dup = collect($list)->where('active', true)->where('dw', false)->count() > 1;
                                        @endphp
                                        <td>
                                            <div class="dc-cell">
                                                @foreach ($list as $c)
                                                    <a href="{{ route('documentconfig.edit', $c['hash']) }}"
                                                       class="dc-item {{ !$c['active'] ? 'is-off' : ($dup && !$c['dw'] ? 'is-dup' : 'is-on') }}"
                                                       aria-label="Ubah {{ $t->nickname }} untuk {{ $co->nickname }}{{ $c['label'] ? ', ' . $c['label'] : '' }}">
                                                        <strong>{{ $c['active'] ? 'Aktif' : 'Nonaktif' }}{{ $c['dw'] ? ', khusus DW' : '' }}</strong>
                                                        @if ($c['label'])<span>{{ $c['label'] }}</span>@endif
                                                        <span>{{ $c['documents'] ? $fmt($c['documents']) . ' dokumen' : 'Belum ada dokumen' }}</span>
                                                    </a>
                                                @endforeach
                                                @if (!$list)
                                                    <a href="{{ route('documentconfig.create', ['company' => $co->id, 'type' => $t->id]) }}" class="dc-add"
                                                       aria-label="Aktifkan {{ $t->nickname }} untuk {{ $co->nickname }}"><i class="fas fa-plus" aria-hidden="true"></i> Aktifkan</a>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Tersimpan', text: @json(session('success')) });
        @endif
    </script>
@endpush
