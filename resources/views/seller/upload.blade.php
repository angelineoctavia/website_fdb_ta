@extends('layouts.seller')
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
            aspect-ratio: 1;
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
            top: 2px;
            right: 2px;
            border: 0;
            background: rgba(0, 0, 0, .6);
            color: #fff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            cursor: pointer;
            line-height: 1
        }

        .slot em {
            position: absolute;
            bottom: 2px;
            left: 4px;
            font-size: 9px;
            background: #c8104a;
            color: #fff;
            padding: 0 4px;
            border-radius: 4px;
            font-style: normal
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

    <script>
        const dz = document.getElementById('dz'),
            input = document.getElementById('images'),
            slots = [...document.querySelectorAll('.slot')];
        let files = [];

        function sync() {
            const dt = new DataTransfer();
            files.forEach(f => dt.items.add(f));
            input.files = dt.files; // supaya ikut ke-submit
            slots.forEach((s, i) => {
                s.innerHTML = '';
                if (files[i]) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(files[i]);
                    img.style.cssText = 'width:100%;height:100%;object-fit:cover;border-radius:8px';
                    const x = document.createElement('button');
                    x.type = 'button';
                    x.textContent = '×';
                    x.onclick = () => {
                        files.splice(i, 1);
                        sync();
                    };
                    s.append(img, x);
                    if (i === 0) {
                        const m = document.createElement('em');
                        m.textContent = 'utama';
                        s.append(m);
                    }
                } else s.textContent = i + 1;
            });
        }

        function add(list) {
            for (const f of list) {
                if (!f.type.startsWith('image/')) continue;
                if (files.length >= 5) {
                    alert('Maksimal 5 gambar');
                    break;
                }
                files.push(f);
            }
            sync();
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

        const rt = document.getElementById('ready_type'),
            po = document.getElementById('po');
        const togglePO = () => po.style.display = rt.value === '2' ? 'block' : 'none';
        rt.addEventListener('change', togglePO);
        togglePO();
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