{{-- @extends('layouts.error')

@section('title', '403')

<style>
    body {
        background: url('{{ asset('img/unsplash/bg.jpg') }}') no-repeat center center fixed;
        background-size: cover;
    }
    .container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

</style>

@section('main')
<div class="container">
    <div class="page-error">
        <div class="page-inner">
            <h1>403</h1>
            <div class="page-description">
                You do not have access to this page.
            </div>
            <div class="page-search">
                <form>
                    <div class="form-group floating-addon floating-addon-not-append">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                               
                                </div>
                            </div>
                            
                            <div class="input-group-append">
                            
                            </div>
                        </div>
                    </div>
                </form>
                <div class="mt-3">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">Logout</button>
                        </form>
                    @else
                        <a href="/">Back to login</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

@endpush --}}
@extends('layouts.error')

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
    <p class="page-description">You do not have access to this page.</p>
    
    <div class="mt-3">
        @auth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-custom">Logout</button>
            </form>
        @else
            <a href="/" class="btn-custom">Back to Login</a>
        @endauth
    </div>
</div>
@endsection

@push('scripts')
@endpush
