<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Support\Phone;

class ProfileController extends Controller
{
    public function __construct(private ShippingService $shipping) {}

    private function uid(): string
    {
        return (string) session('user_id');
    }

    public function show()
    {
        $user = DB::table('users')->where('USERS_ID', $this->uid())->first();
        abort_if(! $user, 404);

        return view('customer.profile', [
            'user'        => $user,
            'address'     => Address::forUser($this->uid()),
            'provinces'   => $this->shipping->provinces(),
            'cityOptions' => $this->shipping->cityOptions(),
        ]);
    }

    public function update(Request $request)
    {
        $uid = $this->uid();

        $data = $request->validate([
            'username'    => ['required', 'string', 'max:50', Rule::unique('users', 'USERS_NAME')->ignore($uid, 'USERS_ID')],
            'phone' => ['required', function ($attribute, $value, $fail) {
                if (! Phone::normalize($value)) {
                    $fail('Invalid phone number. Please enter a valid Indonesian mobile number.');
                }
            }],
            'province'    => ['required', Rule::in($this->shipping->provinces())],
            'city'        => ['required', 'string', 'max:80'],
            'district'    => ['required', 'string', 'max:80'],
            'postal_code' => ['required', 'digits:5'],
            'street'      => ['required', 'string', 'max:255'],
            'detail'      => ['nullable', 'string', 'max:255'],
            'label' => ['required', Rule::in(['Home', 'Office', 'Boarding House', 'Other'])],
        ], [
            'username.unique'    => 'Username sudah dipakai.',
            'province.in'        => 'Pilih provinsi dari daftar.',
            'postal_code.digits' => 'Kode pos harus 5 digit angka.',
        ]);

        if (
            $data['province'] === 'Jawa Timur'
            && ! in_array($data['city'], $this->shipping->cityOptions(), true)
        ) {
            return back()->withErrors(['city' => 'Pilih kota/kabupaten dari daftar.'])->withInput();
        }

        // simpan dalam format 628xxx (sama seperti saat register)
        $phone = Phone::normalize($data['phone']) ?? $data['phone'];

        DB::transaction(function () use ($uid, $data, $phone) {
            DB::table('users')->where('USERS_ID', $uid)->update([
                'USERS_NAME'         => $data['username'],
                'USERS_PHONE_NUMBER' => $phone,
                'updated_at'         => now(),
            ]);

            $c = Address::COL;
            $values = [
                $c['street']   => $data['street'],
                $c['district'] => $data['district'],
                $c['city']     => $data['city'],
                $c['province'] => $data['province'],
                $c['postal']   => $data['postal_code'],
                $c['detail']   => $data['detail'] ?? null,
                // kolom NOT NULL di tabel addresses
                'ADDRESSES_LABEL' => $data['label'],
                'ADDRESSES_RECIPIENT' => $data['username'],
            ];

            $address = Address::forUser($uid);

            if ($address) {
                $address->update($values);
            } else {
                do {
                    $id = 'AD' . strtoupper(Str::random(6)); // 8 karakter
                } while (Address::where('ADDRESSES_ID', $id)->exists());

                Address::create($values + [
                    'ADDRESSES_ID'         => $id,
                    'USERS_ID'             => $uid,
                    'ADDRESSES_IS_DEFAULT' => 1,
                    $c['status_del']       => 0,
                ]);
            }
        });

        session(['user_name' => $data['username']]);

        if ($request->input('next') === 'checkout') {
            return redirect()->route('checkout.index')->with('success', 'Profil tersimpan. Lanjutkan checkout.');
        }

        return redirect()->route('profile')->with('success', 'Profil berhasil disimpan.');
    }
}
