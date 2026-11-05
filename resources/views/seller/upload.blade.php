@extends('layouts.seller')
@section('title', isset($product) ? 'Edit Product' : 'Upload')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
@endpush

@section('content')
    @include('seller.styles')
    @php
        // Halaman ini dipakai untuk Upload (tanpa $product) dan Edit (dengan $product).
        $isEdit = isset($product) && $product;
        $val = fn($key, $default = '') => $isEdit ? $product->{$key} ?? $default : $default;
        $readyType = (int) old('ready_type', $val('PRODUCTS_READY_TYPE', 1));
    @endphp
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

        .sc-input:disabled {
            background: #f1f1f1;
            color: #999;
            cursor: not-allowed
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
            top: 3px;
            right: 3px
        }

        .slot .sl-edit {
            top: 3px;
            left: 3px
        }

        .slot .sl-star {
            top: 27px;
            right: 3px;
            background: rgba(200, 16, 74, .85)
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
        <h3 style="margin:0 0 12px">{{ $isEdit ? 'Edit Product' : 'Upload' }}</h3>
        <form method="POST"
            action="{{ $isEdit ? route('seller.products.update', $product->PRODUCTS_ID) : route('seller.upload.process') }}"
            enctype="multipart/form-data" class="sc-card" id="uform">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="sc-split">
                <div>
                    <label class="sc-label">Product Name</label>
                    <input class="sc-input" name="name" value="{{ old('name', $val('PRODUCTS_NAME')) }}" required>

                    <label class="sc-label">Category</label>
                    <select class="sc-input" name="category" required>
                        <option value="">-- pilih --</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->CATEGORIES_ID }}" @selected(old('category', $val('CATEGORIES_ID')) == $c->CATEGORIES_ID)>
                                {{ $c->CATEGORIES_NAME }}</option>
                        @endforeach
                    </select>

                    <label class="sc-label">Gender</label>
                    <select class="sc-input" name="gender" required>
                        <option value="">-- pilih --</option>
                        @foreach (['Women', 'Men', 'Unisex'] as $g)
                            <option @selected(old('gender', $val('PRODUCTS_GENDER')) == $g)>{{ $g }}</option>
                        @endforeach
                    </select>

                    <label class="sc-label">Price (IDR)</label>
                    <input class="sc-input" type="number" min="0" name="price"
                        value="{{ old('price', $isEdit ? (int) $product->PRODUCTS_PRICE : '') }}" required>

                    {{-- Tipe tepat di bawah Price --}}
                    <label class="sc-label">Tipe</label>
                    <select class="sc-input" name="ready_type" id="ready_type">
                        <option value="1" @selected($readyType === 1)>Ready Stock</option>
                        <option value="2" @selected($readyType === 2)>Pre-Order</option>
                    </select>

                    {{-- Ready Stock: Stock aktif, Days nonaktif. Pre-Order: kebalikannya (Stock dikunci). --}}
                    <label class="sc-label">Stock</label>
                    <input class="sc-input" type="number" min="0" name="stock" id="stockInput"
                        value="{{ old('stock', $isEdit ? (int) $product->PRODUCTS_STOCK : '') }}">
                    <div class="ic-note" id="stockHint" style="margin:4px 0 0"></div>

                    <label class="sc-label">Days (lama Pre-Order)</label>
                    <input class="sc-input" type="number" min="1" name="preorder_days" id="daysInput"
                        value="{{ old('preorder_days', $val('PRODUCTS_PREORDER_DURATION')) }}">

                    <label class="sc-label">Material (opsional)</label>
                    <input class="sc-input" name="material" value="{{ old('material', $val('PRODUCTS_MATERIAL')) }}">
                </div>

                <div>
                    <label class="sc-label">Product Description</label>
                    <textarea class="sc-input" name="description" rows="6">{{ old('description', $val('PRODUCTS_DESCRIPTION')) }}</textarea>

                    <label class="sc-label">Images (Max. 5) — gambar pertama jadi foto utama</label>
                    <div class="ic-note">Semua foto harus berrasio <b>3:4 (portrait)</b>, hasil akhir
                        {{ $imgW }}×{{ $imgH }} px; disarankan foto asli minimal segitu.
                        Setelah memilih foto, atur lewat <b>Crop</b> (geser &amp; zoom), <b>Fit</b> (seluruh foto masuk,
                        sisi sisa putih),
                        atau <b>Stretch</b> (seluruh foto ditarik memenuhi bingkai, bisa terlihat melar). Klik ✎ pada foto
                        baru untuk mengatur ulang{{ $isEdit ? ', ★ untuk menjadikan foto lama sebagai foto utama' : '' }}.
                    </div>
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
                    <div id="imgHidden"></div>
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

            <div class="sc-actions">
                @if ($isEdit)
                    <a href="{{ route('seller.products') }}" class="sc-ghost"
                        style="text-decoration:none;display:inline-block;margin-right:8px">Cancel</a>
                    <button class="sc-btn">Update</button>
                @else
                    <button class="sc-btn">Publish</button>
                @endif
            </div>
        </form>
    </div>

    {{-- Modal atur foto produk --}}
    <div id="icModal">
        <div class="ic-card">
            <b>Atur foto produk</b>
            <div class="ic-note" style="margin-bottom:0">Rasio {{ $imgW }}:{{ $imgH }} (3:4). Geser
                &amp;
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
        // ================= FOTO PRODUK =================
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

            // items: foto lama {kind:'old', id, url} (hanya saat Edit) atau foto baru {kind:'new', orig, out, url}
            const existing = @json($images ?? []);
            let items = existing.map(e => ({
                kind: 'old',
                id: e.id,
                url: e.url
            }));
            let primaryId = (existing.find(e => e.primary) || {}).id ?? null;
            let removed = []; // id foto lama yang dibuang
            let queue = []; // file menunggu diatur
            let cur = null; // { file, index } (index null = foto baru)
            let cropper = null,
                base = 1,
                srcUrl = null;

            slots.forEach(s => s.style.aspectRatio = W + ' / ' + H);

            function primaryIndex() {
                const i = items.findIndex(it => it.kind === 'old' && it.id === primaryId);
                return i >= 0 ? i : 0;
            }

            function sync() {
                const dt = new DataTransfer();
                items.filter(it => it.kind === 'new').forEach(it => dt.items.add(it.out));
                input.files = dt.files; // hanya foto baru yang ikut ke-submit
                const pIdx = primaryIndex();
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
                    s.append(img);

                    const del = document.createElement('button');
                    del.type = 'button';
                    del.className = 'sl-del';
                    del.textContent = '×';
                    del.title = 'Hapus';
                    del.onclick = () => {
                        if (it.kind === 'old') removed.push(it.id);
                        else URL.revokeObjectURL(it.url);
                        items.splice(i, 1);
                        sync();
                    };
                    s.append(del);

                    if (it.kind === 'new') {
                        const ed = document.createElement('button');
                        ed.type = 'button';
                        ed.className = 'sl-edit';
                        ed.textContent = '✎';
                        ed.title = 'Atur ulang';
                        ed.onclick = () => openEditor(it.orig, i);
                        s.append(ed);
                    } else if (i !== pIdx) {
                        const st = document.createElement('button');
                        st.type = 'button';
                        st.className = 'sl-star';
                        st.textContent = '★';
                        st.title = 'Jadikan foto utama';
                        st.onclick = () => {
                            primaryId = it.id;
                            sync();
                        };
                        s.append(st);
                    }
                    if (i === pIdx) {
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
                        kind: 'new',
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
                    return;
                }
                // kirim daftar foto lama yang dibuang + pilihan foto utama
                const box = document.getElementById('imgHidden');
                box.replaceChildren();
                const hid = (n, v) => {
                    const i = document.createElement('input');
                    i.type = 'hidden';
                    i.name = n;
                    i.value = v;
                    box.append(i);
                };
                removed.forEach(id => hid('remove_images[]', id));
                const pi = items.findIndex(it => it.kind === 'old' && it.id === primaryId);
                if (pi >= 0) hid('primary_image', primaryId);
            });

            sync();
        })();
    </script>

    <script>
        // ================= SIZE CHART =================
        (function() {
            const defaults = () => ({
                columns: ['Bust', 'Length', 'Shoulder', 'Sleeve'],
                rows: ['S', 'M', 'L', 'XL'].map(s => ({
                    size: s,
                    values: ['', '', '', '']
                }))
            });
            const saved = @json($sizeChart ?? null);
            let sc = (saved && Array.isArray(saved.columns) && Array.isArray(saved.rows)) ? saved : defaults();
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
                    ...sc.columns.map((_, j) => h('td', {}, inp((r.values || [])[j], v => {
                        r.values = r.values || [];
                        r.values[j] = v;
                    }))),
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
                sc.rows.forEach(r => (r.values = r.values || []).push(''));
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
        // ================= PRICE / STOCK / DAYS + TIPE =================
        (function() {
            // Hanya angka bulat (type="number" bawaan browser masih menerima e, +, -, .)
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

            // Ready Stock <-> Pre-Order
            const type = document.getElementById('ready_type'),
                stock = document.getElementById('stockInput'),
                days = document.getElementById('daysInput'),
                hint = document.getElementById('stockHint');
            let lastStock = stock.value;

            function applyType() {
                const pre = type.value === '2';
                if (pre) {
                    if (!stock.disabled) lastStock = stock.value;
                    stock.value = '0';
                } else if (stock.disabled) {
                    stock.value = lastStock === '0' ? '' : lastStock;
                }
                stock.disabled = pre; // dikunci saat Pre-Order
                stock.required = !pre;
                days.disabled = !pre; // aktif hanya saat Pre-Order
                days.required = pre;
                if (!pre) days.value = '';
                hint.textContent = pre ?
                    'Stok dikunci untuk Pre-Order. Produk Pre-Order tidak bisa masuk keranjang, hanya wishlist.' :
                    'Stok berkurang otomatis saat customer checkout.';
            }
            type.addEventListener('change', applyType);
            applyType();
        })();
    </script>
@endsection