@extends('layouts.app')

@section('title', 'Wishlist')

@push('styles')
    @include('components.ui_styles')
    <style>
        .wish-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: var(--shadow-card);
            padding: 26px
        }

        .wish-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0
        }

        .wish-item {
            display: flex;
            gap: 16px;
            padding: 14px 24px 22px 0
        }

        .wish-item:nth-child(even) {
            padding: 14px 0 22px 24px;
            border-left: 1px solid #ddd
        }

        .wish-img {
            width: 130px;
            height: 172px;
            flex-shrink: 0;
            background: #d9d9d9;
            border-radius: 4px;
            overflow: hidden
        }

        .wish-img img {
            width: 100%;
            height: 100%;
            object-fit: cover
        }

        .wish-body {
            flex: 1;
            font-size: 12px;
            display: flex;
            flex-direction: column
        }

        .wish-name {
            font-weight: 600;
            font-size: 13px
        }

        .wish-name:hover {
            color: var(--brand)
        }

        .wish-sub {
            color: var(--muted);
            line-height: 1.7;
            margin-top: 2px
        }

        .wish-size {
            display: flex;
            align-items: center;
            gap: 8px
        }

        .wish-size select {
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 12px;
            background: #fff
        }

        .wish-actions {
            margin-top: auto;
            padding-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap
        }

        .wish-actions form {
            margin: 0
        }

        .wish-actions .btn {
            padding: 8px 18px;
            font-size: 12px
        }

        @media (max-width: 800px) {
            .wish-grid {
                grid-template-columns: 1fr
            }

            .wish-item,
            .wish-item:nth-child(even) {
                padding: 14px 0;
                border-left: 0;
                border-bottom: 1px solid #eee
            }
        }
    </style>
@endpush

@section('content')
    <div class="page-wrap">
        <h1 class="page-title">Wishlist</h1>

        @include('components.flash')

        <div class="wish-card">
            @if ($items->isEmpty())
                <div class="empty-state">
                    <p>Wishlist kamu masih kosong. Tap ikon hati di halaman produk untuk menyimpannya.</p>
                    <a href="{{ route('shop.index') }}" class="btn btn-brand">Lihat Produk</a>
                </div>
            @else
                <div class="wish-grid">
                    @foreach ($items as $item)
                        @php $p = $item->product; @endphp
                        <div class="wish-item">
                            <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}" class="wish-img" style="display:block">
                                @if ($p->cover_url)
                                    <img src="{{ $p->cover_url }}" alt="{{ $p->PRODUCTS_NAME }}">
                                @endif
                            </a>

                            <div class="wish-body">
                                <a href="{{ route('shop.show', $p->PRODUCTS_ID) }}"
                                    class="wish-name">{{ $p->PRODUCTS_NAME }}</a>
                                <div class="wish-sub">
                                    Brand: {{ $brands[$p->USERS_ID] ?? '-' }}<br>
                                    {{ $p->formatted_price }}
                                    @if (!$p->is_preorder && $p->PRODUCTS_STOCK <= 0)
                                        <br><span class="text-red">Stok habis</span>
                                    @endif
                                </div>

                                <div class="wish-actions">
                                    <form method="POST" action="{{ route('wishlist.toCart', $item->WISHLISTS_ID) }}"
                                        style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                                        @csrf
                                        <span class="wish-size">
                                            <label for="size-{{ $item->WISHLISTS_ID }}">Size</label>
                                            <select name="size" id="size-{{ $item->WISHLISTS_ID }}">
                                                @foreach ($p->sizes as $s)
                                                    <option value="{{ $s }}">{{ $s }}</option>
                                                @endforeach
                                            </select>
                                        </span>
                                        <button type="submit" class="btn btn-dark" @disabled(!$p->is_preorder && $p->PRODUCTS_STOCK <= 0)>Add To
                                            Cart</button>
                                    </form>

                                    <form method="POST" action="{{ route('wishlist.destroy', $item->WISHLISTS_ID) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-red">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection