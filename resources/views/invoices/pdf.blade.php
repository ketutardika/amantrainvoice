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
            border-bottom: 2px solid #6366f1;
            padding-bottom: 15px;
        }
        
        .header-table {
            width: 100%;
            table-layout: fixed;
        }
        
        .header-left {
            width: 50%;
            vertical-align: top;
        }
        
        .header-right {
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #111;
            margin-bottom: 3px;
        }
        
        .company-tagline {
            font-size: 9px;
            color: #6366f1;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        
        .company-info {
            font-size: 9px;
            color: #666;
            line-height: 1.2;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #111;
            margin-bottom: 4px;
        }
        
        .invoice-number {
            font-size: 12px;
            color: #6366f1;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .date-box {
            padding: 8px;
            font-size: 9px;
            margin-top: 5px;
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
            margin-bottom: 20px;
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
        }
        
        .info-box {
            padding: 0px;
            margin-bottom: 10px;
        }
        
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
            padding-bottom: 2px;
            margin-bottom: 8px;
        }
        
        .client-name {
            font-size: 14px;
            font-weight: bold;
            color: #111;
            margin-bottom: 3px;
        }
        
        .client-company {
            font-size: 11px;
            color: #6366f1;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .client-info {
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }
        
        .detail-line {
            margin-bottom: 4px;
            font-size: 9px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #374151;
            display: inline-block;
            width: 50px;
        }
        
        .detail-value {
            color: #111;
            font-weight: bold;
        }
        
        .status-badge {
            background: #f3f4f6;
            color: #374151;
            padding: 1px 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid #d1d5db;
        }
        
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #d1fae5; color: #065f46; }
        
        /* Items Section */
        .items-section {
            margin-bottom: 15px;
        }
        
        .items-title {
            font-size: 12px;
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
            font-size: 9px;
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
            font-size: 10px;
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
            font-size: 10px;
            font-weight: bold;
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
            font-size: 9px;
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
            font-size: 9px;
            padding: 0;
        }
        
        .balance-due {
            
        }
        
        /* Notes Section */
        .notes-section {
            margin-top: 20px;
        }
        
        .notes-table {
            width: 100%;
            table-layout: fixed;
        }
        
        .notes-left {
            width: 48%;
            vertical-align: top;
            padding-right: 1%;
        }
        
        .notes-right {
            width: 48%;
            vertical-align: top;
            padding-left: 1%;
        }
        
        .note-box {
            background: #f8fafc;
            border-left: 2px solid #6366f1;
            padding: 10px;
            margin-bottom: 8px;
        }
        
        .note-title {
            font-size: 10px;
            font-weight: bold;
            color: #111;
            margin-bottom: 4px;
        }
        
        .note-content {
            font-size: 8px;
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
            font-size: 14px;
            font-weight: bold;
            color: #111;
            margin-bottom: 6px;
        }
        
        .generated {
            font-size: 8px;
            color: #999;
            background: #f9fafb;
            padding: 3px 6px;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <div class="company-name">{{ config('app.name', 'Your Company') }}</div>
                        <div class="company-tagline">Invoice Management System</div>
                        <div class="company-info">
                            {{ $record->user->name ?? 'System Administrator' }}<br>
                            {{ $record->user->email ?? 'admin@company.com' }}
                        </div>
                    </td>
                    <td class="header-right">
                        <div class="invoice-title">INVOICE</div>
                        <div class="invoice-number"># {{ $record->invoice_number }}</div>
                        <div class="date-box">
                            <div class="date-line">
                                <strong>Date:</strong> {{ $record->invoice_date->format('M d, Y') }}
                            </div>
                            <div class="date-line">
                                <strong>Due:</strong> {{ $record->due_date->format('M d, Y') }}
                            </div>
                        </div>
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
                            <div class="section-title">Bill To</div>
                            <div class="client-name">{{ $record->client->name }}</div>
                            @if($record->client->company_name)
                                <div class="client-company">{{ $record->client->company_name }}</div>
                            @endif
                            <div class="client-info">
                                {{ $record->client->email }}
                                @if($record->client->phone)
                                    <br>{{ $record->client->phone }}
                                @endif
                                @if($record->client->address)
                                    <br><br>{{ $record->client->address }}
                                    @if($record->client->city || $record->client->state || $record->client->postal_code)
                                        <br>{{ $record->client->city }}@if($record->client->city && ($record->client->state || $record->client->postal_code)), @endif{{ $record->client->state }} {{ $record->client->postal_code }}
                                    @endif
                                    @if($record->client->country)
                                        <br>{{ $record->client->country }}
                                    @endif
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="content-right">
                        <div class="info-box">
                            <div class="section-title">Invoice Details</div>
                            
                            @if($record->project)
                            <div class="detail-line">
                                <span class="detail-label">Project:</span>
                                <span class="detail-value">{{ $record->project->name }}</span>
                            </div>
                            @endif
                            
                            <div class="detail-line">
                                <span class="detail-label">Status:</span>
                                <span class="status-badge status-{{ $record->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                </span>
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
                        <th class="col-price text-right">Price</th>
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

        <!-- Notes Section -->
        @if($record->notes || $record->terms_conditions)
        <div class="notes-section">
            <table class="notes-table">
                <tr>
                    @if($record->notes)
                    <td class="notes-left">
                        <div class="note-box">
                            <div class="note-title">Notes</div>
                            <div class="note-content">{{ $record->notes }}</div>
                        </div>
                    </td>
                    @endif
                    
                    @if($record->terms_conditions)
                    <td class="notes-right">
                        <div class="note-box">
                            <div class="note-title">Terms & Conditions</div>
                            <div class="note-content">{{ $record->terms_conditions }}</div>
                        </div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Thank you for your business!</div>
            <div class="generated">Generated on {{ now()->format('M d, Y \a\t g:i A') }}</div>
        </div>
    </div>
</body>
</html>