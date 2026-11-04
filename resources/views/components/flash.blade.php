@if (session('success'))
    <div class="flash flash-ok">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="flash flash-err">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="flash flash-err">{{ $errors->first() }}</div>
@endif