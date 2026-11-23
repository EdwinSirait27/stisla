@php
    /** @var \App\Models\User|null $shellUser */
    $shellUser     = auth()->user();
    $shellEmployee = $shellUser?->employee;
    $shellName     = $shellEmployee?->employee_name ?: ($shellUser?->username ?? 'Pengguna');
    // Role aktif (kalau dipilih) atau role pertama akun ini
    $shellRole     = $shellUser?->active_role_hrx ?: $shellUser?->getRoleNames()->first();
    $shellStore    = $shellEmployee?->store()->wherePivot('is_primary', true)->value('stores_tables.name');
    $shellPhoto    = $shellEmployee?->photos
        ? route('useremployee.photo', basename($shellEmployee->photos))
        : asset('img/avatar/avatar-1.png');
    $shellNow      = now()->locale('id');
@endphp
<style>
    img.no-drag {
        -webkit-user-drag: none;
        user-select: none;
    }
</style>
<div class="navbar-bg bg-dark"></div>
<nav class="navbar navbar-expand-lg main-navbar" aria-label="Bar atas">
    <form class="form-inline mr-auto" onsubmit="return false">
        <ul class="navbar-nav mr-3 align-items-center">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg shell-toggle" role="button" aria-label="Buka atau tutup menu samping"><i class="fas fa-bars" aria-hidden="true"></i></a></li>
            <li class="shell-today" aria-label="Tanggal dan jam saat ini">
                <strong>{{ $shellNow->translatedFormat('l, d F Y') }}</strong>
                <span><time id="shell-clock" datetime="{{ $shellNow->toIso8601String() }}">{{ $shellNow->format('H.i') }}</time> WITA</span>
            </li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user shell-user"
                aria-haspopup="true" aria-expanded="false" aria-label="Menu akun {{ $shellName }}">
                <img alt="" src="{{ $shellPhoto }}" class="rounded-circle no-drag">
                <span class="shell-user-text">
                    <span class="shell-user-name">{{ $shellName }}</span>
                    @if ($shellRole || $shellStore)
                        <span class="shell-user-meta">{{ collect([$shellRole, $shellStore])->filter()->implode(', ') }}</span>
                    @endif
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shell-menu">
                <div class="shell-menu-head">
                    <strong>{{ $shellName }}</strong>
                    <dl>
                        @if ($shellUser?->username)<dt>Username</dt><dd>{{ $shellUser->username }}</dd>@endif
                        @if ($shellRole)<dt>Role</dt><dd>{{ $shellRole }}</dd>@endif
                        @if ($shellStore)<dt>Lokasi utama</dt><dd>{{ $shellStore }}</dd>@endif
                    </dl>
                </div>
                @if ($shellEmployee)
                    <a href="{{ route('pages.feature-profile') }}" class="dropdown-item"><i class="far fa-user" aria-hidden="true"></i> Profil</a>
                @endif
                <a href="{{ route('pages.change-password') }}" class="dropdown-item"><i class="fas fa-key" aria-hidden="true"></i> Ganti password</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item"><i class="fas fa-right-from-bracket" aria-hidden="true"></i> Keluar</button>
                </form>
            </div>
        </li>
    </ul>
</nav>
