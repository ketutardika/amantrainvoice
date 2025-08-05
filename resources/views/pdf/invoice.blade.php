<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            font-size: 14px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .invoice-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        
        .invoice-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .invoice-header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .invoice-info > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 15px;
        }
        
        .info-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2em;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        
        .info-section p {
            margin-bottom: 8px;
            color: #666;
        }
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .services-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .services-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .services-table tr:nth-child(even) {
            background: #f8fafe;
        }
        
        .services-table tr:hover {
            background: #f1f4f9;
        }
        
        .total-section {
            text-align: right;
            margin-top: 30px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .total-row > div {
            display: table-cell;
            padding: 10px 0;
        }
        
        .total-row .label {
            text-align: right;
            padding-right: 20px;
        }
        
        .total-row .amount {
            text-align: right;
            font-weight: bold;
            color: #667eea;
        }
        
        .total-row.final {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            font-size: 1.3em;
            font-weight: bold;
            margin-top: 15px;
            border-radius: 8px;
        }
        
        .total-row.final .label,
        .total-row.final .amount {
            color: white;
        }
        
        .payment-info {
            background: #f8fafe;
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .payment-info h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .bank-details {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        
        .bank-card {
            display: table-cell;
            width: 50%;
            background: white;
            padding: 15px;
            margin-right: 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .terms {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            color: #666;
            font-size: 12px;
        }
        
        .terms h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .terms ul {
            margin-left: 20px;
            margin-top: 10px;
        }
        
        .terms li {
            margin-bottom: 5px;
        }
        
        .price {
            font-weight: bold;
            color: #667eea;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-0 {
            margin-bottom: 0;
        }
        
        .mb-3 {
            margin-bottom: 15px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #667eea;
            text-align: center;
            color: #667eea;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft { background: #6c757d; color: white; }
        .status-sent { background: #0d6efd; color: white; }
        .status-viewed { background: #0dcaf0; color: white; }
        .status-partial_paid { background: #fd7e14; color: white; }
        .status-paid { background: #198754; color: white; }
        .status-overdue { background: #dc3545; color: white; }
        .status-cancelled { background: #343a40; color: white; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <h1>INVOICE</h1>
            <p>Professional Invoice System</p>
            @if($invoice->status !== 'draft')
                <div style="margin-top: 15px;">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                    </span>
                </div>
            @endif
        </div>
        
        <div class="invoice-info">
            <div>
                <div class="info-section">
                    <h3>From:</h3>
                    <p><strong>{{ config('app.name', 'Invoice System') }}</strong></p>
                    <p>{{ $invoice->user->name }}</p>
                    <p>{{ $invoice->user->address ?? 'Your Company Address' }}</p>
                    <p>{{ $invoice->user->phone ?? 'Your Phone Number' }}</p>
                    <p>Email: {{ $invoice->user->email }}</p>
                    @if(config('app.website'))
                        <p>Website: {{ config('app.website') }}</p>
                    @endif
                </div>
            </div>
            
            <div>
                <div class="info-section">
                    <h3>To:</h3>
                    <p><strong>{{ $invoice->client->full_name }}</strong></p>
                    @if($invoice->client->company_name)
                        <p>{{ $invoice->client->company_name }}</p>
                    @endif
                    <p>{{ $invoice->client->formatted_address }}</p>
                    @if($invoice->client->phone)
                        <p>{{ $invoice->client->phone }}</p>
                    @endif
                    <p>Email: {{ $invoice->client->email }}</p>
                    @if($invoice->client->tax_number)
                        <p>Tax ID: {{ $invoice->client->tax_number }}</p>
                    @endif
                    
                    <br>
                    <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
                    <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
                    @if($invoice->is_overdue)
                        <p style="color: #dc3545;"><strong>OVERDUE:</strong> {{ $invoice->days_overdue }} days</p>
                    @endif
                </div>
            </div>
        </div>
        
        <table class="services-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Unit</th>
                    <th class="text-right">Rate ({{ $invoice->currency }})</th>
                    <th class="text-right">Total ({{ $invoice->currency }})</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->name }}</strong>
                            @if($item->description)
                                <br><small style="color: #666;">{{ $item->description }}</small>
                            @endif
                            @if($item->item_type !== 'service')
                                <br><small style="background: #e9ecef; padding: 2px 6px; border-radius: 4px; font-size: 10px;">{{ ucfirst($item->item_type) }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ number_format($item->quantity, $item->quantity == intval($item->quantity) ? 0 : 2) }}</td>
                        <td class="text-center">{{ $item->unit }}</td>
                        <td class="text-right price">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-right price">{{ number_format($item->total_price, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <div class="label">Subtotal:</div>
                <div class="amount">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 0, ',', '.') }}</div>
            </div>
            
            @if($invoice->discount_amount > 0)
                <div class="total-row">
                    <div class="label">Discount:</div>
                    <div class="amount" style="color: #198754;">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 0, ',', '.') }}</div>
                </div>
            @endif
            
            @if($invoice->tax_amount > 0)
                <div class="total-row">
                    <div class="label">Tax:</div>
                    <div class="amount">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 0, ',', '.') }}</div>
                </div>
            @endif
            
            <div class="total-row final">
                <div class="label">TOTAL:</div>
                <div class="amount">{{ $invoice->currency }} {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
            
            @if($invoice->paid_amount > 0)
                <div class="total-row" style="margin-top: 15px;">
                    <div class="label">Paid Amount:</div>
                    <div class="amount" style="color: #198754;">{{ $invoice->currency }} {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
                </div>
                <div class="total-row">
                    <div class="label">Balance Due:</div>
                    <div class="amount" style="color: #dc3545; font-size: 1.2em;">{{ $invoice->currency }} {{ number_format($invoice->balance_due, 0, ',', '.') }}</div>
                </div>
            @endif
        </div>
        
        @if($invoice->notes)
            <div class="payment-info">
                <h3>Notes</h3>
                <p>{{ $invoice->notes }}</p>
            </div>
        @endif
        
        <!-- Payment Information -->
        <div class="payment-info">
            <h3>💳 Payment Information</h3>
            <p>Please make payment to one of the following accounts:</p>
            
            <div class="bank-details">
                <div class="bank-card">
                    <h4>🏦 Bank BCA</h4>
                    <p><strong>Account No:</strong> 1234-5678-9012</p>
                    <p><strong>Account Name:</strong> {{ config('app.name') }}</p>
                </div>
                
                <div class="bank-card">
                    <h4>🏦 Bank Mandiri</h4>
                    <p><strong>Account No:</strong> 0987-6543-2109</p>
                    <p><strong>Account Name:</strong> {{ config('app.name') }}</p>
                </div>
            </div>
            
            <p style="margin-top: 15px;"><strong>Or via E-wallet:</strong></p>
            <p>• GoPay: +62 812-3456-7890<br>
            • OVO: +62 812-3456-7890</p>
        </div>
        
        @if($invoice->terms_conditions)
            <div class="terms">
                <h4>📋 Terms & Conditions:</h4>
                <p>{{ $invoice->terms_conditions }}</p>
            </div>
        @else
            <div class="terms">
                <h4>📋 Terms & Conditions:</h4>
                <ul>
                    <li>Payment is due within {{ $invoice->client->payment_terms ?? 14 }} days of invoice date</li>
                    <li>Late payments may incur additional charges</li>
                    <li>All disputes must be reported within 7 days of invoice receipt</li>
                    <li>Services rendered are subject to our standard terms of service</li>
                </ul>
            </div>
        @endif
        
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p style="margin-top: 10px; font-size: 12px; color: #666;">
                Generated on {{ now()->format('d M Y H:i') }} • Invoice {{ $invoice->invoice_number }}
            </p>
            @if($invoice->pdf_generated_at)
                <p style="font-size: 10px; color: #999;">
                    PDF created: {{ $invoice->pdf_generated_at->format('d M Y H:i') }}
                </p>
            @endif
        </div>
    </div>
</body>
</html>