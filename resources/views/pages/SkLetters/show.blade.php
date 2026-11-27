@extends('layouts.app')
@section('title', 'SK ' . $skletter->sk_number)
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.SkLetters._styles')
@endpush

@php
    $sk = $skletter;
    $type = $sk->sktype;
    $tgl = fn($d) => $d ? \Carbon\Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '-';
    $jam = fn($d) => $d ? \Carbon\Carbon::parse($d)->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i') : '';
    $rp = fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : null;
    $badge = match ($sk->status) {
        'Draft' => $sk->returned_at ? 'is-returned' : 'is-draft',
        'Approved HR', 'Approved Director' => 'is-waiting',
        \App\Models\SkLetter::FINAL_STATUS => 'is-approved',
        'Cancelled' => 'is-cancelled',
        default => '',
    };
    $applyLater = $sk->isFinal() && $sk->effective_date && $sk->effective_date->isFuture();
    $menetapkan = trim(html_entity_decode(strip_tags(preg_replace('#<(br|/p|/li)\s*/?>#i', "\n", (string) $sk->menetapkan_text)), ENT_QUOTES));
    $steps = [
        1 => ['who' => $sk->approver1, 'at' => $sk->approver_1_at, 'role' => 'HR, mengajukan'],
        2 => ['who' => $sk->approver2, 'at' => $sk->approver_2_at, 'role' => 'Approver 2'],
    ];
    if ($sk->approver_3) $steps[3] = ['who' => $sk->approver3, 'at' => $sk->approver_3_at, 'role' => 'Approver 3'];
    $nowStep = match ($sk->status) { 'Draft' => 1, 'Approved HR' => 2, 'Approved Director' => 3, default => null };
@endphp

@section('main')
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li><a href="{{ route('SkLetters') }}">SK</a></li><li aria-current="page">{{ $sk->sk_number }}</li></ol>
                    <h1 class="pr-title">{{ $sk->title ?: ($type?->sk_name ?? 'Surat keputusan') }}</h1>
                    <p class="pr-lede">
                        <span class="sk-badge {{ $badge }}">{{ $sk->status_label }}</span>
                        {{ $type?->sk_name }}, nomor {{ $sk->sk_number }}, {{ $sk->company?->name }}. Berlaku {{ $tgl($sk->effective_date) }}{{ $sk->inactive_date ? ' sampai ' . $tgl($sk->inactive_date) : '' }}.
                    </p>
                </div>
                <div class="pr-actions">
                    <a href="{{ route('SkLetters.pdf', $sk->id) }}" class="pr-btn" target="_blank" rel="noopener"><i class="fas fa-file-pdf" aria-hidden="true"></i> PDF</a>
                    @if ($canEdit)
                        <a href="{{ route('SkLetters.edit', $sk->id) }}" class="pr-btn"><i class="fas fa-pen" aria-hidden="true"></i> Ubah</a>
                    @endif
                    @if ($canSubmit)
                        <form method="POST" action="{{ route('SkLetters.submit', $sk->id) }}" class="sk-confirm" data-confirm="Ajukan SK ini ke {{ $sk->approver2?->employee_name ?? 'approver 2' }}? Setelah diajukan, SK tidak bisa diubah kecuali dikembalikan.">
                            @csrf
                            <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-paper-plane" aria-hidden="true"></i> Ajukan untuk persetujuan</button>
                        </form>
                    @endif
                    @if ($canCancel)
                        <form method="POST" action="{{ route('SkLetters.cancel', $sk->id) }}" class="sk-confirm" data-confirm="Batalkan SK {{ $sk->sk_number }}? SK yang dibatalkan tidak bisa diajukan lagi.">
                            @csrf
                            <button type="submit" class="pr-btn pr-btn-danger">Batalkan</button>
                        </form>
                    @endif
                </div>
            </header>

            @if (session('success'))<p class="sk-notice is-ok" role="status">{{ session('success') }}</p>@endif
            @if (session('error'))<p class="sk-notice is-warn" role="alert">{{ session('error') }}</p>@endif
            @if ($errors->any())
                <div class="emp-errors" role="alert"><strong>Belum bisa diproses.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if ($sk->status === 'Draft' && $sk->returned_at)
                <p class="sk-notice is-warn"><strong>Dikembalikan oleh {{ $returnedBy ?? 'approver' }} ({{ $jam($sk->returned_at) }}):</strong> {{ $sk->returned_note }}</p>
            @elseif ($sk->status === 'Draft' && $canSubmit)
                <p class="sk-notice is-info">SK ini masih Draft. Periksa isinya, lalu tekan <strong>Ajukan untuk persetujuan</strong>.</p>
            @elseif ($sk->isFinal())
                <p class="sk-notice is-ok">
                    Disetujui penuh {{ $jam($sk->completed_at) }}.
                    {{ $applyLater ? 'Jabatan dan penempatan berubah otomatis pada ' . $tgl($sk->effective_date) . '; gaji baru sudah dicatat dan berlaku mulai tanggal itu.' : 'Perubahan sudah diterapkan ke data karyawan.' }}
                </p>
            @endif

            @if ($myTurn)
                <section class="pr-panel" aria-labelledby="h-decide">
                    <div class="pr-panel-head">
                        <div>
                            <h2 class="pr-panel-title" id="h-decide">Giliran Anda menyetujui</h2>
                            <span class="pr-panel-sub">Periksa daftar karyawan, perubahan, dan gaji di bawah. Tanda tangan dari profil Anda ikut tercetak di PDF.</span>
                        </div>
                    </div>
                    <div class="sk-decide">
                        <form method="POST" action="{{ route('SkLetters.approve', $sk->id) }}" class="sk-confirm" data-confirm="Setujui SK {{ $sk->sk_number }}?">
                            @csrf
                            <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-check" aria-hidden="true"></i> Setujui</button>
                        </form>
                        <form method="POST" action="{{ route('SkLetters.return', $sk->id) }}">
                            @csrf
                            <label for="return-note" class="emp-help" style="font-weight:700;color:var(--pr-ink)">Atau kembalikan ke HR untuk diperbaiki</label>
                            <textarea id="return-note" name="note" class="emp-input" rows="2" minlength="5" maxlength="1000" placeholder="Tulis yang perlu diperbaiki" required>{{ old('note') }}</textarea>
                            <button type="submit" class="pr-btn" style="margin-top:8px">Kembalikan</button>
                        </form>
                    </div>
                </section>
            @endif

            <div class="sk-layout">
                <div>
                    <section class="pr-panel" aria-labelledby="h-people">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-people">Karyawan ({{ $sk->employees->count() }})</h2>
                                <span class="pr-panel-sub">Yang berubah, dari kondisi saat SK dibuat ke yang baru.</span>
                            </div>
                        </div>
                        @forelse ($sk->employees as $e)
                            @php
                                $p = $e->pivot;
                                $changes = array_filter([
                                    'Jabatan'    => $p->new_position_id ? [$positions[$p->position_id] ?? '-', $positions[$p->new_position_id] ?? '-'] : null,
                                    'Departemen' => $p->new_department_id ? [$departments[$p->department_id] ?? '-', $departments[$p->new_department_id] ?? '-'] : null,
                                    'Lokasi'     => $p->new_store_id ? [$stores[$p->previous_store_id] ?? '-', $stores[$p->new_store_id] ?? '-'] : null,
                                    'Grading'    => $p->new_grading_id ? [$gradings[$p->grading_id] ?? '-', $gradings[$p->new_grading_id] ?? '-'] : null,
                                    'Group'      => $p->new_group_id ? [$groups[$p->group_id] ?? '-', $groups[$p->new_group_id] ?? '-'] : null,
                                    'Jenis'      => $p->new_status_employee ? [$p->previous_status_employee ?? '-', $p->new_status_employee] : null,
                                ]);
                                $pay = array_filter(['Gaji pokok' => $rp($p->basic_salary), 'Tunjangan jabatan' => $rp($p->positional_allowance), 'Daily rate' => $rp($p->daily_rate)]);
                            @endphp
                            <article class="sk-person">
                                <div class="sk-person-head">
                                    <div>
                                        <strong>{{ $e->employee_name }}</strong>
                                        <span class="sk-person-now">{{ $e->employee_pengenal ? 'NIP ' . $e->employee_pengenal : 'Tanpa NIP' }}</span>
                                    </div>
                                </div>
                                @if ($changes || $pay || $p->notes || ($p->new_structure_id && !$p->new_position_id))
                                    <dl class="sk-change">
                                        @foreach ($changes as $label => [$from, $to])
                                            <dt>{{ $label }}</dt><dd><span class="from">{{ $from }}</span> menjadi <strong>{{ $to }}</strong></dd>
                                        @endforeach
                                        @if ($type?->categories === 'Termination')
                                            <dt>Status</dt><dd><strong>Resign</strong> mulai {{ $tgl($sk->effective_date) }}</dd>
                                        @endif
                                        @foreach ($pay as $label => $value)
                                            <dt>{{ $label }}</dt><dd><strong>{{ $value }}</strong></dd>
                                        @endforeach
                                        @if ($p->new_structure_id && !$p->new_position_id)
                                            <dt>Struktur</dt><dd>SK lama: memakai struktur jabatan (modul struktur sudah tidak dipakai)</dd>
                                        @endif
                                        @if ($p->notes)
                                            <dt>Keterangan</dt><dd>{{ $p->notes }}</dd>
                                        @endif
                                    </dl>
                                @else
                                    <p class="emp-help">Tidak ada perubahan yang diisi untuk karyawan ini.</p>
                                @endif
                                @if ($sk->isFinal() && $type?->generates_contract)
                                    @php $ct = $sk->contracts->firstWhere('employee_id', $e->id); @endphp
                                    @if ($ct)
                                        <a href="{{ route('showcontract', $ct->id) }}" class="pr-btn pr-btn-sm" style="margin:8px 8px 0 0">Lihat kontrak</a>
                                    @elseif (auth()->user()->can('ManageContracts'))
                                        <a href="{{ route('createcontract', ['sk' => $sk->id, 'employee' => $e->id]) }}" class="pr-btn pr-btn-sm pr-btn-primary" style="margin:8px 8px 0 0">Buat kontrak dari SK ini</a>
                                    @endif
                                @endif
                                @if ($sk->isFinal())
                                    @if ($p->applied_at)
                                        <span class="sk-applied">Sudah diterapkan ke data karyawan, {{ $jam($p->applied_at) }}</span>
                                    @else
                                        <span class="sk-applied is-later">Diterapkan otomatis pada {{ $tgl($sk->effective_date) }}</span>
                                    @endif
                                @endif
                            </article>
                        @empty
                            <p class="sk-empty">Belum ada karyawan di SK ini.@if ($canEdit) Tambahkan lewat Ubah.@endif</p>
                        @endforelse
                    </section>

                    <section class="pr-panel" aria-labelledby="h-content">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-content">Isi SK</h2></div></div>
                        <div class="sk-text">
                            @foreach ([['Menimbang', $sk->menimbang, 'content_menimbang'], ['Mengingat', $sk->mengingat, 'content_mengingat']] as [$label, $items, $col])
                                @if ($items->isNotEmpty())
                                    <h3>{{ $label }}</h3>
                                    <ol>@foreach ($items as $it)<li>{{ $it->{$col} }}</li>@endforeach</ol>
                                @endif
                            @endforeach
                            @if ($menetapkan !== '')
                                <h3>Menetapkan</h3>
                                <p style="margin:0">{!! nl2br(e($menetapkan)) !!}</p>
                            @endif
                            @if ($sk->keputusan->isNotEmpty())
                                <h3>Memutuskan</h3>
                                <ol>@foreach ($sk->keputusan as $it)<li>{{ $it->content_keputusan }}</li>@endforeach</ol>
                            @endif
                            @if ($sk->menimbang->isEmpty() && $sk->mengingat->isEmpty() && $menetapkan === '' && $sk->keputusan->isEmpty())
                                <p class="emp-help" style="margin:0">Isi SK belum ditulis.</p>
                            @endif
                        </div>
                    </section>
                </div>

                <aside>
                    <section class="pr-panel" aria-labelledby="h-steps">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-steps">Persetujuan</h2><span class="pr-panel-sub">Berurutan, dari atas ke bawah.</span></div></div>
                        <ol class="sk-steps">
                            @foreach ($steps as $n => $s)
                                @php
                                    // Saat Draft belum ada yang menyetujui (data lama bisa menyimpan waktu approver 1 di Draft)
                                    $done = $s['at'] && $sk->status !== 'Draft';
                                    $state = $done ? 'is-done' : ($nowStep === $n && $sk->status !== 'Cancelled' ? 'is-now' : '');
                                @endphp
                                <li class="sk-step {{ $state }}">
                                    <span class="sk-step-num" aria-hidden="true">{{ $n }}</span>
                                    <div>
                                        <strong>{{ $s['who']?->employee_name ?? 'Belum dipilih' }}</strong>
                                        <span>{{ $s['role'] }}.
                                            @if ($done) {{ $n === 1 ? 'Diajukan' : 'Disetujui' }} {{ $jam($s['at']) }}
                                            @elseif ($state === 'is-now') {{ $n === 1 ? 'Menunggu diajukan' : 'Sedang menunggu persetujuan' }}
                                            @else Belum @endif
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    @if ($sk->notes && $canManage)
                        <section class="pr-panel" aria-labelledby="h-notes">
                            <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-notes">Catatan internal</h2></div></div>
                            <p class="sk-text" style="margin:0">{{ $sk->notes }}</p>
                        </section>
                    @endif

                    <section class="pr-panel" aria-labelledby="h-log">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-log">Riwayat</h2></div></div>
                        @if ($activities->isEmpty())
                            <p class="sk-empty">Belum ada riwayat.</p>
                        @else
                            <ol class="sk-log">
                                @foreach ($activities as $a)<li>{{ $a->description }}<time datetime="{{ $a->created_at?->toIso8601String() }}">{{ $jam($a->created_at) }}</time></li>@endforeach
                            </ol>
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
        // Konfirmasi untuk aksi yang tidak bisa diurungkan (ajukan, setujui, batalkan)
        document.querySelectorAll('form.sk-confirm').forEach(form => form.addEventListener('submit', function(e) {
            if (form.dataset.ok) return;
            e.preventDefault();
            Swal.fire({ icon: 'question', text: form.dataset.confirm, showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5' })
                .then(r => { if (r.isConfirmed) { form.dataset.ok = '1'; form.querySelector('button[type=submit]').disabled = true; form.submit(); } });
        }));
    </script>
@endpush
