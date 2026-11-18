<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Report - {{ date('M d, Y') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { color: #555; margin-top: 0; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 10px; }
        .text-right { text-align: right; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer; font-weight: bold;">Print Again</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer; margin-left: 10px;">Close Window</button>
    </div>

    <h1>Inventory Stock Report</h1>
    <p>Generated on {{ date('F d, Y - h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>SKU</th>
                <th>Category</th>
                <th class="text-right">Cost</th>
                <th class="text-right">Price</th>
                <th class="text-right">Store Qty</th>
                <th class="text-right">Warehouse Qty</th>
                <th class="pyx-right">Total Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                @php
                    $storeQty = $product->store_quantity ?? 0;
                    $whQty = $product->warehouse_quantity ?? 0;
                @endphp
                <tr>
                    <td><strong>{{ $product->product_name }}</strong></td>
                    <td>{{ $product->product_sku ?? '—' }}</td>
                    <td>{{ $product->category->name ?? '—' }}</td>
                    <td class="text-right">₱{{ number_format($product->purchase_cost ?? 0, 2) }}</td>
                    <td class="text-right">₱{{ number_format($product->selling_price ?? 0, 2) }}</td>
                    <td class="text-right">{{ $storeQty }}</td>
                    <td class="text-right">{{ $whQty }}</td>
                    <td class="text-right"><strong>{{ $storeQty + $whQty }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #777;">No products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>