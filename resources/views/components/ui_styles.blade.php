{{-- Style bersama untuk halaman customer (Shop, Detail, Cart, Wishlist).
     Dipakai lewat:  @push('styles') @include('components.ui_styles') ... @endpush --}}
<style>
    :root {
        --red-btn: #d32f2f;
        --muted: #7a7a7a;
        --wa: #25d366;
        --shadow-card: 0 4px 18px rgba(0, 0, 0, .12);
    }

    /* konten halaman mengisi seluruh ruang di antara header dan footer */
    main {
        display: flex;
        flex-direction: column
    }

    main>.shop-layout,
    main>.page-wrap,
    main>.detail-page {
        flex: 1
    }

    /* footer langsung menempel, tidak ada strip putih */
    .footer {
        margin-top: 0 !important
    }

    .btn {
        display: inline-block;
        padding: 9px 20px;
        border: 0;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #fff;
        cursor: pointer;
        text-align: center;
    }

    .btn-red {
        background: var(--red-btn)
    }

    .btn-red:hover {
        background: #b52626
    }

    .btn-dark {
        background: var(--dark)
    }

    .btn-dark:hover {
        background: #111
    }

    .btn-brand {
        background: var(--brand)
    }

    .btn-brand:hover {
        opacity: .9
    }

    .btn-wa {
        background: var(--wa)
    }

    .btn.disabled,
    .btn:disabled {
        opacity: .45;
        cursor: not-allowed
    }

    .btn-block {
        display: block;
        width: 100%;
        margin-top: 18px
    }

    .btn-icon {
        width: 38px;
        height: 38px;
        border: 1px solid #ccc;
        background: #fff;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
        color: #333;
    }

    .btn-icon:hover {
        color: var(--brand);
        border-color: var(--brand)
    }

    .btn-icon.on {
        color: var(--brand);
        border-color: var(--brand)
    }

    .text-red {
        color: var(--red-btn)
    }

    .muted {
        color: var(--muted)
    }

    .img-placeholder {
        width: 100%;
        height: 100%;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #d9d9d9;
        color: #888;
        font-size: 12px;
    }

    .badge-preorder {
        position: absolute;
        top: 8px;
        left: 8px;
        background: var(--brand);
        color: #fff;
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 10px;
    }

    .badge-preorder.static {
        position: static;
        display: inline-block
    }

    .flash {
        margin: 0 0 16px;
        padding: 11px 16px;
        border-radius: 8px;
        font-size: 13px
    }

    .flash-ok {
        background: #e8f6ec;
        color: #1b6b36;
        border: 1px solid #bfe3ca
    }

    .flash-err {
        background: #fdeaea;
        color: #a12626;
        border: 1px solid #f3c1c1
    }

    .page-wrap {
        padding: 24px 40px 40px;
        background: #f5f5f5
    }

    .page-title {
        font-size: 15px;
        font-weight: 600;
        margin-bottom: 14px
    }

    .empty-state {
        text-align: center;
        padding: 70px 0;
        color: var(--muted)
    }

    .empty-state p {
        margin-bottom: 16px
    }

    @media (max-width: 900px) {
        .page-wrap {
            padding: 18px
        }
    }
</style>
