<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $record->invoice_number }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4 portrait;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            font-weight: 400;
        }
        
        .container {
            width: 90%;
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm;
        }
        
        /* Header Section */
        .header {
            width: 100%;
            margin-bottom: 25px;
            border-bottom: 1px solid #6366f1;
            padding-bottom: 15px;
        }

        .company-logo p{
            font-size:11px;
            color:#666;
        }
        
        .header-table {
            width: 100%;
            table-layout: fixed;
        }
        
        .header-left {
            width: 33.3%;
            vertical-align: middle;
        }

        .header-center {
            width: 33.3%;
            vertical-align: middle;
            text-align: center;
        }
        
        .header-right {
            width: 33.3%;
            vertical-align: middle;
            text-align: right;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        
        .company-tagline {
            font-size: 11px;
            color: #6366f1;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .company-info {
            font-size: 11px;
            color: #666;
            line-height: 1.2;
        }
        
        .invoice-title {
            font-size: 16px;
            font-weight: bold;
            color: #111;
        }
        
        .invoice-number {
            font-size: 11px;
            color: #6366f1;
            font-weight: bold;
        }
        
        .date-box {
            font-size: 10px;
        }
        
        .date-line {
            margin-bottom: 2px;
        }
        
        .date-line:last-child {
            margin-bottom: 0;
        }
        
        /* Content Section */
        .content-section {
            width: 100%;
            margin-bottom: 10px;
        }
        
        .content-table {
            width: 100%;
            table-layout: fixed;
        }
        
        .content-left {
            width: 48%;
            vertical-align: top;
            padding-right: 1%;
        }
        
        .content-right {
            width: 48%;
            vertical-align: top;
            padding-left: 1%;
            text-align: right;
        }
        
        .info-box {
            padding: 0px;
            margin-bottom: 10px;
        }
        
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            margin-bottom: 3px;
        }
        
        .client-name {
            font-size: 11px;
            font-weight: bold;
            color: #111;
            text-transform: uppercase;
        }
        
        .client-company {
            font-size: 11px;
            color: #6366f1;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .client-info {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
        }
        
        .detail-line {
            font-size: 11px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #374151;
        }
        
        .detail-value {
            color: #111;
            font-weight: bold;
        }
        
        .status-badge {
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { color: #374151; }
        .status-sent { color: #1d4ed8; }
        .status-paid { color: #065f46; }
        
        /* Items Section */
        .items-section {
            margin-bottom: 15px;
        }
        
        .items-title {
            font-size: 11px;
            font-weight: bold;
            color: #111;
            margin-bottom: 8px;
            border-bottom: 1px solid #6366f1;
            padding-bottom: 2px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .items-table thead {
            background: #6366f1;
        }
        
        .items-table th {
            font-weight: bold;
            text-transform: uppercase;
            color: white;
            text-align: left;
        }
        
        .items-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        
        .item-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }
        
        .item-description {
            color: #666;
            font-size: 8px;
            line-height: 1.2;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .col-desc { width: 50%; }
        .col-qty { width: 8%; }
        .col-unit { width: 8%; }
        .col-price { width: 17%; }
        .col-total { width: 17%; }
        
        .qty-badge, .unit-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 1px 3px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 2px;
        }
        
        .unit-badge {
            background: #f0fdf4;
            color: #166534;
        }
        
        /* Totals Section */
        .totals-section {
            margin-top: 15px;
        }
        
        .totals-table {
            width: 100%;
            table-layout: fixed;
        }
        
        .totals-spacer {
            width: 60%;
        }
        
        .totals-content {
            width: 40%;
            vertical-align: top;
        }
        
        .summary-box {
            border: 1px solid #e2e8f0;
            background: white;
        }
        
        .summary-header {
            background: #f8fafc;
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .summary-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            margin: 0;
        }
        
        .summary-body {
            padding: 8px 10px;
        }
        
        .total-table {
            width: 100%;
            margin-bottom: 4px;
        }
        
        .total-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        
        .total-label {
            text-align: left;
            font-weight: bold;
            color: #374151;
        }
        
        .total-amount {
            text-align: right;
            font-weight: bold;
            color: #111;
        }
        
        .grand-total {
        }
        
        .grand-total-table {
            width: 100%;
        }
        
        .grand-total-table td {
            font-size: 11px;
            padding: 0;
        }
        
        .balance-due {
            
        }
        
        /* Payment Information Section */
        .payment-section {
            margin-top:30px;
            margin-bottom: 15px;
        }
        
        .payment-title-section {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .payment-main-title {
            font-size: 12px;
            font-weight: bold;
            color: #111;
        }
        
        .payment-subtitle {
            font-size: 11px;
            color: #666;
        }
        
        .payment-table {
            width: 100%;
            table-layout: fixed;
        }
        
        .payment-left {
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .payment-right {
            width: 50%;
            vertical-align: top;
            padding-left: 10px;
        }
        
        .bank-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 1px solid #6366f1;
            padding: 10px;
            text-align: center;
        }
        
        .bank-title {
            font-size: 11px;
            font-weight: bold;
            color: #111;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        
        .bank-detail {
            font-size: 10px;
            color: #666;
            line-height: 1.4;
        }
        
        /* Notes Section */
        .notes-section {
            margin-top: 20px;
        }
        
        .notes-table {
            width: 100%;
            table-layout: fixed;            
            background: #f8fafc;
            padding: 10px;
        }
        
        .notes-left {
            vertical-align: top;
        }
        
        .notes-right {
            vertical-align: top;
        }
        
        .note-box {
            margin-bottom: 8px;
        }
        .note-box ul {
            list-style: decimal;
            padding-left: 20px;
        }

        .note-box ol {
            list-style: decimal;
            padding-left: 20px;
        }
        
        .note-title {
            font-size: 11px;
            font-weight: bold;
            color: #111;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .note-content {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
        }
        
        /* Footer */
        .footer {
            margin-top: 25px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        
        .thank-you {
            font-size: 8px;
            color: #666;
            margin-rgba(8, 8, 8, 1)om: 6px;
        }
        
        .generated {
            font-size: 6px;
            margin-top: 10px;
            color: #999;
            background: #f9fafb;
            padding: 3px 3px;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }
        
        .no-items {
            text-align: center;
            padding: 20px;
            color: #999;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        /* WhatsApp Link Styling */
        .whatsapp-link {
            color: #666;
            text-decoration: none;
        }
        
        .whatsapp-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <div class="invoice-title">INVOICE</div>
                        <div class="detail-line">
                            <span class="detail-label">Status:</span>
                            <span class="status-badge status-{{ $record->status }}">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </div>
                    </td>
                    <td class="header-center">
                        <div class="company-logo">
                            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo_amantrabali-light-02.png'))) }}" alt="Company Logo" style="height: 55px; width: auto;">
                            <p>Crafting Digital Excellence</p>
                        </div>
                    </td>
                    <td class="header-right">
                        <div class="date-box">
                            <div class="date-line">
                                {{ $record->invoice_date->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="invoice-number">#{{ $record->invoice_number }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content Section -->
        <div class="content-section">            
            <table class="content-table">
                <tr>
                    <td class="content-left">
                        <div class="info-box">
                            <div class="section-title">Bill To:</div>
                            <div class="client-name">{{ $record->client->name }}</div>
                            @if($record->client->company_name)
                                <div class="client-company">{{ $record->client->company_name }}</div>
                            @endif
                            <div class="client-info">
                                {{ $record->client->email }}
                                @if($record->client->phone)
                                    <br><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $record->client->phone) }}" class="whatsapp-link">{{ $record->client->phone }}</a>
                                @endif
                                <!-- @if($record->client->address)
                                    <br>{!! $record->client->address !!}
                                    @if($record->client->city || $record->client->state || $record->client->postal_code)
                                        <br>{{ $record->client->city }}@if($record->client->city && ($record->client->state || $record->client->postal_code)), @endif{{ $record->client->state }} {{ $record->client->postal_code }}
                                    @endif
                                    @if($record->client->country)
                                        <br>{{ $record->client->country }}
                                    @endif
                                @endif -->
                            </div>
                            
                            @if($record->project)
                            <!-- <div class="detail-line" style="margin-top: 8px;">
                                <span class="detail-label">Project:</span>
                                <span class="detail-value">{{ $record->project->name }}</span>
                            </div> -->
                            @endif
                        </div>
                    </td>
                    <td class="content-right">
                        <div class="info-box">
                            <div class="section-title">From:</div>
                            <div class="client-name">{{ \App\Models\InvoiceSettings::getValue('company_name', config('app.name', 'Your Company')) }}</div>
                            <div class="client-info">
                                {{ \App\Models\InvoiceSettings::getValue('company_email', 'admin@company.com') }}
                                @if(\App\Models\InvoiceSettings::getValue('company_phone'))
                                    <br><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', \App\Models\InvoiceSettings::getValue('company_phone')) }}" class="whatsapp-link">{{ \App\Models\InvoiceSettings::getValue('company_phone') }}</a>
                                @endif
                                @if(\App\Models\InvoiceSettings::getValue('company_website'))
                                    <br>{{ \App\Models\InvoiceSettings::getValue('company_website') }}
                                @endif
                                <!-- @if(\App\Models\InvoiceSettings::getValue('company_address'))
                                    <br>{!! \App\Models\InvoiceSettings::getValue('company_address') !!}
                                @endif -->
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items Section -->
        <div class="items-section">
            <div class="items-title">Invoice Items</div>
            
            @if($record->items->count() > 0)
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-desc">Description</th>
                        <th class="col-qty text-center">Qty</th>
                        <th class="col-unit text-center">Unit</th>
                        <th class="col-price text-right">Rate</th>
                        <th class="col-total text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->name }}</div>
                            @if($item->description)
                            <div class="item-description">{{ $item->description }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="qty-badge">{{ number_format($item->quantity, 0) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="unit-badge">{{ $item->unit }}</span>
                        </td>
                        <td class="text-right">{{ $record->currency }} {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right"><strong>{{ $record->currency }} {{ number_format($item->total_price, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-items">
                No items found for this invoice.
            </div>
            @endif
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="totals-spacer"></td>
                    <td class="totals-content">
                        <div class="summary-box">
                            <div class="summary-header">
                                <h3 class="summary-title">Invoice Summary</h3>
                            </div>
                            <div class="summary-body">
                                <table class="total-table">
                                    <tr>
                                        <td class="total-label">Subtotal</td>
                                        <td class="total-amount">{{ $record->currency }} {{ number_format($record->subtotal, 2) }}</td>
                                    </tr>
                                </table>
                                
                                @if($record->discount_amount > 0)
                                <table class="total-table">
                                    <tr>
                                        <td class="total-label">Discount</td>
                                        <td class="total-amount">-{{ $record->currency }} {{ number_format($record->discount_amount, 2) }}</td>
                                    </tr>
                                </table>
                                @endif
                                
                                @if($record->tax_amount > 0)
                                <table class="total-table">
                                    <tr>
                                        <td class="total-label">Tax</td>
                                        <td class="total-amount">{{ $record->currency }} {{ number_format($record->tax_amount, 2) }}</td>
                                    </tr>
                                </table>
                                @endif
                                
                                <div class="grand-total">
                                    <table class="grand-total-table">
                                        <tr>
                                            <td class="total-label">Total Amount</td>
                                            <td class="total-amount">{{ $record->currency }} {{ number_format($record->total_amount, 2) }}</td>
                                        </tr>
                                    </table>
                                </div>
                                
                                @if($record->paid_amount > 0)
                                <table class="total-table" style="margin-top: 6px;">
                                    <tr>
                                        <td class="total-label">Paid</td>
                                        <td class="total-amount">{{ $record->currency }} {{ number_format($record->paid_amount, 2) }}</td>
                                    </tr>
                                </table>
                                @endif
                                
                                @if($record->balance_due > 0)
                                <!-- <table class="total-table">
                                    <tr>
                                        <td class="total-label">Balance Due</td>
                                        <td class="total-amount balance-due">{{ $record->currency }} {{ number_format($record->balance_due, 2) }}</td>
                                    </tr>
                                </table> -->
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payment Information Section -->
        @php
            $bankAccountsJson = \App\Models\InvoiceSettings::getValue('bank_accounts');
            $bankAccounts = $bankAccountsJson ? json_decode($bankAccountsJson, true) : [];
        @endphp
        
        @if(!empty($bankAccounts))
        <div class="payment-section">
            <div class="payment-title-section">
                <div class="payment-main-title">Payment Information</div>
                <div class="payment-subtitle">Please transfer to one of the following accounts:</div>
            </div>
            
            <table class="payment-table">
                @foreach($bankAccounts as $index => $bank)
                    @if($index % 2 == 0)
                    <tr>
                    @endif
                        <td class="{{ $index % 2 == 0 ? 'payment-left' : 'payment-right' }}">
                            <div class="bank-info">
                                <div class="bank-title">{{ $bank['bank_name'] ?? '' }}</div>
                                <div class="bank-detail">
                                    No. Rek: {{ $bank['account_number'] ?? '' }}<br>
                                    A/N: {{ $bank['account_holder'] ?? '' }}
                                </div>
                            </div>
                        </td>
                    @if($index % 2 == 1 || $index == count($bankAccounts) - 1)
                        @if($index % 2 == 0 && $index == count($bankAccounts) - 1)
                            <td class="payment-right"></td>
                        @endif
                    </tr>
                    @endif
                @endforeach
            </table>
        </div>
        @endif

        <!-- Notes Section -->
        @if($record->notes || $record->terms_conditions)
        <div class="notes-section">
            <div class="note-title">Terms & Conditions</div>
            <table class="notes-table">
                <!-- <tr>
                    @if($record->notes)
                    <td class="notes-left">
                        <div class="note-box">
                            <div class="note-title">Notes</div>
                            <div class="note-content">{{ $record->notes }}</div>
                        </div>
                    </td>
                    @endif
                </tr> -->
                <tr> 
                    @if($record->terms_conditions)
                    <td class="notes-right">
                        <div class="note-box">
                            <div class="note-content">{!! $record->terms_conditions !!}</div>
                        </div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">{!! \App\Models\InvoiceSettings::getValue('invoice_footer_text', 'Thank you for your business!') !!}</div>
            <div class="generated">Generated on {{ now()->format('M d, Y \a\t g:i A') }}</div>
        </div>
    </div>
</body>
</html>