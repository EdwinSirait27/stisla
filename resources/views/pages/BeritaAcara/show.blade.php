@extends('layouts.app')
@section('title', $doc->content?->subject ?? 'Berita acara')
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    @include('pages.Documenttype._styles')
    @include('pages.BeritaAcara._list_styles')
@endpush

@section('main')
    @php
        $c = $doc->content;
        $dt = fn($d) => $d ? $d->copy()->timezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i') : '-';
        $type = $doc->companydocumentconfigs?->documenttypes;
    @endphp
    <div class="main-content pr">
        <section class="section">
            <header class="pr-head">
                <div>
                    <ol class="pr-crumbs"><li>Dokumen</li><li><a href="{{ route('beritaacara.index') }}">Berita acara</a></li><li aria-current="page">Detail</li></ol>
                    <h1 class="pr-title">{{ $c?->subject }}</h1>
                    <p class="pr-lede">
                        <span class="ba-st is-{{ $doc->status }}">{{ $doc->status_label }}</span>
                        @if (in_array($doc->status, \App\Models\Documents::BA_IN_APPROVAL, true) && $step)
                            {{ $steps->where('status', 'approved')->count() }} dari {{ $steps->count() }} menyetujui, menunggu {{ $step->employee?->employee_name }}
                            {{ $waitingSince ? 'sejak ' . $waitingSince->locale('id')->diffForHumans() : '' }}.
                        @endif
                        {{ $type?->document_name }}, nomor {{ $doc->document_number }}{{ ($c?->round ?? 1) > 1 ? ', pengajuan ke-' . $c->round : '' }}
                    </p>
                </div>
                <div class="pr-actions">
                    @if ($doc->status === 'approved')
                        <a href="{{ route('beritaacara.pdf', $doc->id) }}" class="pr-btn pr-btn-primary" target="_blank" rel="noopener"><i class="fas fa-file-pdf" aria-hidden="true"></i> Unduh PDF</a>
                    @endif
                    @if ($isOwner && $doc->status === 'revision')
                        <a href="{{ route('beritaacara.edit', $doc->id) }}" class="pr-btn pr-btn-primary"><i class="fas fa-pen" aria-hidden="true"></i> Revisi dan ajukan ulang</a>
                    @endif
                    @if ($isOwner && in_array($doc->status, ['pending', 'in_review', 'revision'], true))
                        <form method="POST" action="{{ route('beritaacara.cancel', $doc->id) }}" id="cancel-form">@csrf
                            <button type="submit" class="pr-btn ba-danger">Batalkan</button>
                        </form>
                    @endif
                </div>
            </header>

            <div class="dt-layout">
                <div>
                    @if ($myTurn)
                        <section class="pr-panel" aria-labelledby="h-act">
                            <div class="pr-panel-head">
                                <div>
                                    <h2 class="pr-panel-title" id="h-act">Giliran Anda menyetujui</h2>
                                    <span class="pr-panel-sub">Anda approver ke-{{ $step->sequence }} dari {{ $steps->count() }}. Baca isinya di bawah sebelum menyetujui.</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('beritaacara.approve', $doc->id) }}" id="approve-form" class="ba-action" style="border-top:0">
                                @csrf
                                <h3>Setujui dan tandatangani</h3>
                                @if ($mySignature)
                                    <div>
                                        <span class="emp-help" style="margin:0 0 4px">Tanda tangan dari profil Anda akan dipakai:</span>
                                        <img src="{{ $mySignature }}" alt="Tanda tangan Anda" class="ba-sign">
                                    </div>
                                @else
                                    <div>
                                        <label for="ba-pad" class="emp-help" style="margin:0 0 4px;font-weight:700;color:var(--pr-ink)">Gambar tanda tangan Anda</label>
                                        <canvas id="ba-pad" class="ba-pad" tabindex="0" aria-label="Area tanda tangan. Gambar dengan jari, stylus, atau mouse."></canvas>
                                        <div class="ba-pad-bar">
                                            <span>Tanda tangan ini juga disimpan ke profil Anda untuk persetujuan berikutnya.</span>
                                            <button type="button" class="pr-btn pr-btn-sm" id="pad-clear">Hapus</button>
                                        </div>
                                        <input type="hidden" name="signature" id="signature">
                                    </div>
                                @endif
                                @error('signature')<span class="emp-error">{{ $message }}</span>@enderror
                                @error('status')<span class="emp-error">{{ $message }}</span>@enderror
                                <label for="approve-note" class="sr-only">Catatan persetujuan</label>
                                <input type="text" id="approve-note" name="note" maxlength="1000" class="emp-input" placeholder="Catatan (opsional)">
                                <div><button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-signature" aria-hidden="true"></i> Setujui dan tandatangani</button></div>
                            </form>
                            <form method="POST" action="{{ route('beritaacara.return', $doc->id) }}" id="return-form" class="ba-action">
                                @csrf
                                <h3>Atau kembalikan ke pengaju</h3>
                                <label for="return-note" class="sr-only">Alasan dikembalikan</label>
                                <textarea id="return-note" name="note" rows="3" maxlength="1000" class="ba-textarea @error('note') is-invalid @enderror" style="min-height:80px"
                                    placeholder="Tulis apa yang perlu diperbaiki. Persetujuan akan dimulai lagi dari approver pertama setelah direvisi.">{{ old('note') }}</textarea>
                                @error('note')<span class="emp-error">{{ $message }}</span>@enderror
                                <div><button type="submit" class="pr-btn ba-danger"><i class="fas fa-rotate-left" aria-hidden="true"></i> Kembalikan untuk revisi</button></div>
                            </form>
                        </section>
                    @endif

                    <section class="pr-panel" aria-labelledby="h-isi">
                        <div class="pr-panel-head"><div><h2 class="pr-panel-title" id="h-isi">Isi berita acara</h2></div></div>
                        <div class="ba-content">
                            <dl class="ba-facts">
                                <div><dt>Diajukan oleh</dt><dd>{{ $doc->employee?->employee_name }}@if ($titles[$doc->employee_id] ?? null), {{ $titles[$doc->employee_id] }}@endif</dd></div>
                                <div><dt>Diajukan</dt><dd>{{ $dt($c?->submitted_at) }}</dd></div>
                                <div><dt>Waktu kejadian</dt><dd>{{ $c?->event_at ? $dt($c->event_at) : '-' }}</dd></div>
                                <div><dt>Tempat</dt><dd>{{ $c?->location ?: '-' }}</dd></div>
                            </dl>
                            <p class="ba-body">{{ $c?->body }}</p>
                        </div>
                        @if ($doc->attachments->isNotEmpty())
                            <div class="pr-panel-head" style="border-top:1px solid var(--pr-rule)"><div><h2 class="pr-panel-title">Lampiran</h2></div></div>
                            <ul class="ba-people">
                                @foreach ($doc->attachments as $a)
                                    <li><a href="{{ route('beritaacara.attachment', [$doc->id, $a->id]) }}" target="_blank" rel="noopener"><i class="fas fa-paperclip" aria-hidden="true"></i> {{ $a->original_name }}</a><small>{{ number_format($a->size / 1024, 0, ',', '.') }} KB</small></li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                </div>

                <aside>
                    <section class="pr-panel" aria-labelledby="h-steps">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-steps">Persetujuan</h2>
                                <span class="pr-panel-sub">{{ $steps->where('status', 'approved')->count() }} dari {{ $steps->count() }} sudah menyetujui</span>
                            </div>
                        </div>
                        <ol class="ba-steps">
                            @foreach ($steps as $s)
                                <li class="ba-step is-{{ $s->status }}">
                                    <span class="ba-num">{{ $s->sequence }}</span>
                                    <div>
                                        <div class="ba-step-head">
                                            <span class="ba-step-name">{{ $s->employee?->employee_name }}</span>
                                            <span class="ba-st is-{{ $s->status }}">{{ $s->status_label }}</span>
                                        </div>
                                        <span class="ba-step-sub">{{ $titles[$s->employee_id] ?? '' }}@if ($s->acted_at){{ ($titles[$s->employee_id] ?? null) ? ', ' : '' }}{{ $dt($s->acted_at) }}@endif</span>
                                        @if ($s->status === 'approved' && $s->signature_path)
                                            <img src="{{ route('beritaacara.signature', [$doc->id, $s->id]) }}" alt="Tanda tangan {{ $s->employee?->employee_name }}" class="ba-sign" loading="lazy">
                                        @endif
                                        @if ($s->note)<p class="ba-step-note">{{ $s->note }}</p>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                        @if ($history->isNotEmpty())
                            <details style="padding:0 var(--pr-space-lg) var(--pr-space-md)">
                                <summary style="cursor:pointer;font-size:var(--pr-text-sm);font-weight:600">Riwayat pengajuan sebelumnya</summary>
                                @foreach ($history as $r => $rows)
                                    <p class="emp-help" style="margin-top:var(--pr-space-sm)">Pengajuan ke-{{ $r }}</p>
                                    <ul class="ba-people" style="padding:0">
                                        @foreach ($rows as $s)
                                            <li style="padding:2px 0"><span>{{ $s->sequence }}. {{ $s->employee?->employee_name }}@if ($s->note): {{ $s->note }}@endif</span><small>{{ $s->status_label }}</small></li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </details>
                        @endif
                    </section>

                    @if ($canRemind && $reminder)
                        @php
                            $opts = [
                                'current'  => ['Approver yang sedang mendapat giliran', 'Email "perlu persetujuan Anda" dikirim ulang.'],
                                'superior' => ['Atasan pengaju', 'Pengingat bahwa berita acara bawahannya masih menunggu persetujuan.'],
                                'next'     => ['Approver berikutnya', 'Pengingat bahwa sebentar lagi gilirannya.'],
                            ];
                            $firstOk = collect(['current', 'superior', 'next'])->first(fn($k) => $reminder[$k][1] === null) ?? 'email';
                            // Sudah tidak menunggu persetujuan: hanya kirim tautan ke alamat email
                            $inApproval = in_array($doc->status, \App\Models\Documents::BA_IN_APPROVAL, true);
                        @endphp
                        <section class="pr-panel" aria-labelledby="h-remind">
                            <div class="pr-panel-head">
                                <div>
                                    <h2 class="pr-panel-title" id="h-remind">{{ $inApproval ? 'Kirim ulang email' : 'Kirim tautan lewat email' }}</h2>
                                    <span class="pr-panel-sub">{{ $inApproval ? 'Untuk mengingatkan yang ditunggu.' : 'Berita acara ini sudah tidak menunggu persetujuan.' }} Email masuk antrian dan tercatat di riwayat.</span>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('beritaacara.remind', $doc->id) }}" class="rm-form" id="remind-form">
                                @csrf
                                @foreach ($inApproval ? $opts : [] as $key => [$title, $desc])
                                    @php [$who, $problem] = $reminder[$key]; @endphp
                                    <label class="rm-option">
                                        <input type="radio" name="target" value="{{ $key }}" @checked(old('target', $firstOk) === $key) @disabled($problem !== null)>
                                        <span>
                                            <strong>{{ $title }}{{ $who ? ': ' . $who->employee_name : '' }}</strong>
                                            <small>{{ $problem ? 'Tidak bisa: ' . $problem . '.' : $desc . ' Ke ' . $who->company_email }}</small>
                                        </span>
                                    </label>
                                @endforeach
                                <label class="rm-option">
                                    <input type="radio" name="target" value="email" @checked(old('target', $firstOk) === 'email')>
                                    <span>
                                        <strong>Alamat email lain</strong>
                                        <small>Penerima hanya bisa membuka isinya kalau punya akses ke berita acara ini di HRX.</small>
                                    </span>
                                </label>
                                <label for="remind-email" class="rm-label">Alamat email</label>
                                <input type="email" id="remind-email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.co.id" autocomplete="off">
                                <label for="remind-note" class="rm-label">Pesan (opsional)</label>
                                <textarea id="remind-note" name="note" rows="2" maxlength="500" placeholder="Mis. Mohon disetujui sebelum Jumat">{{ old('note') }}</textarea>
                                @error('target')<span class="emp-error">{{ $message }}</span>@enderror
                                @error('email')<span class="emp-error">{{ $message }}</span>@enderror
                                <button type="submit" class="pr-btn pr-btn-primary"><i class="fas fa-paper-plane" aria-hidden="true"></i> Kirim email</button>
                            </form>
                        </section>
                    @endif

                    <section class="pr-panel" aria-labelledby="h-activity">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-activity">Riwayat aktivitas</h2>
                                <span class="pr-panel-sub">Tercatat setiap kali diajukan, disetujui, dikembalikan, atau dibatalkan.</span>
                            </div>
                        </div>
                        @if ($activities->isEmpty())
                            <p class="emp-help" style="padding:0 var(--pr-space-lg) var(--pr-space-md)">Belum ada aktivitas tercatat.</p>
                        @else
                            <ol class="ba-log">
                                @foreach ($activities as $a)
                                    <li class="is-{{ $a->event }}">
                                        <span class="ba-log-text">{{ $a->description }}</span>
                                        <time datetime="{{ $a->created_at?->toIso8601String() }}">{{ $dt($a->created_at) }}</time>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </section>

                    <section class="pr-panel" aria-labelledby="h-cc">
                        <div class="pr-panel-head">
                            <div>
                                <h2 class="pr-panel-title" id="h-cc">Tembusan</h2>
                                <span class="pr-panel-sub">{{ $doc->recipients->count() ? 'Diberi tahu lewat email saat berita acara sah.' : 'Tidak ada tembusan.' }}</span>
                            </div>
                        </div>
                        @if ($doc->recipients->isNotEmpty())
                            <ul class="ba-people">
                                @foreach ($doc->recipients as $r)
                                    <li><span>{{ $r->employee?->employee_name }}</span><small>{{ $r->read_at ? 'Dibaca ' . $dt($r->read_at) : 'Belum dibaca' }}</small></li>
                                @endforeach
                            </ul>
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
        (function() {
            @if (session('success'))
                Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')) });
            @endif

            // ── Kanvas tanda tangan (hanya kalau profil belum punya) ──
            const pad = document.getElementById('ba-pad');
            let drawn = false;
            if (pad) {
                const ctx = pad.getContext('2d');
                const fit = () => {
                    const r = pad.getBoundingClientRect(), dpr = window.devicePixelRatio || 1;
                    pad.width = r.width * dpr; pad.height = r.height * dpr;
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                    ctx.lineWidth = 2.2; ctx.lineCap = 'round'; ctx.lineJoin = 'round';
                    ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--pr-ink').trim() || 'black';
                    drawn = false;
                };
                fit();
                let last = null;
                const pos = e => { const r = pad.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; };
                pad.addEventListener('pointerdown', e => { pad.setPointerCapture(e.pointerId); last = pos(e); });
                pad.addEventListener('pointermove', e => {
                    if (!last) return;
                    const p = pos(e);
                    ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(p.x, p.y); ctx.stroke();
                    last = p; drawn = true;
                });
                ['pointerup', 'pointercancel', 'pointerleave'].forEach(ev => pad.addEventListener(ev, () => { last = null; }));
                document.getElementById('pad-clear').addEventListener('click', () => { ctx.clearRect(0, 0, pad.width, pad.height); drawn = false; });
            }

            const approve = document.getElementById('approve-form');
            if (approve) approve.addEventListener('submit', function(e) {
                e.preventDefault();
                if (pad && !drawn) return Swal.fire({ icon: 'error', title: 'Gambar tanda tangan dulu', text: 'Tanda tangan dibutuhkan untuk menyetujui.' });
                if (pad) document.getElementById('signature').value = pad.toDataURL('image/png');
                Swal.fire({
                    icon: 'question', title: 'Setujui berita acara ini?',
                    text: 'Tanda tangan Anda akan tercantum di dokumen.',
                    showCancelButton: true, confirmButtonText: 'Setujui', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5',
                }).then(r => { if (r.isConfirmed) { approve.querySelector('button[type=submit]').disabled = true; approve.submit(); } });
            });

            const ret = document.getElementById('return-form');
            if (ret) ret.addEventListener('submit', function(e) {
                e.preventDefault();
                const note = document.getElementById('return-note');
                if (note.value.trim().length < 5) { note.focus(); return Swal.fire({ icon: 'error', title: 'Tulis alasan pengembalian', text: 'Minimal 5 karakter, supaya pengaju tahu apa yang harus diperbaiki.' }); }
                Swal.fire({
                    icon: 'warning', title: 'Kembalikan ke pengaju?',
                    text: 'Persetujuan yang sudah ada akan dibatalkan dan dimulai lagi dari approver pertama setelah direvisi.',
                    showCancelButton: true, confirmButtonText: 'Kembalikan', cancelButtonText: 'Batal', confirmButtonColor: '#b45309',
                }).then(r => { if (r.isConfirmed) ret.submit(); });
            });

            const cancel = document.getElementById('cancel-form');
            if (cancel) cancel.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning', title: 'Batalkan berita acara?', text: 'Berita acara tidak bisa dilanjutkan setelah dibatalkan. Nomornya tetap tercatat.',
                    showCancelButton: true, confirmButtonText: 'Batalkan', cancelButtonText: 'Kembali', confirmButtonColor: '#b91c1c',
                }).then(r => { if (r.isConfirmed) cancel.submit(); });
            });
        })();
    </script>
@endpush
