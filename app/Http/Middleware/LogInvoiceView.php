<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogInvoiceView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        if ($request->route('invoice') && $request->has('view')) {
            $invoice = $request->route('invoice');
            
            // Log the view
            \Log::info('Invoice viewed', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'viewed_at' => now(),
            ]);
        }
        
        return $response;
    }
}