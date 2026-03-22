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
            object-fit: cover;
            -webkit-user-drag: none;
        }

        .brand-name {
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
        <div class="code">500 — Server Error</div>
        <h1>Server <em>internal</em><br>error.</h1>
        <p>Please contact edwin sirait ASAP.</p>
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