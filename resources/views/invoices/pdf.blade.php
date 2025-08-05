<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $record->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #1a1a1a;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .company-info {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }
        
        .invoice-number {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        
        .invoice-meta {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }
        
        .bill-to, .invoice-details {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 40px;
        }
        
        .invoice-details {
            padding-right: 0;
            padding-left: 40px;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #374151;
            margin-bottom: 16px;
        }
        
        .client-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }
        
        .client-info {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.4;
        }
        
        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        
        .detail-label {
            display: table-cell;
            font-weight: 500;
            color: #374151;
            width: 100px;
        }
        
        .detail-value {
            display: table-cell;
            color: #111827;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1d4ed8; }
        .status-viewed { background-color: #e0f2fe; color: #0369a1; }
        .status-partial_paid { background-color: #fef3c7; color: #92400e; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-overdue { background-color: #fee2e2; color: #dc2626; }
        .status-cancelled { background-color: #f3f4f6; color: #6b7280; }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 40px 0;
            background: white;
        }
        
        .items-table thead tr {
            border-bottom: 2px solid #e5e7eb;
        }
        
        .items-table th {
            padding: 16px 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #374151;
            background: #f9fafb;
        }
        
        .items-table td {
            padding: 20px 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #111827;
        }
        
        .items-table tr:last-child td {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .item-description {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.4;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals {
            margin-top: 40px;
            margin-left: auto;
            width: 320px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .total-label {
            display: table-cell;
            font-weight: 500;
            color: #374151;
        }
        
        .total-amount {
            display: table-cell;
            text-align: right;
            font-weight: 600;
            color: #111827;
        }
        
        .total-final {
            background: #111827;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 16px;
            font-size: 18px;
            font-weight: 700;
        }
        
        .total-final .total-label,
        .total-final .total-amount {
            color: white;
        }
        
        .notes-section {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }
        
        .notes-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
        }
        
        .notes-content {
            color: #6b7280;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 12px;
        }
        
        .thank-you {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ config('app.name', 'Your Company') }}</div>
                <div class="company-info">
                    Invoice Management System<br>
                    {{ $record->user->name ?? 'System Administrator' }}<br>
                    {{ $record->user->email ?? 'admin@company.com' }}
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number"># {{ $record->invoice_number }}</div>
            </div>
        </div>

        <!-- Invoice Meta Information -->
        <div class="invoice-meta">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="client-name">{{ $record->client->name }}</div>
                <div class="client-info">
                    @if($record->client->company_name)
                        {{ $record->client->company_name }}<br>
                    @endif
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
            
            <div class="invoice-details">
                <div class="section-title">Invoice Details</div>
                
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value">{{ $record->invoice_date->format('M d, Y') }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Due Date:</div>
                    <div class="detail-value">{{ $record->due_date->format('M d, Y') }}</div>
                </div>
                
                @if($record->project)
                <div class="detail-row">
                    <div class="detail-label">Project:</div>
                    <div class="detail-value">{{ $record->project->name }}</div>
                </div>
                @endif
                
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge status-{{ $record->status }}">
                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center" style="width: 80px;">Qty</th>
                    <th class="text-center" style="width: 60px;">Unit</th>
                    <th class="text-right" style="width: 120px;">Unit Price</th>
                    <th class="text-right" style="width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($record->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->name }}</div>
                        @if($item->description)
                        <div class="item-description">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">{{ $record->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ $record->currency }} {{ number_format($item->total_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 60px; color: #9ca3af;">
                        No items found for this invoice.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals">
            <div class="total-row">
                <div class="total-label">Subtotal</div>
                <div class="total-amount">{{ $record->currency }} {{ number_format($record->subtotal, 2) }}</div>
            </div>
            
            @if($record->discount_amount > 0)
            <div class="total-row">
                <div class="total-label">Discount</div>
                <div class="total-amount">-{{ $record->currency }} {{ number_format($record->discount_amount, 2) }}</div>
            </div>
            @endif
            
            @if($record->tax_amount > 0)
            <div class="total-row">
                <div class="total-label">Tax</div>
                <div class="total-amount">{{ $record->currency }} {{ number_format($record->tax_amount, 2) }}</div>
            </div>
            @endif
            
            <div class="total-row total-final">
                <div class="total-label">Total Amount</div>
                <div class="total-amount">{{ $record->currency }} {{ number_format($record->total_amount, 2) }}</div>
            </div>
            
            @if($record->paid_amount > 0)
            <div class="total-row" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #f3f4f6;">
                <div class="total-label">Paid Amount</div>
                <div class="total-amount">{{ $record->currency }} {{ number_format($record->paid_amount, 2) }}</div>
            </div>
            @endif
            
            @if($record->balance_due > 0)
            <div class="total-row">
                <div class="total-label">Balance Due</div>
                <div class="total-amount">{{ $record->currency }} {{ number_format($record->balance_due, 2) }}</div>
            </div>
            @endif
        </div>

        <!-- Notes Section -->
        @if($record->notes || $record->terms_conditions)
        <div class="notes-section">
            @if($record->notes)
            <div style="margin-bottom: 30px;">
                <div class="notes-title">Notes</div>
                <div class="notes-content">{{ $record->notes }}</div>
            </div>
            @endif
            
            @if($record->terms_conditions)
            <div>
                <div class="notes-title">Terms & Conditions</div>
                <div class="notes-content">{{ $record->terms_conditions }}</div>
            </div>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="thank-you">Thank you for your business!</div>
            <div>Generated on {{ now()->format('M d, Y \a\t g:i A') }}</div>
        </div>
    </div>
</body>
</html>