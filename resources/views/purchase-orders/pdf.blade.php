<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        /* General Reset & Fonts */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 12px; 
            color: #000; 
            line-height: 1.5; 
            padding: 40px; 
            background: #fff;
        }
        
        /* Watermark - DomPDF compatible */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            margin-top: -250px;
            margin-left: -275px;
            width: 550px;
            opacity: 0.07;
            z-index: -1000;
        }

        /* Utility */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .text-gray { color: #555; }
        .text-small { font-size: 10px; }
        .tracking-wide { letter-spacing: 1px; }
        .nowrap { white-space: nowrap; }
        
        /* Header */
        .header { 
            width: 100%; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #000; 
            padding-bottom: 20px; 
        }
        .header table { width: 100%; }
        .header td { vertical-align: top; }
        .company-name { font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .doc-title { font-size: 24px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px; }
        
        /* Info Section */
        .info-section { 
            width: 100%; 
            margin-bottom: 30px; 
            border-bottom: 1px solid #ccc;
            padding-bottom: 20px;
        }
        .info-section table { width: 100%; }
        .info-section td { vertical-align: top; }
        .info-box h3 { 
            font-size: 10px; 
            text-transform: uppercase; 
            color: #555; 
            letter-spacing: 1px; 
            margin-bottom: 6px; 
            font-weight: bold;
        }
        .info-box p { margin-bottom: 2px; font-size: 12px; }
        
        /* Table */
        .items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 30px; 
            table-layout: fixed; /* Forces strict column widths */
        }
        .items-table th { 
            border-bottom: 2px solid #000; 
            padding: 10px 6px; 
            text-align: left; 
            font-size: 10px; 
            text-transform: uppercase; 
            font-weight: bold; 
            letter-spacing: 1px;
        }
        .items-table td { 
            border-bottom: 1px solid #ccc; 
            padding: 12px 6px; 
            font-size: 12px; 
            vertical-align: top; 
            word-wrap: break-word; /* Prevents long text from breaking table */
        }
        .items-table tr {
            page-break-inside: avoid; /* Prevents rows from splitting across pages */
        }
        
        /* Summary */
        .summary-section { width: 100%; margin-bottom: 40px; page-break-inside: avoid; }
        .summary-section table { width: 100%; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 4px 8px; font-size: 12px; }
        .summary-table .total-row td { border-top: 2px solid #000; font-size: 14px; font-weight: bold; padding-top: 10px; }
        
        /* Footer/Signatures */
        .footer { width: 100%; margin-top: 50px; border-top: 1px solid #ccc; padding-top: 20px; page-break-inside: avoid; }
        .footer table { width: 100%; }
        .footer td { vertical-align: bottom; width: 50%; }
        .signature-line { border-bottom: 1px solid #000; width: 80%; height: 40px; margin-bottom: 8px; }
    </style>
</head>
<body>

    <!-- Background Watermark -->
    <img src="{{ public_path('images/logo.svg') }}" class="watermark" alt="Watermark">

    <!-- HEADER -->
    <div class="header">
        <table>
            <tr>
                <td style="width: 60%;">
                    <div class="company-name">STEVEN HYDROTECH EXPONENT</div>
                    <div class="text-small text-gray">
                        Water Filtration & Treatment Specialist<br>
                        Branch: Legazpi Branch (MAIN)<br>
                        Contact: (02) 1234-5678 | Email: info@stevenhydrotech.com<br>
                        TIN: 123-456-789-000
                    </div>
                </td>
                <td style="width: 40%;" class="text-right">
                    <div class="doc-title">Purchase Order</div>
                    <div class="text-small text-gray font-bold">PO Number: <span style="color:#000; font-size: 13px;">{{ $purchaseOrder->po_number }}</span></div>
                    <div class="text-small text-gray font-bold">Date Issued: <span style="color:#000;">{{ $purchaseOrder->created_at->format('M d, Y') }}</span></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- INFO SECTION -->
    <div class="info-section">
        <table>
            <tr>
                <!-- VENDOR (SUPPLIER) -->
                <td style="width: 50%; padding-right: 20px;">
                    <div class="info-box">
                        <h3>Vendor / Supplier:</h3>
                        <p class="font-bold" style="font-size: 14px;">{{ $purchaseOrder->supplier->name ?? 'N/A' }}</p>
                        @if($purchaseOrder->supplier)
                            @if($purchaseOrder->supplier->address) <p>{{ $purchaseOrder->supplier->address }}</p> @endif
                            @if($purchaseOrder->supplier->contact_person) <p>Attn: {{ $purchaseOrder->supplier->contact_person }}</p> @endif
                            @if($purchaseOrder->supplier->phone) <p>Phone: {{ $purchaseOrder->supplier->phone }}</p> @endif
                            @if($purchaseOrder->supplier->email) <p>Email: {{ $purchaseOrder->supplier->email }}</p> @endif
                        @endif
                    </div>
                </td>
                
                <!-- DELIVERY LOCATIONS -->
                <td style="width: 50%;">
                    <div class="info-box">
                        <h3>Delivery Instructions:</h3>
                        <p class="font-bold" style="margin-bottom: 4px;">Please deliver to ONE of the following locations:</p>
                        
                        <p><strong>Option 1 (Main Store):</strong> Store (Legazpi Branch - MAIN)</p>
                        <p style="margin-bottom: 6px;"><strong>Option 2 (Warehouse):</strong> Warehouse (Putiao Branch)</p>
                        
                        @if($purchaseOrder->expected_delivery_date)
                            <p class="font-bold" style="margin-top: 8px;">
                                Expected Delivery: {{ \Carbon\Carbon::parse($purchaseOrder->expected_delivery_date)->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- ITEMS TABLE -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">ITEM NO.</th>
                <th style="width: 25%;">PRODUCT</th>
                <th style="width: 16%;">MAIN CATEGORY</th>
                <th style="width: 14%;">BRAND</th>
                <th style="width: 8%; text-align: center;">QTY</th>
                <th style="width: 15%; text-align: right;">UNIT COST</th>
                <th style="width: 16%; text-align: right;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $index => $item)
                @php
                    $cat = $item->product ? $item->product->category : null;
                    $mainCategory = $cat ? ($cat->parent ? $cat->parent->name : $cat->name) : '—';
                    $brand = $cat ? ($cat->parent ? $cat->name : 'N/A') : '—';
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    
                    <!-- PRODUCT -->
                    <td>
                        <span class="font-bold">{{ $item->product->product_name ?? 'Deleted Product' }}</span>
                        @if($item->product && $item->product->approx_volume)
                            <br><span class="text-small text-gray">{{ $item->product->approx_volume }}</span>
                        @endif
                    </td>
                    
                    <!-- MAIN CATEGORY -->
                    <td class="text-gray">{{ $mainCategory }}</td>
                    
                    <!-- BRAND -->
                    <td class="text-gray">{{ $brand }}</td>
                    
                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                    
                    <!-- UNIT COST (Nowrap & PHP) -->
                    <td class="text-right nowrap">PHP {{ number_format($item->unit_cost, 2) }}</td>
                    
                    <!-- TOTAL (Nowrap & PHP) -->
                    <td class="text-right font-bold nowrap">PHP {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SUMMARY & NOTES -->
    <div class="summary-section">
        <table>
            <tr>
                <!-- NOTES -->
                <td style="width: 55%; padding-right: 30px; vertical-align: top;">
                    <div class="text-small uppercase text-gray font-bold" style="letter-spacing: 1px; margin-bottom: 6px;">Notes / Instructions:</div>
                    <div style="border: 1px solid #ccc; padding: 10px; min-height: 80px; font-size: 11px; background: #fff;">
                        {{ $purchaseOrder->notes ?? 'No additional instructions provided.' }}
                    </div>
                </td>
                
                <!-- TOTALS -->
                <td style="width: 45%; vertical-align: top;">
                    <table class="summary-table">
                        <tr>
                            <td class="text-gray">Subtotal:</td>
                            <td class="text-right nowrap">PHP {{ number_format($subtotal, 2) }}</td>
                        </tr>
                        
                        {{-- Only show Tax if > 0 --}}
                        @if($taxAmount > 0)
                        <tr>
                            <td class="text-gray">Tax:</td>
                            <td class="text-right nowrap">PHP {{ number_format($taxAmount, 2) }}</td>
                        </tr>
                        @endif

                        {{-- Only show Shipping if > 0 --}}
                        @if($shippingFee > 0)
                        <tr>
                            <td class="text-gray">Shipping / Freight:</td>
                            <td class="text-right nowrap">PHP {{ number_format($shippingFee, 2) }}</td>
                        </tr>
                        @endif

                        @php
                            $linkedPayment = $purchaseOrder->supplier ? $purchaseOrder->supplier->ledgers()
                                ->where('transaction_type', 'payment')
                                ->where('purchase_order_id', $purchaseOrder->id)
                                ->where('change_amount', '>', 0)
                                ->first() : null;
                        @endphp
                        @if($linkedPayment)
                        <tr>
                            <td class="text-gray" style="color: #d97706; font-weight: bold;">Sukli / Change:</td>
                            <td class="text-right nowrap" style="color: #d97706; font-weight: bold;">- PHP {{ number_format($linkedPayment->change_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td>TOTAL AMOUNT:</td>
                            <td class="text-right nowrap">PHP {{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER / SIGNATURES -->
    <div class="footer">
        <table>
            <tr>
                <td style="text-align: left;">
                    <p class="font-bold text-sm mb-2">Prepared By / Approved By:</p>
                    <div class="signature-line"></div>
                    <p class="font-bold">{{ $purchaseOrder->user->name ?? 'System Administrator' }}</p>
                    <p class="text-small text-gray uppercase tracking-wide">Authorized Signatory</p>
                </td>
                <td style="text-align: right;">
                    <p class="font-bold text-sm mb-2">Received By (Supplier):</p>
                    <div class="signature-line" style="margin-left: auto;"></div>
                    <p class="font-bold">&nbsp;</p>
                    <p class="text-small text-gray uppercase tracking-wide">Signature over Printed Name / Date</p>
                </td>
            </tr>
        </table>
        
        <div class="text-center text-small text-gray" style="margin-top: 30px;">
            This is a system-generated Purchase Order. Valid without a signature if processed through the supplier portal.
        </div>
    </div>

</body>
</html>