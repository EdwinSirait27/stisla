{{-- Daftar aktivitas dengan filter. Butuh: $modules, $eventMap. Opsional: $logNames, $events (dari index) --}}
@php
    $logNames = $logNames ?? collect(array_keys($modules));
    $events = $events ?? collect(array_keys($eventMap));
@endphp
<section class="pr-panel" aria-labelledby="feed-title">
    <div class="pr-panel-head">
        <div>
            <h2 class="pr-panel-title" id="feed-title">{{ $feedTitle }}</h2>
            <span class="pr-panel-sub" id="feed-count">Memuat</span>
        </div>
        <div class="pr-search">
            <i class="fas fa-search" aria-hidden="true"></i>
            <label for="feed-search" class="sr-only">Cari keterangan</label>
            <input type="search" id="feed-search" placeholder="Cari keterangan{{ $feedUser ? '' : ' atau nama' }}">
        </div>
    </div>
    <div class="emp-filters">
        <div class="pr-field">
            <label for="feed-range">Rentang</label>
            <select id="feed-range" class="emp-select">
                <option value="today">Hari ini</option>
                <option value="7d">7 hari terakhir</option>
                <option value="30d" selected>30 hari terakhir</option>
                <option value="90d">90 hari terakhir</option>
                <option value="">Semua waktu</option>
                <option value="custom">Pilih tanggal</option>
            </select>
        </div>
        <div class="pr-field js-custom" hidden>
            <label for="feed-from">Dari</label>
            <input type="date" id="feed-from" class="emp-input">
        </div>
        <div class="pr-field js-custom" hidden>
            <label for="feed-to">Sampai</label>
            <input type="date" id="feed-to" class="emp-input">
        </div>
        <div class="pr-field">
            <label for="feed-log">Modul</label>
            <select id="feed-log" class="emp-select">
                <option value="">Semua modul</option>
                @foreach ($logNames as $ln)
                    <option value="{{ $ln }}">{{ $modules[$ln] ?? \Illuminate\Support\Str::headline($ln) }}</option>
                @endforeach
            </select>
        </div>
        <div class="pr-field">
            <label for="feed-event">Aksi</label>
            <select id="feed-event" class="emp-select">
                <option value="">Semua aksi</option>
                @foreach ($events as $ev)
                    <option value="{{ $ev }}">{{ $eventMap[$ev][0] ?? \Illuminate\Support\Str::headline($ev) }}</option>
                @endforeach
            </select>
        </div>
        @unless ($feedUser)
            <div class="pr-field">
                <label for="feed-causer">Pelaku</label>
                <select id="feed-causer" class="emp-select">
                    <option value="">Semua</option>
                    <option value="system">Sistem (tanpa user)</option>
                </select>
            </div>
        @endunless
        <div class="emp-filter-actions">
            <button type="button" id="feed-reset" class="pr-btn pr-btn-sm" hidden>Hapus filter</button>
        </div>
    </div>
    <div class="pr-table-wrap">
        <table id="feed-table" class="table">
            <thead>
                <tr>
                    <th style="width:150px">Waktu</th>
                    @unless ($feedUser)<th>Oleh</th>@endunless
                    <th>Modul</th>
                    <th>Aksi</th>
                    <th>Keterangan dan perubahan</th>
                </tr>
            </thead>
        </table>
    </div>
</section>
