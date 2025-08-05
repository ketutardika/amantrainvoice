<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInvoiceOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $invoice = $request->route('invoice');
        
        if ($invoice && auth()->user()->role !== 'admin') {
            if ($invoice->user_id !== auth()->id()) {
                abort(403, 'You do not have permission to access this invoice.');
            }
        }
        
        return $next($request);
    }
}