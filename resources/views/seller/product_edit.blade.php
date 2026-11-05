@extends('layouts.seller')
@section('title', 'Edit Product')

@push('styles')
    <style>
        .edit-container {
            padding: 30px 40px;
            background: #f4f5f7;
            min-height: 80vh;
            display: flex;
            justify-content: center;
        }

        .edit-wrapper {
            width: 100%;
            max-width: 650px;
        }

        .edit-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 32px;
        }

        .edit-card h2 {
            font-size: 22px;
            font-weight: 700;
            color: #111;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13.5px;
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #b8004c;
            box-shadow: 0 0 0 3px rgba(184, 0, 76, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-update {
            background: #b8004c;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-update:hover {
            background: #9a003f;
        }

        .btn-cancel {
            color: #4b5563;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            padding: 10px 16px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .btn-cancel:hover {
            background: #f3f4f6;
            color: #111;
        }

        .alert-error {
            background: #ffeeef;
            color: #b8004c;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid #f9d8dc;
        }
    </style>
@endpush

@section('content')
    <div class="edit-container">
        <div class="edit-wrapper">
            <div class="edit-card">
                <h2>Edit Product</h2>

                @if ($errors->any())
                    <div class="alert-error">
                        <ul style="margin-left: 16px; margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('seller.products.update', $product->PRODUCTS_ID) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $product->PRODUCTS_NAME) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Price (Rp)</label>
                        <input type="number" name="price" class="form-control"
                            value="{{ old('price', $product->PRODUCTS_PRICE) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control"
                            value="{{ old('stock', $product->PRODUCTS_STOCK) }}" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            @foreach ($categories as $c)
                                <option value="{{ $c->CATEGORIES_ID }}" @selected($c->CATEGORIES_ID == $product->CATEGORIES_ID)>
                                    {{ $c->CATEGORIES_NAME }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Product Type</label>
                        <select name="ready_type" class="form-control" required>
                            <option value="1" @selected($product->PRODUCTS_READY_TYPE == 1)>Ready Stock</option>
                            <option value="2" @selected($product->PRODUCTS_READY_TYPE == 2)>Pre-Order</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control">{{ old('description', $product->PRODUCTS_DESCRIPTION) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Material</label>
                        <input type="text" name="material" class="form-control"
                            value="{{ old('material', $product->PRODUCTS_MATERIAL) }}">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-update">Update Product</button>
                        <a href="{{ route('seller.products') }}" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection