{{-- Pratinjau kop surat, meniru SkLetters/pdf & documents/types. Elemen ber-id dipakai script form untuk pembaruan langsung. --}}
<div class="co-kop" @isset($kopId) id="{{ $kopId }}" @endisset>
    <div class="co-kop-logo">
        <img src="{{ $logo ?? '' }}" alt="" data-k="logo" @if (empty($logo)) hidden @endif>
        <span class="co-kop-nologo" data-k="nologo" @if (!empty($logo)) hidden @endif>Logo</span>
    </div>
    <div class="co-kop-text">
        <div class="co-kop-name" data-k="name">{{ $name ?: 'NAMA PERUSAHAAN' }}</div>
        <div class="co-kop-sub" data-k="header" @if (empty($header)) hidden @endif>{{ $header }}</div>
        <div class="co-kop-addr" data-k="address">{{ $address ?: 'Alamat perusahaan' }}</div>
        <div class="co-kop-contact" data-k="contact" @if (empty($email) && empty($website)) hidden @endif>{{ collect([$email, $website])->filter()->implode(' | ') }}</div>
    </div>
    <div class="co-kop-spacer" aria-hidden="true"></div>
</div>
