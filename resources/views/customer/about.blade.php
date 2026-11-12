@extends('layouts.app')
@section('title', 'About Us - MADE BY PIDIZ')

@push('styles')
    <style>
        .about-container {
            max-width: 1000px;
            margin: 60px auto;
            padding: 0 20px 20px;
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

        /* Pembungkus video: tinggi mengikuti video, jadi halaman memanjang dan bisa di-scroll */
        .about-video {
            position: relative;
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
        }

        .about-video video {
            display: block;
            width: 100%;
            height: auto;
            pointer-events: none;
            /* tidak bisa diklik / di-pause, seperti banner */
        }

        /* Tombol suara kecil (hanya mute/unmute, bukan pause) */
        .about-sound {
            position: absolute;
            right: 14px;
            bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(0, 0, 0, .6);
            color: #fff;
            border: 0;
            border-radius: 99px;
            padding: 8px 14px;
            font-size: 13px;
            cursor: pointer;
        }

        .about-sound:hover {
            background: rgba(0, 0, 0, .8);
        }
    </style>
@endpush

@section('content')
    <div class="about-container">
        <h1 class="about-title">About Made By Pidiz</h1>
        <p class="about-description">
            Made By Pidiz is a curated fashion design showcase and e-marketplace platform
            featuring works by Universitas Ciputra Surabaya students. We bridge the gap between
            talented young designers and fashion lovers seeking authentic, innovative style.
        </p>

        <!-- Banner About Us (video): berputar terus, tanpa kontrol play/pause -->
        <div class="about-video">
            <video id="aboutVideo" loop playsinline preload="auto" disablepictureinpicture disableremoteplayback>
                <source src="{{ asset('images/Banner_FDB.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <button type="button" class="about-sound" id="aboutSound" aria-label="Nyalakan / matikan suara">
                <i class="fa-solid fa-volume-xmark" id="aboutSoundIcon"></i>
                <span id="aboutSoundHint">Klik untuk menyalakan suara</span>
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            var v = document.getElementById('aboutVideo'),
                btn = document.getElementById('aboutSound'),
                icon = document.getElementById('aboutSoundIcon'),
                hint = document.getElementById('aboutSoundHint');
            if (!v) return;

            var armed = false;
            var events = ['pointerdown', 'keydown', 'touchstart'];

            function ui() {
                icon.className = v.muted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high';
                hint.style.display = v.muted ? '' : 'none';
            }

            // Browser memblokir suara otomatis kalau pengunjung belum berinteraksi dengan situs.
            // Kalau diblokir: jalan tanpa suara dulu, lalu nyalakan suara pada klik / tap / tombol pertama.
            function onFirstInteraction(e) {
                if (btn.contains(e.target)) return; // klik tombol suara ditangani sendiri
                disarm();
                v.muted = false;
                v.play().catch(function() {});
                ui();
            }

            function arm() {
                if (armed) return;
                armed = true;
                events.forEach(function(ev) {
                    document.addEventListener(ev, onFirstInteraction);
                });
            }

            function disarm() {
                armed = false;
                events.forEach(function(ev) {
                    document.removeEventListener(ev, onFirstInteraction);
                });
            }

            function startMutedFallback() {
                v.muted = true;
                v.play().catch(function() {});
                ui();
                arm();
            }

            // 1) coba langsung dengan suara
            v.muted = false;
            var p = v.play();
            if (p && p.catch) p.catch(startMutedFallback);
            ui();

            // 2) tombol suara: hanya mute / unmute
            btn.addEventListener('click', function() {
                disarm();
                v.muted = !v.muted;
                v.play().catch(function() {});
                ui();
            });

            // 3) banner: kalau ada yang menjeda (mis. tombol media), langsung lanjut lagi
            v.addEventListener('pause', function() {
                if (!document.hidden && !v.ended) v.play().catch(function() {});
            });
            v.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });
        })();
    </script>
@endpush