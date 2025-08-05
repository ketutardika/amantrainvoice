<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PDFController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Clients Management
    Route::resource('clients', ClientController::class);
    
    // Invoices Management
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    
    // Payments Management
    Route::resource('payments', PaymentController::class)->except(['edit', 'update', 'destroy']);
    Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('payments/{payment}/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
    
    // PDF Generation
    Route::get('invoices/{invoice}/pdf', [PDFController::class, 'generateInvoice'])->name('invoices.pdf');
    Route::get('invoices/{invoice}/pdf/preview', [PDFController::class, 'previewInvoice'])->name('invoices.pdf.preview');
    Route::get('invoices/{invoice}/pdf/download', [PDFController::class, 'downloadStoredInvoice'])->name('invoices.pdf.download');
    
    // API Routes for AJAX
    Route::prefix('api')->group(function () {
        Route::get('clients/{client}/invoices', function($client) {
            return \App\Models\Client::findOrFail($client)->invoices()->with('client')->get();
        })->name('api.clients.invoices');
        
        Route::get('invoices/{invoice}/balance', function($invoice) {
            $invoice = \App\Models\Invoice::findOrFail($invoice);
            return response()->json([
                'balance_due' => $invoice->balance_due,
                'formatted_balance' => $invoice->formatted_balance
            ]);
        })->name('api.invoices.balance');
    });
});

// Public invoice view (for clients)
Route::get('invoices/{invoice}/view/{token}', function($invoice, $token) {
    $invoice = \App\Models\Invoice::findOrFail($invoice);
    
    // Simple token validation (you might want to implement proper signed URLs)
    $expectedToken = md5($invoice->id . $invoice->invoice_number . config('app.key'));
    
    if ($token !== $expectedToken) {
        abort(404);
    }
    
    $invoice->markAsViewed();
    
    return view('invoices.public', compact('invoice'));
})->name('invoices.public');

require __DIR__.'/auth.php';