{{--
    Flash message: tampil sekali per halaman (@once), hilang otomatis 6-8 detik, ada tombol X.
    Dipakai di halaman mana pun (customer, seller, admin, Home). Style inline + <style> kecil di dalamnya.

    Opsional: @include('components.flash', ['wrap' => true])
    -> dibungkus .sc-wrap (dipakai oleh partial style seller supaya sejajar dengan isi halaman).
--}}
@php
    $flashBase =
        'position:relative;margin:0 0 16px;padding:11px 40px 11px 16px;border-radius:8px;font-size:13px;text-align:left;';
    $flashes = [];
    if (session('success')) {
        $flashes[] = ['background:#e8f6ec;color:#1b6b36;border:1px solid #bfe3ca', [session('success')], 6000];
    }
    if (session('info')) {
        $flashes[] = ['background:#eef4ff;color:#1e40af;border:1px solid #c7d7fe', [session('info')], 6000];
    }
    if (session('error')) {
        $flashes[] = ['background:#fdeaea;color:#a12626;border:1px solid #f3c1c1', [session('error')], 8000];
    }
    if ($errors->any()) {
        $flashes[] = ['background:#fdeaea;color:#a12626;border:1px solid #f3c1c1', $errors->all(), 8000];
    }
@endphp

@if (count($flashes))
    @once
        <style>
            .flash-msg {
                overflow: hidden;
                transition: opacity .4s ease, max-height .4s ease, margin .4s ease, padding .4s ease, border-width .4s ease;
            }

            .flash-msg.hide {
                opacity: 0;
                max-height: 0 !important;
                margin-bottom: 0 !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                border-width: 0 !important;
            }

            .flash-close {
                position: absolute;
                top: 8px;
                right: 12px;
                background: none;
                border: 0;
                font-size: 20px;
                line-height: 1;
                color: inherit;
                opacity: .55;
                cursor: pointer;
                padding: 0;
            }

            .flash-close:hover {
                opacity: 1;
            }
        </style>

        @if ($wrap ?? false)
            <div class="sc-wrap" style="margin-bottom:0">
        @endif
        @foreach ($flashes as [$style, $msgs, $ms])
            <div class="flash-msg" role="alert" data-timeout="{{ $ms }}"
                style="{{ $flashBase }}{{ $style }}">
                @foreach ($msgs as $m)
                    <div>{{ $m }}</div>
                @endforeach
                <button type="button" class="flash-close" aria-label="Close">&times;</button>
            </div>
        @endforeach
        @if ($wrap ?? false)
            </div>
        @endif

        <script>
            (function() {
                function dismiss(el) {
                    if (el._gone) return;
                    el._gone = true;
                    el.style.maxHeight = el.offsetHeight + 'px'; // supaya bisa dianimasikan menutup
                    void el.offsetHeight;
                    el.classList.add('hide');
                    setTimeout(function() {
                        el.remove();
                    }, 450);
                }

                document.querySelectorAll('.flash-msg').forEach(function(el) {
                    var ms = parseInt(el.dataset.timeout, 10) || 6000;
                    var timer = setTimeout(function() {
                        dismiss(el);
                    }, ms);

                    el.querySelector('.flash-close').addEventListener('click', function() {
                        clearTimeout(timer);
                        dismiss(el);
                    });
                    // kursor di atas pesan = jeda timer, supaya sempat dibaca
                    el.addEventListener('mouseenter', function() {
                        clearTimeout(timer);
                    });
                    el.addEventListener('mouseleave', function() {
                        timer = setTimeout(function() {
                            dismiss(el);
                        }, 2500);
                    });
                });
            })
            ();
        </script>
    @endonce
@endif