@extends('layouts.seller')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
@endpush

@section('content')
    @include('seller.styles')
    <style>
        .sc-sm {
            background: #f0508c;
            color: #fff;
            border: 0;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 12px;
            cursor: pointer;
            margin-right: 6px
        }

        .sc-x {
            border: 0;
            background: transparent;
            color: #c8104a;
            cursor: pointer;
            font-size: 16px
        }

        #scBox .sc-table th {
            padding: 6px
        }

        #dz {
            border: 2px dashed #ccc;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            background: #fff
        }

        #dz.over {
            border-color: #c8104a;
            background: #fff0f5
        }

        .slots {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-top: 8px
        }

        .slot {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #999;
            position: relative
        }

        .slot button {
            position: absolute;
            top: 3px;
            border: 0;
            background: rgba(0, 0, 0, .65);
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            line-height: 1;
            font-size: 12px;
            padding: 0
        }

        .slot .sl-del {
            right: 3px
        }

        .slot .sl-edit {
            left: 3px
        }

        .slot em {
            position: absolute;
            bottom: 3px;
            left: 4px;
            font-size: 9px;
            background: #c8104a;
            color: #fff;
            padding: 0 4px;
            border-radius: 4px;
            font-style: normal
        }

        .ic-note {
            font-size: 11px;
            color: #666;
            margin: 6px 0 8px;
            line-height: 1.5
        }

        /* modal atur foto */
        #icModal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100
        }

        #icModal.open {
            display: flex
        }

        .ic-card {
            background: #fff;
            border-radius: 14px;
            padding: 16px;
            width: min(460px, 94vw)
        }

        .ic-stage {
            height: 380px;
            background: #222;
            border-radius: 8px;
            overflow: hidden;
            margin: 10px 0
        }

        .ic-stage img {
            display: block;
            max-width: 100%
        }

        .ic-btns {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap
        }

        .ic-alt {
            background: #fff;
            color: #c8104a;
            border: 1px solid #c8104a;
            border-radius: 8px;
            padding: 8px 14px;
            cursor: pointer;
            font-size: 13px
        }

        .sc-ghost {
            background: #eee;
            color: #222;
            border: 0;
            border-radius: 8px;
            padding: 8px 18px;
            cursor: pointer;
            font-size: 13px
        }

        .cropper-view-box {
            outline: 2px solid rgba(255, 255, 255, .95);
            outline-color: rgba(255, 255, 255, .95)
        }

        .cropper-dashed,
        .cropper-line,
        .cropper-point {
            display: none
        }
    </style>
    <div class="sc-wrap">
        <h3 style="margin:0 0 12px">Upload</h3>
        <form method="POST" action="{{ route('seller.upload.process') }}" enctype="multipart/form-data" class="sc-card"
            id="uform">
            @csrf
            <div class="sc-split">
                <div>
                    <label class="sc-label">Product Name</label>
                    <input class="sc-input" name="name" value="{{ old('name') }}" required>

                    <label class="sc-label">Category</label>
                    <select class="sc-input" name="category" required>
                        <option value="">-- pilih --</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->CATEGORIES_ID }}" @selected(old('category') == $c->CATEGORIES_ID)>{{ $c->CATEGORIES_NAME }}
                            </option>
                        @endforeach
                    </select>

                    <label class="sc-label">Gender</label>
                    <select class="sc-input" name="gender" required>
                        <option value="">-- pilih --</option>
                        @foreach (['Women', 'Men', 'Unisex'] as $g)
                            <option @selected(old('gender') == $g)>{{ $g }}</option>
                        @endforeach
                    </select>

                    <label class="sc-label">Price (IDR)</label>
                    <input class="sc-input" type="number" min="0" name="price" value="{{ old('price') }}"
                        required>

                    <label class="sc-label">Stock</label>
                    <input class="sc-input" type="number" min="0" name="stock" value="{{ old('stock') }}"
                        required>

                    <label class="sc-label">Material (opsional)</label>
                    <input class="sc-input" name="material" value="{{ old('material') }}">

                    <label class="sc-label">Tipe</label>
                    <select class="sc-input" name="ready_type" id="ready_type">
                        <option value="1" @selected(old('ready_type', 1) == 1)>Ready Stock</option>
                        <option value="2" @selected(old('ready_type') == 2)>Pre-Order</option>
                    </select>
                    <div id="po" style="display:none">
                        <label class="sc-label">Lama Pre-Order (hari)</label>
                        <input class="sc-input" type="number" min="1" name="preorder_days"
                            value="{{ old('preorder_days') }}">
                    </div>
                </div>

                <div>
                    <label class="sc-label">Product Description</label>
                    <textarea class="sc-input" name="description" rows="6">{{ old('description') }}</textarea>

                    <label class="sc-label">Images (Max. 5) — gambar pertama jadi foto utama</label>
                    <div class="ic-note">Semua foto harus berrasio <b>3:4 (portrait)</b>, hasil akhir
                        {{ $imgW }}×{{ $imgH }} px; disarankan foto asli minimal segitu.
                        Setelah memilih foto, atur lewat <b>Crop</b> (geser &amp; zoom), <b>Fit</b> (seluruh foto masuk,
                        sisi sisa putih),
                        atau <b>Stretch</b> (seluruh foto ditarik memenuhi bingkai, bisa terlihat melar). Klik ✎ pada foto
                        untuk mengatur ulang.</div>
                    <div id="dz">
                        <div style="font-size:26px">⬆️</div>
                        drag and drop files here<br>or<br>
                        <button type="button" id="browse" class="sc-btn-dark" style="border:0;cursor:pointer">Browse
                            Files</button>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple hidden>
                    </div>
                    <div class="slots">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="slot">{{ $i }}</div>
                        @endfor
                    </div>
                </div>
            </div>
            <div style="margin-top:20px;border-top:1px solid #eee;padding-top:8px">
                <label class="sc-label" style="font-weight:600">Size Chart <span
                        style="font-weight:400;color:#777">(opsional, boleh dikosongkan)</span></label>
                <div id="scBox"></div>
                <div style="margin-top:8px">
                    <button type="button" class="sc-sm" id="scRow">+ Baris</button>
                    <button type="button" class="sc-sm" id="scCol">+ Kolom</button>
                    <button type="button" class="sc-sm" id="scReset" style="background:#999">Kosongkan</button>
                </div>
                <div style="font-size:10px;margin-top:4px">*Ukuran dalam cm. Nama kolom bisa diubah sesuai jenis pakaian
                    (T-Shirt, Jacket, Outer, dst). Baris yang tidak diisi diabaikan.</div>
                <input type="hidden" name="size_chart_json" id="size_chart_json">
            </div>
            <div class="sc-actions"><button class="sc-btn">Publish</button></div>
        </form>
    </div>

    {{-- Modal atur foto produk --}}
    <div id="icModal">
        <div class="ic-card">
            <b>Atur foto produk</b>
            <div class="ic-note" style="margin-bottom:0">Rasio {{ $imgW }}:{{ $imgH }} (3:4). Geser &amp;
                zoom lalu <b>Crop</b>, atau pakai <b>Fit</b> / <b>Stretch</b> supaya seluruh foto masuk.</div>
            <div class="ic-stage"><img id="icImg" alt=""></div>
            <input type="range" id="icZoom" min="1" max="3" step="0.01" value="1"
                style="width:100%">
            <div class="ic-btns">
                <button type="button" class="sc-ghost" id="icCancel">Batal</button>
                <button type="button" class="ic-alt" id="icFit"
                    title="Seluruh foto masuk tanpa terpotong, sisa ruang diisi putih">Fit (latar putih)</button>
                <button type="button" class="ic-alt" id="icStretch"
                    title="Seluruh foto ditarik memenuhi bingkai, bisa terlihat melar">Stretch</button>
                <button type="button" class="sc-btn" id="icCrop">Crop</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script>
        (function() {
            const W = {{ $imgW }},
                H = {{ $imgH }},
                MAX = 5;
            const dz = document.getElementById('dz'),
                input = document.getElementById('images'),
                slots = [...document.querySelectorAll('.slot')],
                modal = document.getElementById('icModal'),
                icImg = document.getElementById('icImg'),
                zoom = document.getElementById('icZoom');
            let items = []; // { orig: File, out: File, url }
            let queue = []; // file menunggu diatur
            let cur = null; // { file, index } (index null = foto baru)
            let cropper = null,
                base = 1,
                srcUrl = null;

            slots.forEach(s => s.style.aspectRatio = W + ' / ' + H);

            function sync() {
                const dt = new DataTransfer();
                items.forEach(it => dt.items.add(it.out));
                input.files = dt.files; // ikut ke-submit
                slots.forEach((s, i) => {
                    s.replaceChildren();
                    const it = items[i];
                    if (!it) {
                        s.textContent = i + 1;
                        return;
                    }
                    const img = document.createElement('img');
                    img.src = it.url;
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:8px';
                    const del = document.createElement('button');
                    del.type = 'button';
                    del.className = 'sl-del';
                    del.textContent = '×';
                    del.title = 'Hapus';
                    del.onclick = () => {
                        URL.revokeObjectURL(it.url);
                        items.splice(i, 1);
                        sync();
                    };
                    const ed = document.createElement('button');
                    ed.type = 'button';
                    ed.className = 'sl-edit';
                    ed.textContent = '✎';
                    ed.title = 'Atur ulang';
                    ed.onclick = () => openEditor(it.orig, i);
                    s.append(img, del, ed);
                    if (i === 0) {
                        const m = document.createElement('em');
                        m.textContent = 'utama';
                        s.append(m);
                    }
                });
            }

            function openEditor(file, index) {
                if (cur) return;
                cur = {
                    file,
                    index
                };
                srcUrl = URL.createObjectURL(file);
                modal.classList.add('open');
                icImg.onload = () => {
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(icImg, {
                        aspectRatio: W / H,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        cropBoxMovable: false,
                        cropBoxResizable: false,
                        guides: false,
                        center: false,
                        highlight: false,
                        background: false,
                        toggleDragModeOnDblclick: false,
                        ready() {
                            const d = cropper.getImageData();
                            base = d.width / d.naturalWidth;
                            zoom.value = 1;
                        },
                        zoom(e) {
                            if (e.detail.ratio < base - 0.001) e.preventDefault();
                        }
                    });
                };
                icImg.src = srcUrl;
            }

            function closeEditor() {
                modal.classList.remove('open');
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
                if (srcUrl) {
                    URL.revokeObjectURL(srcUrl);
                    srcUrl = null;
                }
                cur = null;
                next();
            }

            function next() {
                if (!cur && queue.length) openEditor(queue.shift(), null);
            }

            function finish(canvas) {
                canvas.toBlob(blob => {
                    const name = (cur.file.name.replace(/\.[^.]+$/, '') || 'foto') + '.jpg';
                    const item = {
                        orig: cur.file,
                        out: new File([blob], name, {
                            type: 'image/jpeg'
                        }),
                        url: URL.createObjectURL(blob)
                    };
                    if (cur.index === null) items.push(item);
                    else {
                        URL.revokeObjectURL(items[cur.index].url);
                        items[cur.index] = item;
                    }
                    sync();
                    closeEditor();
                }, 'image/jpeg', 0.9);
            }

            const blank = () => {
                const c = document.createElement('canvas');
                c.width = W;
                c.height = H;
                const x = c.getContext('2d');
                x.fillStyle = '#fff';
                x.fillRect(0, 0, W, H);
                x.imageSmoothingQuality = 'high';
                return [c, x];
            };

            zoom.oninput = () => cropper && cropper.zoomTo(base * parseFloat(zoom.value));
            document.getElementById('icCancel').onclick = closeEditor;

            // CROP: potong sesuai bingkai
            document.getElementById('icCrop').onclick = () => {
                if (!cropper) return;
                finish(cropper.getCroppedCanvas({
                    width: W,
                    height: H,
                    fillColor: '#fff',
                    imageSmoothingQuality: 'high'
                }));
            };
            // STRETCH: seluruh foto ditarik memenuhi bingkai (tanpa crop, bisa melar)
            document.getElementById('icStretch').onclick = () => {
                const [c, x] = blank();
                x.drawImage(icImg, 0, 0, W, H);
                finish(c);
            };
            // FIT: seluruh foto masuk dengan proporsi asli, sisa ruang putih
            document.getElementById('icFit').onclick = () => {
                const [c, x] = blank();
                const k = Math.min(W / icImg.naturalWidth, H / icImg.naturalHeight);
                const w = icImg.naturalWidth * k,
                    h = icImg.naturalHeight * k;
                x.drawImage(icImg, (W - w) / 2, (H - h) / 2, w, h);
                finish(c);
            };

            function add(list) {
                for (const f of list) {
                    if (!f.type.startsWith('image/')) continue;
                    const pending = queue.length + (cur && cur.index === null ? 1 : 0);
                    if (items.length + pending >= MAX) {
                        alert('Maksimal 5 gambar');
                        break;
                    }
                    queue.push(f);
                }
                next();
            }

            dz.addEventListener('dragover', e => {
                e.preventDefault();
                dz.classList.add('over');
            });
            dz.addEventListener('dragleave', () => dz.classList.remove('over'));
            dz.addEventListener('drop', e => {
                e.preventDefault();
                dz.classList.remove('over');
                add(Array.from(e.dataTransfer.files));
            });
            document.getElementById('browse').onclick = () => input.click();
            input.addEventListener('change', () => add(Array.from(input.files)));

            document.getElementById('uform').addEventListener('submit', e => {
                if (!items.length) {
                    e.preventDefault();
                    alert('Tambahkan minimal 1 foto produk.');
                }
            });
        })();
    </script>
    <script>
        (function() {
            const defaults = () => ({
                columns: ['Bust', 'Length', 'Shoulder', 'Sleeve'],
                rows: ['S', 'M', 'L', 'XL'].map(s => ({
                    size: s,
                    values: ['', '', '', '']
                }))
            });
            let sc = defaults();
            const box = document.getElementById('scBox');

            const h = (tag, attrs = {}, ...kids) => {
                const e = document.createElement(tag);
                for (const [k, v] of Object.entries(attrs)) k.startsWith('on') ? e[k] = v : k === 'class' ? e
                    .className = v : e.setAttribute(k, v);
                kids.flat().forEach(c => e.append(c));
                return e;
            };
            const inp = (val, on, ph = '', w) => {
                const i = h('input', {
                    class: 'sc-input',
                    placeholder: ph,
                    type: 'text',
                    inputmode: 'decimal'
                });
                i.value = val ?? '';
                i.oninput = () => on(i.value);
                if (w) i.style.width = w;
                return i;
            };
            const x = fn => h('button', {
                type: 'button',
                class: 'sc-x',
                onclick: fn
            }, '×');

            function render() {
                const thead = h('tr', {}, h('th', {}, 'Size'),
                    ...sc.columns.map((col, j) => h('th', {}, inp(col, v => sc.columns[j] = v, 'Kolom (cm)'),
                        x(() => {
                            sc.columns.splice(j, 1);
                            sc.rows.forEach(r => r.values.splice(j, 1));
                            render();
                        }))),
                    h('th'));
                const body = sc.rows.map((r, ri) => h('tr', {},
                    h('td', {}, inp(r.size, v => r.size = v, 'S', '60px')),
                    ...sc.columns.map((_, j) => h('td', {}, inp(r.values[j], v => r.values[j] = v))),
                    h('td', {}, x(() => {
                        sc.rows.splice(ri, 1);
                        render();
                    }))));
                box.replaceChildren(h('div', {
                        style: 'overflow-x:auto'
                    },
                    h('table', {
                        class: 'sc-table'
                    }, h('thead', {}, thead), h('tbody', {}, body))));
            }

            document.getElementById('scRow').onclick = () => {
                sc.rows.push({
                    size: '',
                    values: sc.columns.map(() => '')
                });
                render();
            };
            document.getElementById('scCol').onclick = () => {
                sc.columns.push('');
                sc.rows.forEach(r => r.values.push(''));
                render();
            };
            document.getElementById('scReset').onclick = () => {
                sc = defaults();
                render();
            };
            document.getElementById('uform').addEventListener('submit', () => {
                document.getElementById('size_chart_json').value = JSON.stringify(
                sc); // server mengabaikan kalau kosong
            });
            render();
        })();
    </script>
    <script>
        // Price, stock, dan lama pre-order: hanya angka bulat (type="number" bawaan browser masih menerima e, +, -, .)
        document.querySelectorAll('input[name=price], input[name=stock], input[name=preorder_days]').forEach(el => {
            el.step = '1';
            el.inputMode = 'numeric';
            el.addEventListener('keydown', e => {
                if (['e', 'E', '+', '-', '.', ','].includes(e.key)) e.preventDefault();
            });
            el.addEventListener('input', () => {
                el.value = el.value.replace(/\D/g, '');
            });
            el.addEventListener('wheel', () => el.blur()); // cegah angka berubah karena scroll mouse
        });
    </script>
@endsection