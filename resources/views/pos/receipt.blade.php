@extends('layouts.default')

@section('title', 'Receipt')

@section('content')

    <div class="flex justify-center items-center min-h-[70vh] py-8 print:block print:min-h-0 print:py-0 print:bg-white">
        <div class="w-full flex flex-col items-center print:block print:w-auto">
            
            <!-- Success Checkmark Banner & Title (Hidden on Print) -->
            <div class="text-center mb-8 print:hidden">
                <div class="flex justify-center mb-3">
                    <span class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/65 text-emerald-600 dark:text-emerald-400 border-2 border-emerald-200 dark:border-emerald-900 shadow-xs">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order Created!</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                    Transaction #{{ $transaction->transaction_id ?? $transaction->id }} has been successfully created.
                </p>
            </div>

            <!-- ON-SCREEN THERMAL RECEIPT PREVIEW CONTAINER -->
            <div id="printableReceipt" class="w-full max-w-[360px] bg-white dark:bg-[#10141D] text-gray-900 dark:text-gray-100 border-2 border-gray-200 dark:border-[#1e2536] rounded-3xl p-6 text-left shadow-lg font-mono text-[11px] leading-tight print:border-none print:shadow-none print:p-0 print:m-0 print:bg-white print:text-black">
                
                @php
                    $receiptItems = $transaction->transactionItems ?? $transaction->items ?? [];
                    $hasProject = collect($receiptItems)->contains('item_type', 'project');
                    $hasProduct = collect($receiptItems)->contains('item_type', 'product');
                    
                    $typeLabel = 'RETAIL SALE';
                    if ($hasProject && !$hasProduct) $typeLabel = 'PROJECT / SERVICE';
                    if ($hasProject && $hasProduct) $typeLabel = 'MIXED ORDER';

                    // Aggregators for the breakdown
                    $projectOutsource = 0;
                    $projectLabor = 0;
                    $projectInstall = 0;
                    $projectAdd = 0;
                    $projectFreight = 0;
                    $projectMarkup = 0;
                    $projectMaterialsTotal = 0;
                    $retailSubtotal = 0;
                    $totalItemDiscounts = 0;
                @endphp

                <!-- Business Name Header with Logo -->
                <div class="text-center pb-5">
                    <!-- Logo (Forced Grayscale using Tailwind classes and inline style) -->
                    <div class="flex justify-center mb-3">
                        <img src="{{ asset('images/logo.svg') }}" alt="System Logo" class="w-16 h-16 object-contain grayscale contrast-125" style="filter: grayscale(100%) contrast(200%) !important;">
                    </div>

                    <h2 class="text-[13px] font-bold tracking-wider uppercase text-gray-900 dark:text-white print:text-black">
                        STEVEN HYDROTECH EXPONENT
                    </h2>
                    <p class="text-[9px] text-gray-500 uppercase tracking-widest mt-1 print:text-gray-700">
                        Water Filtration & Treatment Specialist
                    </p>
                    <p class="text-[9px] text-gray-500 uppercase tracking-widest mt-1 print:text-gray-700">
                        Branch: Legazpi Branch (MAIN)
                    </p>

                    <div class="mt-4 mb-1 text-[10px] font-bold tracking-widest text-gray-800 dark:text-gray-200 uppercase print:text-black">
                        OFFICIAL SALES RECEIPT
                    </div>
                </div>

                <div class="border-t-2 border-dashed border-gray-200 dark:border-[#1e2536] my-2 print:border-black"></div>

                <!-- Transaction Metadata -->
                <div class="py-1 space-y-1.5 text-[10px] text-gray-600 dark:text-slate-400 print:text-black">
                    <div class="flex justify-between">
                        <span>Receipt #:</span>
                        <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ $transaction->transaction_id ?? 'SALE-' . strtoupper(uniqid()) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Date/Time:</span>
                        <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ \Carbon\Carbon::parse($transaction->created_at)->format('M j, Y, g:i A') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Cashier:</span>
                        <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ $transaction->user->name ?? ($transaction->user->firstname ?? 'System') . ' ' . ($transaction->user->lastname ?? '') }}</span>
                    </div>
                    
                    <!-- Order Type -->
                    <div class="flex justify-between">
                        <span>Order Type:</span>
                        <span class="font-bold text-gray-900 dark:text-white uppercase tracking-wider print:text-black">{{ $typeLabel }}</span>
                    </div>

                    @if(!empty($transaction->customer_name))
                        <div class="flex justify-between">
                            <span>Customer:</span>
                            <span class="font-bold text-gray-900 dark:text-white print:text-black">{{ $transaction->customer_name }}</span>
                        </div>
                    @endif
                </div>

                <div class="border-t-2 border-dashed border-gray-200 dark:border-[#1e2536] my-2 print:border-black"></div>

                <!-- ITEMS TABLE HEADER -->
                <div class="flex justify-between text-[9px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-2 print:text-black">
                    <span>ITEM / QTY</span>
                    <span>AMOUNT</span>
                </div>

                <!-- ITEMS LIST -->
                <div class="space-y-3 pb-2 border-b-2 border-dashed border-gray-200 dark:border-[#1e2536] print:border-black">
                    @forelse ($receiptItems as $item)
                        @if($item->item_type === 'project')
                            <!-- PROJECT ITEM RENDERING -->
                            @php
                                $breakdown = $item->project_breakdown;
                                
                                $projectOutsource += $breakdown['outsource_cost'] ?? 0;
                                $projectLabor += $breakdown['labor_cost'] ?? 0;
                                $projectInstall += $breakdown['installation_cost'] ?? 0;
                                $projectAdd += $breakdown['additional_cost'] ?? 0;
                                $projectFreight += $breakdown['freight'] ?? 0;
                                $projectMarkup += $breakdown['markup_amount'] ?? 0;
                                $projectMaterialsTotal += $breakdown['materials_subtotal'] ?? 0;
                            @endphp
                            <div class="space-y-1.5 pb-1">
                                <div class="flex justify-between items-start font-bold text-gray-900 dark:text-white text-[11px] print:text-black">
                                    <span>{{ str_replace('PROJECT: ', '', $item->product_name) }} (x1)</span>
                                    <span>₱{{ number_format($item->subtotal, 2) }}</span>
                                </div>
                                
                                <div class="pl-2 border-l border-gray-300 dark:border-gray-600 space-y-1 mt-1 print:border-black">
                                    @foreach($breakdown['materials'] as $mat)
                                        <div class="flex justify-between text-[9px] text-gray-600 dark:text-slate-400 print:text-gray-800">
                                            <span>{{ $mat['qty'] }}x {{ $mat['name'] }} <span class="text-[8px] text-gray-400 uppercase print:text-gray-600">({{ $mat['source'] }})</span></span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <!-- STANDARD RETAIL PRODUCT RENDERING -->
                            @php
                                $retailSubtotal += $item->subtotal;
                                $baseItemTotal = $item->selling_price * $item->quantity;
                                $itemDiscount = max(0, $baseItemTotal - $item->subtotal);
                                $totalItemDiscounts += $itemDiscount;
                                $sku = $item->product_sku ?? ($item->product ? $item->product->product_sku : '—');
                            @endphp
                            <div class="space-y-1">
                                <div class="flex justify-between items-start font-bold text-gray-900 dark:text-white text-[11px] print:text-black">
                                    <span>{{ $item->product_name }} (x{{ $item->quantity }})</span>
                                </div>
                                <div class="flex justify-between text-[10px] print:text-black">
                                    <span class="text-gray-600 dark:text-slate-400 print:text-gray-800">@ ₱{{ number_format($item->selling_price, 2) }}/unit</span>
                                    <span class="font-bold text-gray-900 dark:text-white print:text-black">₱{{ number_format($item->subtotal, 2) }}</span>
                                </div>
                                <div class="text-[9px] text-gray-500 font-normal print:text-gray-700">SKU: {{ $sku }}</div>
                                
                                @if($itemDiscount > 0)
                                    <div class="flex justify-between text-[9px] text-gray-900 dark:text-gray-100 font-medium print:text-black">
                                        <span>Item Disc:</span>
                                        <span>-₱{{ number_format($itemDiscount, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @empty
                        <p class="text-xs text-gray-400 text-center py-2 print:text-black">No items found for this transaction.</p>
                    @endforelse
                </div>

                <!-- SUMMARY TOTALS & BREAKDOWN -->
                <div class="pt-2 space-y-1.5 text-[10px] text-gray-600 dark:text-slate-400 print:text-black">
                    
                    <div class="flex justify-between">
                        <span>Items & Materials Subtotal:</span>
                        <span class="font-semibold text-gray-900 dark:text-white print:text-black">₱{{ number_format($retailSubtotal + $projectMaterialsTotal, 2) }}</span>
                    </div>

                    @if($hasProject)
                        @if(($projectOutsource ?? 0) > 0)
                            <div class="flex justify-between">
                                <span>Outsource Materials:</span>
                                <span class="font-semibold text-gray-900 dark:text-white print:text-black">₱{{ number_format($projectOutsource, 2) }}</span>
                            </div>
                        @endif

                        @if(($projectLabor + $projectInstall + $projectAdd) > 0)
                            <div class="flex justify-between">
                                <span>Labor & Services:</span>
                                <span class="font-semibold text-gray-900 dark:text-white print:text-black">₱{{ number_format($projectLabor + $projectInstall + $projectAdd, 2) }}</span>
                            </div>
                        @endif
                        
                        @if($projectMarkup > 0)
                            <div class="flex justify-between">
                                <span>Project Mark Up:</span>
                                <span class="font-semibold text-gray-900 dark:text-white print:text-black">₱{{ number_format($projectMarkup, 2) }}</span>
                            </div>
                        @endif
                    @endif

                    @if($totalItemDiscounts > 0)
                        <div class="flex justify-between text-gray-900 dark:text-gray-100 print:text-black">
                            <span>Item Discounts:</span>
                            <span class="font-semibold">-₱{{ number_format($totalItemDiscounts, 2) }}</span>
                        </div>
                    @endif

                    @php
                        $cartDiscountAmount = (float)($transaction->cart_discount_value ?? 0);
                        $dbCartDiscType = strtolower(trim($transaction->cart_discount_type ?? 'none'));
                        $cartDiscLabel = 'Cart Discount';
                        if ($dbCartDiscType !== 'none' && $dbCartDiscType !== '') {
                            $cartDiscLabel = 'Cart Discount (' . ucwords($dbCartDiscType) . ')';
                        }
                    @endphp

                    @if($cartDiscountAmount > 0)
                        <div class="flex justify-between text-gray-900 dark:text-gray-100 print:text-black">
                            <span>{{ $cartDiscLabel }}:</span>
                            <span class="font-semibold">-₱{{ number_format($cartDiscountAmount, 2) }}</span>
                        </div>
                    @endif

                    @php
                        $isImported = str_starts_with($transaction->transaction_id ?? '', 'IMP-') || ($transaction->source ?? '') === 'imported';
                        $vatRate = 0.12;
                        $vatAmount = $transaction->total_amount - ($transaction->total_amount / (1 + $vatRate));
                    @endphp
                    @if(!$isImported)
                    <div class="flex justify-between">
                        <span>Tax (12% VAT Incl.):</span>
                        <span class="font-semibold text-gray-900 dark:text-white print:text-black">₱{{ number_format($vatAmount, 2) }}</span>
                    </div>
                    @endif

                    @if($projectFreight > 0)
                        <div class="flex justify-between">
                            <span>Shipping / Freight:</span>
                            <span class="font-semibold text-gray-900 dark:text-white print:text-black">₱{{ number_format($projectFreight, 2) }}</span>
                        </div>
                    @endif

                    <div class="border-t-2 border-dashed border-gray-200 dark:border-[#1e2536] my-1.5 print:border-black"></div>

                    <div class="flex justify-between font-extrabold text-[12px] text-gray-900 dark:text-white pt-1 print:text-black">
                        <span>TOTAL DUE:</span>
                        <span>₱{{ number_format($transaction->total_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Payment & Change Section -->
                <div class="border-t-2 border-dashed border-gray-200 dark:border-[#1e2536] mt-2 pt-2 pb-2 space-y-1.5 text-[10px] print:border-black">
                    <div class="flex justify-between text-gray-600 dark:text-slate-400 print:text-black">
                        <span>Payment:</span>
                        <span class="font-bold text-gray-900 dark:text-white print:text-black">₱{{ number_format($transaction->amount_paid, 2) }}</span>
                    </div>
                    @if($transaction->amount_change > 0)
                        <div class="flex justify-between text-gray-600 dark:text-slate-400 print:text-black">
                            <span>Change:</span>
                            <span class="font-bold text-gray-900 dark:text-white print:text-black">₱{{ number_format($transaction->amount_change, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="border-t-2 border-dashed border-gray-200 dark:border-[#1e2536] my-1 print:border-black"></div>

                <!-- Footer Note -->
                <div class="text-center pt-3 text-[9px] text-gray-500 dark:text-gray-400 space-y-1 mt-1 print:text-black">
                    <p class="font-bold tracking-wide text-gray-700 dark:text-gray-300 uppercase print:text-black">THANK YOU FOR YOUR BUSINESS!</p>
                    <p class="leading-tight px-2 print:text-gray-800">Please keep this receipt for warranty and service records.</p>
                </div>

            </div>

            <!-- ACTIONS (Hidden when printing) -->
            <div class="flex items-center justify-center gap-3 pt-6 w-full max-w-[360px] print:hidden">
                <a href="{{ route('pos') }}" class="w-1/2 text-center bg-white border-2 border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-emerald-600/80 dark:text-emerald-400 dark:hover:bg-emerald-600 dark:hover:text-white font-semibold py-3 rounded-full transition-all duration-300 ease-in-out shadow-xs text-xs">
                    New Sale
                </a>
                <button type="button" onclick="window.print()" class="w-1/2 bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white dark:bg-[#10141D] dark:border-2 dark:border-[#1e2536] dark:text-slate-300 dark:hover:bg-[#161B26] dark:hover:text-white text-xs font-semibold py-3 rounded-full transition-all duration-300 ease-in-out shadow-xs cursor-pointer">
                    Print Receipt
                </button>
            </div>

        </div>
    </div>

    <!-- Strict Thermal Printer & Preview Stylesheet -->
    <style>
        @media print {
            /* Try to force 80mm size */
            @page {
                size: 80mm auto;
                margin: 0;
            }
            
            /* Force the body to align top-left, preventing the centering issue */
            html, body {
                width: 80mm !important; 
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                color: black !important;
                display: block !important; /* Fixes huge zoom issue */
            }

            /* Hide everything */
            body * {
                visibility: hidden;
            }

            /* Reveal ONLY the receipt */
            #printableReceipt, #printableReceipt * {
                visibility: visible;
            }

            /* Force it to the top-left corner */
            #printableReceipt {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 80mm !important; /* Forces the width, preventing stretching */
                max-width: 80mm !important;
                padding: 5mm !important; /* Safe margin for thermal printers */
                margin: 0 !important;
                background: white !important;
                color: black !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            /* Override Tailwind Color classes to Black */
            .print\:text-black, h2, span, p {
                color: black !important;
            }
            .print\:border-black {
                border-color: black !important;
            }
            .print\:hidden {
                display: none !important;
            }
        }
    </style>
@endsection