@extends('layouts.seller')
@section('title', 'Admin Profile')
@section('header')@include('components.admin_header')@endsection

@section('content')
    @include('seller.styles')
    @php $pct = (int) round($feeRate * 100); @endphp
    <div class="sc-wrap">
        <h3 style="margin:0 0 12px">Profile</h3>
        <div class="sc-split">
            <div class="sc-card">
                <div style="display:flex;align-items:center;gap:14px">
                    <div
                        style="width:64px;height:64px;border-radius:50%;background:#cc0a48;color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700">
                        {{ strtoupper(mb_substr($user->USERS_NAME, 0, 1)) }}</div>
                    <div>
                        <div style="font-weight:600;font-size:16px">{{ $user->USERS_NAME }}</div>
                        <div style="font-size:12px;color:#777">Administrator</div>
                    </div>
                </div>
                <table style="font-size:13px;margin-top:16px;width:100%;border-spacing:0 8px">
                    <tr>
                        <td style="color:#777;width:40%">User ID</td>
                        <td>{{ $user->USERS_ID }}</td>
                    </tr>
                    <tr>
                        <td style="color:#777">Nomor telepon</td>
                        <td>{{ $user->USERS_PHONE_NUMBER ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#777">Akun dibuat</td>
                        <td>{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d M Y') : '-' }}</td>
                    </tr>
                </table>
            </div>

            <div style="display:grid;gap:16px;align-content:start">
                <div class="sc-card">
                    <b>Pembagian Pendapatan</b>
                    <p style="font-size:13px;margin:8px 0 0">Semua pembayaran masuk ke akun admin (Midtrans) lebih dulu.
                        Admin mendapat <b>{{ $pct }}%</b> dan seller (mahasiswa) mendapat
                        <b>{{ 100 - $pct }}%</b> dari setiap transaksi.</p>
                </div>
                <div class="sc-card">
                    <b>Keamanan Akun</b>
                    <p style="font-size:13px;margin:8px 0 0">Password admin tidak bisa diubah dari website ini. Untuk
                        mengganti password atau data akun,
                        hubungi pusat database.</p>
                </div>
            </div>
        </div>
    </div>
@endsection