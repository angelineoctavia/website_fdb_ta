@extends('layouts.app')
@section('title', 'About Us - MADE BY PIDIZ')

@push('styles')
    <style>
        .about-container {
            max-width: 1000px;
            margin: 60px auto;
            padding: 0 20px;
            text-align: center;
            min-height: 60vh;
        }

        .about-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .about-description {
            font-size: 16px;
            color: #4b5563;
            line-height: 1.8;
            max-width: 800px;
            margin: 0 auto 40px auto;
        }

        .about-card-placeholder {
            width: 100%;
            height: 300px;
            background-color: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="about-container">
        <h1 class="about-title">About Made By Pidiz</h1>
        <p class="about-description">
            Fashionology / Made By Pidiz merupakan platform showcase dan e-commerce curated karya desain fashion dari
            mahasiswa Universitas Ciputra Surabaya. Kami menghubungkan desainer muda berbakat dengan pecinta fashion yang
            mencari karya autentik, inovatif, dan berkarakter.
        </p>

        <!-- Placeholder Konten / Banner About Us -->
        <div class="about-card-placeholder">
            [ Video / Banner Profile Brand Fashionology UC ]
        </div>
    </div>
@endsection