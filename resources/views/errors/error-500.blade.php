@extends('layouts.error')

@section('title', '500')

@push('style')
    <!-- CSS Libraries -->
@endpush

@section('main')
    <div class="page-error">
        <div class="page-inner">
            <h1>500</h1>
            <div class="page-description">
                Whoopps, something went wrong.
            </div>
            <div class="page-search">
                <form>
                    <div class="form-group floating-addon floating-addon-not-append">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    {{-- <i class="fas fa-search"></i> --}}
                                </div>
                            </div>
                            {{-- <input type="text"
                                class="form-control"
                                placeholder="Search"> --}}
                            <div class="input-group-append">
                                {{-- <button class="btn btn-primary btn-lg">
                                    Search
                                </button> --}}
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
@endsection

@push('scripts')
    <!-- JS Libraies -->

    <!-- Page Specific JS File -->
@endpush
