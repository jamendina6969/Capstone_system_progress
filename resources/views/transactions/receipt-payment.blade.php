@extends('layouts.default')

@section('title', 'Payment Voucher')

@section('content')
    <div class="max-w-4xl mx-auto">
        
        <!-- Header Actions -->
        <div class="flex items-center justify-between mb-6 print:hidden">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Payment Voucher</h1>
                <p class="text-sm text-gray-500 mt-1">Official record of supplier disbursement</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="printVoucher()" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs cursor-pointer">
                    Print Voucher
                </button>
                <a href="{{ route('transaction.history') }}" class="bg-white border-2 border-slate-300 text-slate-700 hover:bg-slate-700 hover:text-white text-sm font-semibold px-5 py-2.5 rounded-full transition-all shadow-xs">
                    &larr; Back
                </a>
            </div>
        </div>

        <!-- Helpful Prompt for the User -->
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-xl text-sm flex items-start gap-3 print:hidden">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <strong class="font-bold">Printing Tip:</strong> When the print window opens, click <strong>"More settings"</strong> and <strong>uncheck "Headers and footers"</strong> to remove the browser's default date and URL from the paper.
            </div>
        </div>

        <!-- Printable Voucher Area -->
        <div id="printable-voucher" class="bg-white border border-gray-300 shadow-sm p-5 sm:p-12 relative overflow-x-auto">
            
            <!-- SUBTLE BACKGROUND WATERMARK -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.03] z-0">
                <img src="{{ asset('images/logo.svg') }}" alt="Watermark" class="w-[550px] h-auto object-contain">
            </div>

            <!-- CONTENT WRAPPER -->
            <div class="relative z-10 font-sans text-gray-900">

                <!-- DOCUMENT HEADER -->
                <div class="flex justify-between items-start border-b-2 border-black pb-6 mb-8">
                    <div class="w-3/5">
                        <h1 class="text-2xl font-bold uppercase tracking-widest text-black mb-1">Steven Hydrotech Exponent</h1>
                        <p class="text-[10px] text-gray-600 uppercase tracking-wider mb-2">Water Filtration & Treatment Specialist</p>
                        
                        <div class="text-[11px] text-gray-600 leading-relaxed">
                            Branch: Legazpi Branch (MAIN)<br>
                            Contact: (02) 1234-5678 | Email: info@stevenhydrotech.com<br>
                            TIN: 123-456-789-000
                        </div>
                    </div>
                    <div class="text-right w-2/5">
                        <h2 class="text-2xl font-bold uppercase tracking-widest text-black mb-2">Payment Voucher</h2>
                        <div class="text-[11px] text-gray-800 space-y-0.5">
                            <p><span class="font-bold text-gray-500 uppercase tracking-wider">Voucher No:</span> <span class="font-mono text-black text-sm">{{ str_pad($ledger->id, 6, '0', STR_PAD_LEFT) }}</span></p>
                            <p><span class="font-bold text-gray-500 uppercase tracking-wider">Date:</span> <span class="text-black">{{ $ledger->created_at->format('F d, Y') }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- PAYEE AND DETAILS SECTION -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 mb-8 border-b border-gray-300 pb-6">
                    <div>
                        <h3 class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2">Paid To (Vendor)</h3>
                        <p class="text-sm font-bold text-black">{{ $ledger->supplier->name ?? 'N/A' }}</p>
                        @if($ledger->supplier)
                            <div class="text-[11px] text-gray-600 mt-1 space-y-0.5">
                                @if($ledger->supplier->address) <p>{{ $ledger->supplier->address }}</p> @endif
                                @if($ledger->supplier->contact_person) <p>Attn: {{ $ledger->supplier->contact_person }}</p> @endif
                                @if($ledger->supplier->phone) <p>Phone: {{ $ledger->supplier->phone }}</p> @endif
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <h3 class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2">Payment Details</h3>
                        <div class="text-[11px] text-gray-800 space-y-1">
                            <p><span class="font-bold">Method:</span> Cash / Bank Transfer</p>
                            <p><span class="font-bold">Status:</span> <span class="text-black font-bold uppercase tracking-wide">PAID</span></p>
                            @if($ledger->purchaseOrder)
                                <p><span class="font-bold">Linked PO:</span> {{ $ledger->purchaseOrder->po_number }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- ITEMS TABLE -->
                <div class="mb-8 relative z-10 bg-white/80">
                    <table class="w-full text-left text-sm border-collapse">
                        <thead>
                            <tr class="border-b-2 border-black">
                                <th class="py-2 px-1 font-bold text-black uppercase text-[10px] tracking-wider w-12 text-center">Item</th>
                                <th class="py-2 px-1 font-bold text-black uppercase text-[10px] tracking-wider">Description</th>
                                <th class="py-2 px-1 font-bold text-black uppercase text-[10px] tracking-wider text-right w-40">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-300">
                            <tr>
                                <td class="py-4 px-1 text-center text-gray-600 text-xs">1</td>
                                <td class="py-4 px-1">
                                    <p class="font-bold text-black">{{ $ledger->description }}</p>
                                    @if($ledger->purchaseOrder)
                                        <p class="text-[10px] text-gray-500 mt-0.5">Ref: Purchase Order {{ $ledger->purchaseOrder->po_number }}</p>
                                    @endif
                                </td>
                                <td class="py-4 px-1 text-right font-bold text-black text-sm">
                                    ₱{{ number_format($ledger->amount, 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SUMMARY -->
                <div class="flex justify-end mb-12 relative z-10 bg-white/80">
                    <div class="w-1/2">
                        <table class="w-full text-sm border-t-2 border-black">
                            <tr>
                                <td class="py-2 text-black-500 text-xs font-bold">Amount Tendered:</td>
                                <td class="py-2 text-right font-medium text-black-500">₱{{ number_format($ledger->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="py-1 text-black-500 text-xs font-bold">Tax / Fees:</td>
                                <td class="py-1 text-right font-medium text-black-500">₱0.00</td>
                            </tr>
                            
                            {{-- SUKLI / CHANGE ROW --}}
                            @if($ledger->change_amount > 0)
                            <tr>
                                <td class="py-2 text-black-500 text-xs font-bold">Change:</td>
                                <td class="py-2 text-right font-medium text-black-500">- ₱{{ number_format($ledger->change_amount, 2) }}</td>
                            </tr>
                            @endif
                            
                            <tr class="border-t border-gray-300">
                                <td class="py-3 font-bold text-black text-[11px] uppercase tracking-widest">Net Amount Paid:</td>
                                <td class="py-3 text-right font-bold text-black text-base">
                                    ₱{{ number_format($ledger->amount - ($ledger->change_amount ?? 0), 2) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- SIGNATURES -->
                <div class="grid grid-cols-2 gap-16 mt-16 pt-4 relative z-10">
                    <div>
                        <div class="border-b border-black w-full mb-2"></div>
                        <p class="text-[11px] font-bold text-black uppercase tracking-wider mb-0.5">Prepared By:</p>
                        <p class="text-[10px] text-gray-500">Authorized Signatory</p>
                    </div>
                    <div>
                        <div class="border-b border-black w-full mb-2"></div>
                        <p class="text-[11px] font-bold text-black uppercase tracking-wider mb-0.5">Received By:</p>
                        <p class="text-[10px] text-gray-500">Supplier Representative</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function printVoucher() {
        const voucherContent = document.getElementById('printable-voucher').innerHTML;
        
        const iframe = document.createElement('iframe');
        iframe.style.position = 'absolute';
        iframe.style.width = '0px';
        iframe.style.height = '0px';
        iframe.style.border = 'none';
        document.body.appendChild(iframe);

        const iframeDoc = iframe.contentWindow.document;
        iframeDoc.open();
        // By keeping the title completely blank, we attempt to suppress the browser's top header
        iframeDoc.write(`
            <html>
                <head>
                    <title></title> 
                    <script src="https://cdn.tailwindcss.com"><\/script>
                    <style>
                        @page { size: A4 portrait; margin: 0; }
                        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; padding: 0; background: #fff; }
                        .absolute { position: absolute !important; }
                        img { max-width: 100%; height: auto; }
                    </style>
                </head>
                <body>
                    <!-- Top padding acts as the physical margin so we avoid header space -->
                    <div style="padding: 40px; position: relative; min-height: 100vh;">
                        ${voucherContent}
                    </div>
                </body>
            </html>
        `);
        iframeDoc.close();

        iframe.onload = function() {
            setTimeout(() => {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 1000);
            }, 500); 
        };
    }
</script>
@endpush