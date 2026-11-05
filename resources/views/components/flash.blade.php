{{-- Flash message: style inline supaya tampil di halaman mana pun (termasuk Home) --}}
@php
    $flashBase = 'margin:0 0 16px;padding:11px 16px;border-radius:8px;font-size:13px;text-align:left;';
@endphp
@if (session('success'))
    <div style="{{ $flashBase }}background:#e8f6ec;color:#1b6b36;border:1px solid #bfe3ca">{{ session('success') }}
    </div>
@endif
@if (session('info'))
    <div style="{{ $flashBase }}background:#eef4ff;color:#1e40af;border:1px solid #c7d7fe">{{ session('info') }}</div>
@endif
@if (session('error'))
    <div style="{{ $flashBase }}background:#fdeaea;color:#a12626;border:1px solid #f3c1c1">{{ session('error') }}
    </div>
@endif
@if ($errors->any())
    <div style="{{ $flashBase }}background:#fdeaea;color:#a12626;border:1px solid #f3c1c1">
        @foreach ($errors->all() as $e)
            <div>{{ $e }}</div>
        @endforeach
    </div>
@endif