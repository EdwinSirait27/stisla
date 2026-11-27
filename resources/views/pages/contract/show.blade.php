@extends('layouts.app')
@section('title', 'Kontrak ' . ($contract->employee?->employee_name ?? ''))
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.contract._styles')
@endpush

@php
    $c = $contract;
    $e = $c->employee;
    $tgl = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : null;
    $jam = fn($d) => $d ? \Carbon\Carbon::parse($d)->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i') : '';
    $rp = fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-';
    $left = $c->contract_status === 'Active' ? $c->daysLeft() : null;
    $badge = ['Draft' => $c->returned_at ? 'is-returned' : 'is-draft', 'Pending' => 'is-waiting', 'Active' => 'is-approved', 'Expired' => 'is-draft', 'Terminated' => 'is-cancelled', 'Cancelled' => 'is-cancelled'][$c->contract_status] ?? '';
    $statusLabel = $c->status_label;
    $steps = [
        1 => ['who' => $c->approver1, 'at' => $c->contract_status !== 'Draft' ? $c->approver_1_at : null, 'role' => 'HR, mengajukan'],
        2 => ['who' => $c->approver2, 'at' => $c->contract_status !== 'Draft' ? $c->approver_2_at : null, 'role' => 'Approver 2'],
    ];
    if ($c->approver_3) $steps[3] = ['who' => $c->approver3, 'at' => $c->contract_status !== 'Draft' ? $c->approver_3_at : null, 'role' => 'Approver 3'];
    $nowStep = $c->contract_status === 'Draft' ? 1 : (($c->currentStep()[0] ?? null) === 'approver_2' ? 2 : (($c->currentStep()[0] ?? null) === 'approver_3' ? 3 : null));
@endphp

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('contract') }}">Kontrak</a></li><li aria-current="page">{{ $e?->employee_name }}</li></ol>
                    <h1 class="pr-title">Kontrak {{ $c->contract_type }} {{ $e?->employee_name }}</h1>
                    <p class="pr-lede">
                        <span class="sk-badge {{ $badge }}">{{ $statusLabel }}</span>
                        {{ $tgl($c->start_date) }} sampai {{ $tgl($c->end_date) ?? 'tanpa batas waktu' }}.
                        @if ($left !== null)
                            @if ($left > 0) <span class="ct-left {{ $left <= 30 ? 'is-soon' : '' }}">{{ $left }} hari lagi.</span>
                            @elseif ($left === 0) <span class="ct-left is-soon">Berakhir hari ini.</span>
                            @else <span class="ct-left is-late">Sudah lewat, berubah menjadi Berakhir pada proses malam ini.</span> @endif
                        @endif
                    </p>
                </div>
                <div class="pr-actions">
                    @if ($c->file_path)
                        <a href="{{ route('contractfile', $c->id) }}" class="pr-btn" target="_blank" rel="noopener"><i class="fas fa-file-pdf" aria-hidden="true"></i> Dokumen</a>
                    @endif
                    @if ($canEdit)
                        <a href="{{ route('editcontract', $c->id) }}" class="pr-btn"><i class="fas fa-pen" aria-hidden="true"></i> {{ $c->contract_status === 'Active' ? 'Ubah dokumen' : 'Ubah' }}</a>
                    @endif
                    @if ($canSubmit)
                        <form method="POST" action="{{ route('submitcontract', $c->id) }}" class="sk-confirm" data-confirm="Ajukan kontrak ini ke {{ $c->approver2?->employee_name ?? 'approver 2' }}? Setelah diajukan, kontrak tidak bisa diubah kecuali dikembalikan.">
                            @csrf<button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-paper-plane" aria-hidden="true"></i> Ajukan untuk persetujuan</button>
                        </form>
                    @endif
                    @if ($canCancel)
                        <form method="POST" action="{{ route('cancelcontract', $c->id) }}" class="sk-confirm" data-confirm="Batalkan kontrak ini? Kontrak yang dibatalkan tidak bisa diajukan lagi.">
                            @csrf<button type="submit" class="pr-btn pr-btn-danger">Batalkan</button>
                        </form>
                    @endif
                    @if ($canManage && in_array($c->contract_status, ['Expired', 'Terminated', 'Cancelled'], true))
                        <a href="{{ route('createcontract') }}?employee={{ $c->employee_id }}" class="pr-btn pr-btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Kontrak baru</a>
                    @endif
                </div>
            </header>

            @if (session('success'))<p class="sk-notice is-ok" role="status">{{ session('success') }}</p>@endif
            @if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif
            @if ($errors->any())
                <div class="emp-errors" role="alert"><strong>Belum bisa diproses.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if ($c->contract_status === 'Draft' && $c->returned_at)
                <p class="sk-notice is-warn"><strong>Dikembalikan oleh {{ $returnedBy ?? 'approver' }} ({{ $jam($c->returned_at) }}):</strong> {{ $c->returned_note }}</p>
            @elseif ($c->contract_status === 'Draft' && $canSubmit)
                <p class="sk-notice is-info">Kontrak ini masih Draft dan belum berlaku. Periksa isinya, lalu tekan <strong>Ajukan untuk persetujuan</strong>.</p>
            @elseif ($c->contract_status === 'Pending')
                <p class="sk-notice is-info">Menunggu persetujuan. Kontrak berlaku setelah semua approver menyetujui.</p>
            @elseif (in_array($c->contract_status, ['Active', 'Expired'], true) && $c->approved_at && $c->skLetter && $c->approver_1_at && $c->skLetter->approver_1_at && $c->approver_1_at->equalTo($c->skLetter->approver_1_at))
                <p class="sk-notice is-ok">Berlaku berdasarkan SK <a href="{{ route('SkLetters.show', $c->sk_letter_id) }}">{{ $c->skLetter->sk_number }}</a> tanpa approval ulang, karena isinya sama dengan SK yang sudah disetujui. Persetujuan di samping adalah persetujuan SK.</p>
            @elseif (in_array($c->contract_status, ['Active', 'Expired'], true) && $c->approved_at)
                <p class="sk-notice is-ok">Disetujui penuh {{ $jam($c->approved_at) }}.</p>
            @endif
            @if ($myTurn)
                <section class="pr-panel" aria-labelledby="h-decide">
                    <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-decide">Giliran Anda menyetujui</h2><span class="pr-panel-sub">Periksa masa kontrak, penempatan, dan gaji di bawah. Tanda tangan dari profil Anda ikut tersimpan.</span></div></div>
                    <div class="sk-decide">
                        <form method="POST" action="{{ route('approvecontract', $c->id) }}" class="sk-confirm" data-confirm="Setujui kontrak {{ $e?->employee_name }}?">
                            @csrf<button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('returncontract', $c->id) }}">
                            @csrf
                            <label for="return-note" class="emp-help" style="font-weight:700;color:var(--pr-ink)">Atau kembalikan ke HR untuk diperbaiki</label>
                            <textarea id="return-note" name="note" class="emp-input" rows="2" minlength="5" maxlength="1000" required placeholder="Tulis yang perlu diperbaiki">{{ old('note') }}</textarea>
                            <button type="submit" class="pr-btn" style="margin-top:8px">Kembalikan</button>
                        </form>
                    </div>
                </section>
            @endif
            @if ($c->contract_status === 'Terminated')
                <p class="sk-notice is-warn"><strong>Diakhiri lebih awal per {{ $tgl($c->terminated_at) }}{{ $terminatedBy ? ' oleh ' . $terminatedBy : '' }}:</strong> {{ $c->terminated_reason }}</p>
            @endif
            @if (!$c->file_path && $c->contract_status === 'Active')
                <p class="sk-notice is-info">Dokumen kontrak yang ditandatangani belum diunggah.@if ($canManage) Unggah lewat Ubah.@endif</p>
            @endif

            <div class="sk-layout">
                <div>
                    <section class="pr-panel" aria-labelledby="h-terms">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-terms">Isi kontrak</h2></div></div>
                        <dl class="sk-meta">
                            <div><dt>Karyawan</dt><dd>{{ $e?->employee_name ?? '-' }}{{ $e?->employee_pengenal ? ', NIP ' . $e->employee_pengenal : '' }}</dd></div>
                            <div><dt>Jenis</dt><dd>{{ $c->contract_type }}</dd></div>
                            <div><dt>Perusahaan penerbit</dt><dd>{{ $c->issuerCompany?->name ?? '-' }}</dd></div>
                            <div><dt>Masa kontrak</dt><dd>{{ $tgl($c->start_date) }} sampai {{ $tgl($c->end_date) ?? 'tanpa batas waktu' }}</dd></div>
                            <div><dt>Jabatan</dt><dd>{{ $c->position?->name ?? '-' }}</dd></div>
                            <div><dt>Departemen</dt><dd>{{ $c->department?->department_name ?? '-' }}</dd></div>
                            <div><dt>Lokasi</dt><dd>{{ $c->store?->name ?? '-' }}</dd></div>
                            <div><dt>Grading, group</dt><dd>{{ collect([$c->grading?->grading_name, $c->group?->group_name])->filter()->implode(', ') ?: '-' }}</dd></div>
                            <div><dt>Gaji pokok</dt><dd>{{ $rp($c->basic_salary) }}</dd></div>
                            <div><dt>Tunjangan jabatan</dt><dd>{{ $rp($c->positional_allowance) }}</dd></div>
                            <div><dt>Daily rate</dt><dd>{{ $rp($c->daily_rate) }}</dd></div>
                            <div><dt>Dasar</dt><dd>@if ($c->skLetter)<a href="{{ route('SkLetters.show', $c->sk_letter_id) }}">SK {{ $c->skLetter->sk_number }}</a>@else Dibuat langsung{{ $createdBy ? ' oleh ' . $createdBy : '' }}@endif</dd></div>
                        </dl>
                        @if ($c->notes)<p class="sk-text" style="margin:0"><strong>Catatan:</strong> {{ $c->notes }}</p>@endif
                    </section>

                    @if ($canManage && $c->contract_status === 'Active')
                        <section class="pr-panel" aria-labelledby="h-end">
                            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-end">Akhiri lebih awal</h2><span class="pr-panel-sub">Untuk resign, PHK, atau diganti kontrak baru. Kontrak yang selesai sesuai tanggal tidak perlu diakhiri manual.</span></div></div>
                            <form method="POST" action="{{ route('terminatecontract', $c->id) }}" class="emp-fields sk-fields-3" id="end-form">
                                @csrf
                                <div class="emp-field">
                                    <label for="terminated_at">Berakhir per tanggal</label>
                                    <input type="date" id="terminated_at" name="terminated_at" class="emp-input" required value="{{ old('terminated_at', today()->format('Y-m-d')) }}" min="{{ $c->start_date?->format('Y-m-d') }}">
                                </div>
                                <div class="emp-field sk-span-2">
                                    <label for="terminated_reason">Alasan</label>
                                    <input type="text" id="terminated_reason" name="terminated_reason" class="emp-input" required minlength="5" maxlength="1000" value="{{ old('terminated_reason') }}" placeholder="Mis. resign per tanggal tersebut">
                                </div>
                                <div class="emp-field"><button type="submit" class="pr-btn pr-btn-danger">Akhiri kontrak</button></div>
                            </form>
                        </section>
                    @endif
                </div>

                <aside>
                    <section class="pr-panel" aria-labelledby="h-steps">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-steps">Persetujuan</h2><span class="pr-panel-sub">Berurutan, dari atas ke bawah.</span></div></div>
                        <ol class="sk-steps">
                            @foreach ($steps as $n => $st)
                                @php
                                    $state = $st['at'] ? 'is-done' : ($nowStep === $n && !in_array($c->contract_status, ['Cancelled'], true) ? 'is-now' : '');
                                @endphp
                                <li class="sk-step {{ $state }}">
                                    <span class="sk-step-num" aria-hidden="true">{{ $n }}</span>
                                    <div>
                                        <strong>{{ $st['who']?->employee_name ?? ($n === 1 ? 'Belum diajukan' : 'Belum dipilih') }}</strong>
                                        <span>{{ $st['role'] }}.
                                            @if ($st['at']) {{ $n === 1 ? 'Diajukan' : 'Disetujui' }} {{ $jam($st['at']) }}
                                            @elseif ($state === 'is-now') {{ $n === 1 ? 'Menunggu diajukan' : 'Sedang menunggu persetujuan' }}
                                            @else Belum @endif
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                    @if ($history->isNotEmpty() || $canManage)
                    <section class="pr-panel" aria-labelledby="h-history">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-history">Kontrak lain karyawan ini</h2></div></div>
                        @if ($history->isEmpty())
                            <p class="sk-empty">Belum ada kontrak lain.</p>
                        @else
                            <ol class="sk-log">
                                @foreach ($history as $h)
                                    <li><a href="{{ route('showcontract', $h->id) }}">{{ $h->contract_type }}, {{ $tgl($h->start_date) }} sampai {{ $tgl($h->end_date) ?? 'tanpa batas' }}</a><time>{{ \App\Models\Contract::getContractStatusOptions()[$h->contract_status] ?? $h->contract_status }}</time></li>
                                @endforeach
                            </ol>
                        @endif
                    </section>
                    @endif
                    <section class="pr-panel" aria-labelledby="h-log">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-log">Riwayat</h2><span class="pr-panel-sub">Nominal gaji tidak dicatat di riwayat.</span></div></div>
                        @if ($activities->isEmpty())
                            <p class="sk-empty">Belum ada riwayat.</p>
                        @else
                            <ol class="sk-log">@foreach ($activities as $a)<li>{{ $a->description }}<time>{{ $jam($a->created_at) }}</time></li>@endforeach</ol>
                        @endif
                    </section>
                </aside>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.querySelectorAll('form.sk-confirm').forEach(form => form.addEventListener('submit', function(e) {
            if (form.dataset.ok) return;
            e.preventDefault();
            Swal.fire({ icon: 'question', text: form.dataset.confirm, showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal' })
                .then(r => { if (r.isConfirmed) { form.dataset.ok = '1'; form.querySelector('button[type=submit]').disabled = true; form.submit(); } });
        }));
        document.getElementById('end-form')?.addEventListener('submit', function(e) {
            if (this.dataset.ok) return;
            e.preventDefault();
            Swal.fire({ icon: 'warning', text: 'Akhiri kontrak ini? Kontrak yang diakhiri tidak bisa diaktifkan lagi.', showCancelButton: true, confirmButtonText: 'Akhiri', cancelButtonText: 'Batal' })
                .then(r => { if (r.isConfirmed) { this.dataset.ok = '1'; this.submit(); } });
        });
    </script>
@endpush
