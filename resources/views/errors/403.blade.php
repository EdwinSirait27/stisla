{{-- @extends('layouts.error')

@section('title', '403 Forbidden')

<style>
    body {
        background: url('{{ asset('img/unsplash/bg.jpg') }}') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        font-family: 'Arial', sans-serif;
    }

    .container {
        max-width: 600px;
        text-align: center;
        background: rgba(255, 255, 255, 0.8);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        font-size: 80px;
        font-weight: bold;
        color: #e74c3c;
        margin-bottom: 10px;
        animation: fadeIn 1s ease-in-out;
    }

    .page-description {
        font-size: 18px;
        color: #555;
        margin-bottom: 20px;
    }

    .btn-custom {
        background-color: #e74c3c;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        transition: 0.3s;
    }

    .btn-custom:hover {
        background-color: #c0392b;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@section('main')
<div class="container">
    <h1>403</h1>
    <p class="page-description">why do you visit this site.</p>
    
    <div class="mt-3">
        @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-custom">Logout</button>
        </form>
        <button onclick="window.history.back()" class="btn-custom">go back</button>
        @else
          <a href="{{ url()->previous() }}" class="btn-custom">Back</a>

        @endauth
    </div>
</div>
@endsection

@push('scripts')
@endpush --}}
{{-- @extends('layouts.error')

@section('title', '404 Not Found')

<style>
    body {
        background: url('{{ asset('img/unsplash/bg.jpg') }}') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
        font-family: 'Arial', sans-serif;
    }

    .container {
        max-width: 600px;
        text-align: center;
        background: rgba(255, 255, 255, 0.8);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
        font-size: 80px;
        font-weight: bold;
        color: #e74c3c;
        margin-bottom: 10px;
        animation: fadeIn 1s ease-in-out;
    }

    .page-description {
        font-size: 18px;
        color: #555;
        margin-bottom: 20px;
    }

    .btn-custom {
        background-color: #e74c3c;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        text-decoration: none;
        font-size: 16px;
        transition: 0.3s;
    }

    .btn-custom:hover {
        background-color: #c0392b;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@section('main')
<div class="container">
    <h1>404</h1>
    <p class="page-description">Not Found.</p>
    <div class="mt-3">
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-custom">Logout</button>
            </form>
        @else
           <a href="{{ url()->previous() }}" class="btn-custom">Back</a>

        @endauth
    </div>
</div>
@endsection

@push('scripts')
@endpush --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HRX — Asian Bay Development</title>
    <link rel="icon" type="image/png" href="{{ asset('img/AsianBay logomark.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=Mulish:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Mulish', sans-serif;
            background: #0c0d10;
            color: #e8e4dc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 36px 52px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        header img {
            width: 88px;
            height: 88px;
            /* border-radius: 7px; */
            object-fit: cover;
            /* border: 1px solid rgba(255,255,255,0.1); */
            -webkit-user-drag: none;
        }

        .brand-name {
            /* font-family: 'Playfair Display', serif; */
            font-size: 1.2rem;
            letter-spacing: 0.06em;
            color: #e8e4dc;
        }

        .brand-sub {
            font-size: 0.58rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #d4a843;
            display: block;
            margin-top: 1px;
        }

        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 52px;
            max-width: 560px;
        }

        .code {
            font-size: 0.62rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #d4a843;
            margin-bottom: 20px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.2rem;
            font-weight: 700;
            line-height: 1.1;
            color: #e8e4dc;
            margin-bottom: 16px;
        }

        h1 em {
            font-weight: 400;
            font-style: italic;
            color: rgba(232,228,220,0.4);
        }

        p {
            font-size: 0.85rem;
            color: #666;
            font-weight: 300;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .btn {
            padding: 12px 28px;
            border: 1px solid #d4a843;
            color: #d4a843;
            font-family: 'Mulish', sans-serif;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            text-decoration: none;
            background: transparent;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: color 0.3s;
            display: inline-block;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: #d4a843;
            transform: translateX(-101%);
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        .btn:hover { color: #111; }
        .btn:hover::before { transform: translateX(0); }
        .btn span { position: relative; z-index: 1; }

        .back {
            font-size: 0.75rem;
            color: #444;
            text-decoration: none;
            transition: color 0.2s;
        }

        .back:hover { color: #888; }

        footer {
            padding: 28px 52px;
            border-top: 1px solid rgba(255,255,255,0.06);
            font-size: 0.62rem;
            color: #333;
            letter-spacing: 0.08em;
        }

        @media (max-width: 600px) {
            header, main, footer { padding-left: 28px; padding-right: 28px; }
            h1 { font-size: 2.4rem; }
        }
    </style>
</head>
<body>
    <header>
        <img src="{{ asset('img/AsianBay.png') }}" alt="HRX">
        <div>
            <span class="brand-name">HRX</span>
            <span class="brand-sub">Human Resource System</span>
        </div>
    </header>

    <main>
        <div class="code">403 — Forbidden</div>
        <h1>Forbidden <em>not</em><br>allowed.</h1>
        <p>why do you see this page?.</p>
        <div class="actions">
             @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
            {{-- <a href="{{ url('/') }}" class="btn"><span>Go to Dashboard</span></a> --}}
             <button type="submit" class="btn">Logout</button>
            </form>
        @else
            <a href="javascript:history.back()" class="back">← Go back</a>
            @endauth
        </div>
    </main>

    <footer>© {{ date('Y') }} HRX · Developed by Edwin Sirait</footer>
</body>
</html>
 {{-- @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-custom">Logout</button>
            </form>
        @else
           <a href="{{ url()->previous() }}" class="btn-custom">Back</a>

        @endauth --}}