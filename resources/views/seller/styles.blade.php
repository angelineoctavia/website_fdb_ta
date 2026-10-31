<style>
    .sc-wrap {
        max-width: 1100px;
        margin: 24px auto;
        padding: 0 16px
    }

    .sc-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .08);
        padding: 16px
    }

    .sc-card small {
        font-size: 12px;
        color: #444
    }

    .sc-card h3 {
        margin: 6px 0 0;
        color: #c8104a;
        font-size: 20px
    }

    .sc-grid4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px
    }

    .sc-grid21 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 16px;
        margin-top: 16px
    }

    .sc-grid11 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-top: 16px
    }

    .sc-label {
        font-size: 12px;
        margin: 12px 0 4px;
        display: block
    }

    .sc-input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #fff;
        box-sizing: border-box
    }

    .sc-btn {
        background: #f0508c;
        color: #fff;
        border: 0;
        border-radius: 8px;
        padding: 8px 20px;
        cursor: pointer
    }

    .sc-btn-dark {
        background: #222;
        color: #fff;
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 12px;
        text-decoration: none
    }

    .sc-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px
    }

    .sc-table th {
        background: #c8104a;
        color: #fff;
        padding: 10px;
        text-align: left
    }

    .sc-table td {
        padding: 10px;
        border-bottom: 1px solid #eee
    }

    .sc-table tr:nth-child(even) td {
        background: #f5f5f5
    }

    .sc-pill {
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 12px;
        background: #eee
    }

    .sc-pill.pending {
        background: #fff3cd
    }

    .sc-pill.paid,
    .sc-pill.packing {
        background: #d1ecf1
    }

    .sc-pill.shipping {
        background: #e2d9f3
    }

    .sc-pill.completed {
        background: #d4edda
    }

    .sc-pill.cancelled {
        background: #f8d7da
    }

    .sc-alert {
        padding: 10px 14px;
        border-radius: 8px;
        margin-bottom: 12px;
        font-size: 13px
    }

    .sc-ok {
        background: #d4edda
    }

    .sc-err {
        background: #f8d7da
    }

    .sc-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px
    }

    .sc-actions {
        text-align: right;
        margin-top: 16px
    }

    @media(max-width:800px) {

        .sc-grid4,
        .sc-grid21,
        .sc-grid11,
        .sc-split {
            grid-template-columns: 1fr
        }
    }
</style>
@if (session('success'))
    <div class="sc-wrap" style="margin-bottom:0">
        <div class="sc-alert sc-ok">{{ session('success') }}</div>
    </div>
@endif
@if ($errors->any())
    <div class="sc-wrap" style="margin-bottom:0">
        <div class="sc-alert sc-err">
            @foreach ($errors->all() as $e)
                <div>{{ $e }}</div>
            @endforeach
        </div>
    </div>
@endif