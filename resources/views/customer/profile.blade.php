@extends('layouts.app')

@section('title', 'Profile')

@push('styles')
    @include('components.ui_styles')
    <style>
        .profile-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 28px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 40px
        }

        .profile-col h3 {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 14px
        }

        .field {
            margin-bottom: 14px
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 13px;
            background: #fff;
            outline: none;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--brand)
        }

        .field textarea {
            resize: vertical;
            min-height: 70px
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px
        }

        .hint {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px
        }

        .profile-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px
        }

        .profile-actions .btn {
            padding: 11px 28px
        }

        @media (max-width: 800px) {
            .profile-card {
                grid-template-columns: 1fr
            }
        }
    </style>
@endpush

@section('content')
    @php
        $a = $address;
        $v = fn($key, $form, $fallback = '') => old($form, $a ? $a->field($key) : $fallback);
    @endphp
    <div class="page-wrap">
        <h1 class="page-title">Profile</h1>

        @include('components.flash')

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="next" value="{{ request('next') }}">

            <div class="profile-card">
                {{-- ===== KIRI: AKUN ===== --}}
                <div class="profile-col">
                    <h3>Akun</h3>

                    <div class="field">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                            value="{{ old('username', $user->USERS_NAME) }}" required>
                    </div>

                    <div class="field">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            value="{{ old('phone', $user->USERS_PHONE_NUMBER) }}" placeholder="08xx / +62" required>
                    </div>
                </div>

                {{-- ===== KANAN: ALAMAT (satu box = satu kolom database) ===== --}}
                <div class="profile-col">
                    <h3>Alamat Pengiriman</h3>

                    <div class="field">
                        <label for="label">Label Alamat</label>
                        <select id="label" name="label" required>
                            @foreach (['Home', 'Office', 'Boarding House', 'Other'] as $l)
                                <option value="{{ $l }}" @selected($v('label', 'label', 'Home') === $l)>{{ $l }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="street">Alamat Rumah</label>
                        <textarea id="street" name="street" placeholder="Nama jalan, nomor rumah, RT/RW" required>{{ $v('street', 'street') }}</textarea>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="province">Provinsi</label>
                            <select id="province" name="province" required>
                                <option value="" disabled @selected(!$v('province', 'province'))>Pilih provinsi</option>
                                @foreach ($provinces as $p)
                                    <option value="{{ $p }}" @selected($v('province', 'province') === $p)>{{ $p }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="city-select">Kota / Kabupaten</label>

                            {{-- Jawa Timur: pilih dari daftar --}}
                            <select id="city-select" name="city" required>
                                <option value="" disabled @selected(!$v('city', 'city'))>Pilih kota / kabupaten</option>
                                @foreach ($cityOptions as $c)
                                    <option value="{{ $c }}" @selected($v('city', 'city') === $c)>{{ $c }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Provinsi lain: ketik manual --}}
                            <input type="text" id="city-text" name="city" value="{{ $v('city', 'city') }}"
                                placeholder="Ketik kota / kabupaten" required>
                        </div>
                    </div>

                    <div class="field-row">
                        <div class="field">
                            <label for="district">Kecamatan</label>
                            <input type="text" id="district" name="district" value="{{ $v('district', 'district') }}"
                                required>
                        </div>
                        <div class="field">
                            <label for="postal_code">Kode Pos</label>
                            <input type="text" id="postal_code" name="postal_code" inputmode="numeric" maxlength="5"
                                value="{{ $v('postal', 'postal_code') }}" required>
                        </div>
                    </div>

                    <div class="field">
                        <label for="detail">Detail Lainnya</label>
                        <input type="text" id="detail" name="detail" value="{{ $v('detail', 'detail') }}"
                            placeholder="Patokan, warna pagar, dll (opsional)">
                        <p class="hint">Ongkir dihitung otomatis dari provinsi dan kota ini.</p>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <button type="submit" class="btn btn-brand">Save Changes</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const prov = document.getElementById('province');
        const citySelect = document.getElementById('city-select');
        const cityText = document.getElementById('city-text');

        function toggleCity() {
            const jatim = prov.value === 'Jawa Timur';
            citySelect.hidden = !jatim;
            citySelect.disabled = !jatim;
            cityText.hidden = jatim;
            cityText.disabled = jatim;
        }

        prov.addEventListener('change', toggleCity);
        toggleCity();
    </script>
@endpush