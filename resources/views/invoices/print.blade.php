<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('Invoice Print') }} - #{{ $invoice->id }}</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 24px;
                color: #18181b;
            }

            h1, h2, h3 {
                margin: 0;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 24px;
                border-bottom: 1px solid #d4d4d8;
                padding-bottom: 12px;
            }

            .meta-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 12px;
                margin-bottom: 18px;
            }

            .meta-card {
                border: 1px solid #e4e4e7;
                border-radius: 8px;
                padding: 10px 12px;
            }

            .meta-label {
                font-size: 11px;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: #71717a;
                margin-bottom: 4px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
                margin-top: 12px;
            }

            th,
            td {
                border: 1px solid #d4d4d8;
                padding: 8px;
                text-align: left;
            }

            th {
                background: #f4f4f5;
            }

            .text-right {
                text-align: right;
            }

            .toolbar {
                margin-bottom: 14px;
            }

            .toolbar button {
                padding: 8px 14px;
                border-radius: 6px;
                border: 1px solid #3f3f46;
                background: #18181b;
                color: #ffffff;
                cursor: pointer;
            }

            @media print {
                .toolbar {
                    display: none;
                }

                body {
                    margin: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="toolbar">
            <button type="button" onclick="window.print()">{{ __('Print / Save as PDF') }}</button>
        </div>

        <div class="header">
            <div>
                <h2>APK Vendor</h2>
                <p>{{ __('Approved Invoice Document') }}</p>
            </div>
            <div class="text-right">
                <h3>{{ __('Invoice #') }}{{ $invoice->id }}</h3>
                <p>{{ __('PO #') }}{{ $invoice->po_id }}</p>
            </div>
        </div>

        <div class="meta-grid">
            <div class="meta-card">
                <div class="meta-label">{{ __('Vendor') }}</div>
                <div>{{ $invoice->vendor?->company_name ?? '-' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">{{ __('Contact') }}</div>
                <div>{{ $invoice->vendor?->user?->name ?? '-' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">{{ __('Invoice Date') }}</div>
                <div>{{ $invoice->created_at?->format('Y-m-d H:i') ?? '-' }}</div>
            </div>
            <div class="meta-card">
                <div class="meta-label">{{ __('Status') }}</div>
                <div>{{ strtoupper($invoice->status->value) }}</div>
            </div>
        </div>

        <h3>{{ __('Purchase Order Items') }}</h3>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('Price') }}</th>
                    <th>{{ __('Subtotal') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($invoice->purchaseOrder?->items ?? collect()) as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format((float) $item->price, 2) }}</td>
                        <td>{{ number_format((float) $item->qty * (float) $item->price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ __('No PO items available.') }}</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">{{ __('Total') }}</th>
                    <th>{{ number_format((float) ($invoice->purchaseOrder?->total_price ?? 0), 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
