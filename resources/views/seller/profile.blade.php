@extends('layouts.seller')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
@endpush

@section('content')
    @include('seller.styles')
    <style>
        #avatarBtn {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            cursor: pointer;
            overflow: hidden;
            position: relative;
            border: 0;
            padding: 0
        }

        #avatarBtn img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        #cropModal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100
        }

        #cropModal.open {
            display: flex
        }

        .crop-card {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            width: min(420px, 92vw)
        }

        .crop-stage {
            height: 300px;
            background: #222;
            border-radius: 8px;
            overflow: hidden;
            margin: 10px 0
        }

        .crop-stage img {
            display: block;
            max-width: 100%
        }

        /* kotak crop dibuat bulat */
        .cropper-view-box,
        .cropper-face {
            border-radius: 50%
        }

        .cropper-view-box {
            outline: 0;
            box-shadow: 0 0 0 2px #fff
        }

        .cropper-dashed,
        .cropper-line,
        .cropper-point {
            display: none
        }

        .crop-btns {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 12px
        }

        .sc-ghost {
            background: #eee;
            color: #222;
            border: 0;
            border-radius: 8px;
            padding: 8px 18px;
            cursor: pointer
        }
    </style>

    <div class="sc-wrap">
        <h3 style="margin:0 0 12px">Profile</h3>
        <form method="POST" action="{{ route('seller.profile.update') }}" enctype="multipart/form-data" class="sc-card">
            @csrf
            <div class="sc-split">
                <div>
                    <label class="sc-label">Profile Picture</label>
                    <button type="button" id="avatarBtn" title="Pilih foto">
                        <img id="avatarImg" alt=""
                            src="{{ $user->USERS_PROFILE_PICTURE ? asset('storage/' . $user->USERS_PROFILE_PICTURE) : '' }}"
                            style="{{ $user->USERS_PROFILE_PICTURE ? '' : 'display:none' }}">
                        <span id="avatarPlus" style="{{ $user->USERS_PROFILE_PICTURE ? 'display:none' : '' }}">+</span>
                    </button>
                    <input type="file" id="avatarPick" accept="image/*" hidden>
                    <input type="file" name="picture" id="avatarFinal" hidden>

                    <label class="sc-label">Brand/Designer Name</label>
                    <input class="sc-input" name="brand" value="{{ old('brand', $user->USERS_NAME) }}" required>
                    <label class="sc-label">Whatsapp Number</label>
                    <input class="sc-input" name="whatsapp" value="{{ old('whatsapp', $user->USERS_PHONE_NUMBER) }}">
                    <label class="sc-label">Instagram</label>
                    <input class="sc-input" name="instagram" value="{{ old('instagram', $user->USERS_INSTAGRAM) }}">
                </div>

                <div>
                    <label class="sc-label">Offline Store Address</label>
                    <textarea class="sc-input" name="address" rows="9">{{ old('address', $user->USERS_ADDRESS) }}</textarea>
                </div>
            </div>
            <div class="sc-actions"><button class="sc-btn">Save Changes</button></div>
        </form>
    </div>

    {{-- Modal crop foto --}}
    <div id="cropModal">
        <div class="crop-card">
            <b>Atur foto profil</b>
            <div style="font-size:11px;color:#666">Geser dan zoom sampai pas di dalam lingkaran.</div>
            <div class="crop-stage"><img id="cropImg" alt=""></div>
            <input type="range" id="zoomRange" min="1" max="3" step="0.01" value="1"
                style="width:100%">
            <div class="crop-btns">
                <button type="button" class="sc-ghost" id="cropCancel">Cancel</button>
                <button type="button" class="sc-btn" id="cropOk">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        (function() {
            const btn = document.getElementById('avatarBtn'),
                pick = document.getElementById('avatarPick'),
                fin = document.getElementById('avatarFinal'),
                img = document.getElementById('avatarImg'),
                plus = document.getElementById('avatarPlus'),
                modal = document.getElementById('cropModal'),
                cropImg = document.getElementById('cropImg'),
                zoom = document.getElementById('zoomRange');
            let cropper = null,
                base = 1,
                srcUrl = null;

            const close = () => {
                modal.classList.remove('open');
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                if (srcUrl) {
                    URL.revokeObjectURL(srcUrl);
                    srcUrl = null;
                }
                pick.value = ''; // supaya foto yang sama bisa dipilih lagi
            };

            btn.onclick = () => pick.click();

            pick.onchange = () => {
                const f = pick.files[0];
                if (!f) return;
                if (!f.type.startsWith('image/')) {
                    alert('File harus berupa gambar');
                    pick.value = '';
                    return;
                }
                srcUrl = URL.createObjectURL(f);
                modal.classList.add('open');
                cropImg.onload = () => {
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(cropImg, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.9,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        guides: false,
                        center: false,
                        highlight: false,
                        background: false,
                        toggleDragModeOnDblclick: false,
                        ready() {
                            const d = cropper.getImageData();
                            base = d.width / d.naturalWidth; // skala "pas" awal
                            zoom.value = 1;
                        },
                        zoom(e) {
                            if (e.detail.ratio < base - 0.001) e.preventDefault();
                        }
                    });
                };
                cropImg.src = srcUrl;
            };

            zoom.oninput = () => cropper && cropper.zoomTo(base * parseFloat(zoom.value));

            document.getElementById('cropCancel').onclick = close;

            document.getElementById('cropOk').onclick = () => {
                if (!cropper) return;
                cropper.getCroppedCanvas({
                    width: 400,
                    height: 400,
                    imageSmoothingQuality: 'high'
                }).toBlob(blob => {
                    const file = new File([blob], 'avatar.jpg', {
                        type: 'image/jpeg'
                    });
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fin.files = dt.files; // ikut ke-submit sebagai "picture"
                    img.src = URL.createObjectURL(blob); // langsung tampil di lingkaran
                    img.style.display = 'block';
                    plus.style.display = 'none';
                    close();
                }, 'image/jpeg', 0.9);
            };
        })();
    </script>
@endsection