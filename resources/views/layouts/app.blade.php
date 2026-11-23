<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | HRX Asian Bay Development</title>
    <meta name="description" content="HRX, sistem HR Asian Bay Development: data karyawan, absensi, roster, cuti, lembur, dan payroll.">
    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('library/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="icon" type="image/png" href="{{ asset('img/AsianBay logomark.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    @stack('styles')
    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-nav.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}">
    {{-- Google Analytics bawaan template Stisla (UA-94034622-3, milik pembuat template) dihapus:
         data pemakaian aplikasi HR tidak boleh terkirim ke akun pihak lain.
         app.js juga tidak lagi dimuat dua kali (Alpine.js sempat terinisialisasi ganda). --}}
</head>

<body>
    <a href="#main-content" class="shell-skip">Lewati ke konten</a>
    <div id="app">
        <div class="main-wrapper">
            <!-- Header -->
            @include('components.header')

            <!-- Sidebar -->
            @include('components.sidebar')

            <!-- Content -->
            <div id="main-content" tabindex="-1">
                @yield('main')
            </div>
            @include('components.mobile-bottom-nav')

            <!-- Footer -->
            @include('components.footer')

        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('library/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('library/tooltip.js/dist/umd/tooltip.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('library/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>

    <!-- Peringatan keluar otomatis -->
    <div class="shell-idle" id="shell-idle" role="alertdialog" aria-modal="true" aria-labelledby="shell-idle-title" aria-describedby="shell-idle-text" hidden>
        <div class="shell-idle-box">
            <h2 id="shell-idle-title">Anda akan keluar otomatis</h2>
            <p id="shell-idle-text">Tidak ada aktivitas hampir 60 menit. Anda akan keluar dalam <strong id="shell-idle-left">2:00</strong>. Data yang belum disimpan akan hilang.</p>
            <div class="shell-idle-actions">
                <button type="button" id="shell-idle-logout">Keluar sekarang</button>
                <button type="button" class="is-primary" id="shell-idle-stay">Tetap masuk</button>
            </div>
        </div>
    </div>

    <!-- Main scripts that should always run -->
    <script>
        (function() {
            if (window.inactivityTimerInitialized) return;
            window.inactivityTimerInitialized = true;

            const IDLE_MS = 60 * 60 * 1000;   // keluar otomatis setelah 60 menit tidak aktif
            const WARN_MS = 2 * 60 * 1000;    // peringatan 2 menit sebelumnya
            const KEY = 'hrx:last-activity'; // dibagi antar tab: aktif di satu tab menjaga tab lain tetap masuk
            const box = document.getElementById('shell-idle');
            const left = document.getElementById('shell-idle-left');
            let lastWrite = 0;

            const last = () => Math.max(Number(localStorage.getItem(KEY)) || 0, window.__hrxLocalActivity || 0);
            function markActive() {
                const now = Date.now();
                window.__hrxLocalActivity = now;
                if (now - lastWrite > 5000) { // cukup sekali per 5 detik
                    lastWrite = now;
                    try { localStorage.setItem(KEY, String(now)); } catch (e) {}
                }
                if (!box.hidden) hideWarning();
            }
            function hideWarning() { box.hidden = true; }

            function logout() {
                if (window.loggingOut) return;
                window.loggingOut = true;
                fetch('{{ route('logout') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(() => { window.location.href = '/'; })
                  .catch(() => { window.loggingOut = false; });
            }

            ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'wheel'].forEach(ev =>
                window.addEventListener(ev, () => { if (box.hidden) markActive(); }, { passive: true, capture: true }));
            document.getElementById('shell-idle-stay').addEventListener('click', markActive);
            document.getElementById('shell-idle-logout').addEventListener('click', logout);
            markActive();

            setInterval(() => {
                const idle = Date.now() - last();
                if (idle >= IDLE_MS) return logout();
                if (idle >= IDLE_MS - WARN_MS) {
                    const sec = Math.ceil((IDLE_MS - idle) / 1000);
                    left.textContent = Math.floor(sec / 60) + ':' + String(sec % 60).padStart(2, '0');
                    if (box.hidden) { box.hidden = false; document.getElementById('shell-idle-stay').focus(); }
                } else if (!box.hidden) {
                    hideWarning(); // tab lain aktif
                }
            }, 1000);

            // Jam WITA di bar atas, mengikuti zona waktu aplikasi walau HP di zona lain
            const clock = document.getElementById('shell-clock');
            if (clock) {
                const fmt = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: @json(config('app.timezone')) });
                const tick = () => { clock.textContent = fmt.format(new Date()); };
                tick(); setInterval(tick, 15000);
            }
        })();
    </script>

    @stack('scripts')


</body>

</html>
