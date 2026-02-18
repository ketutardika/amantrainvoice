<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\ViewInvoice;

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
    return redirect('/admin/login');
});

// PDF Routes (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/view', ViewInvoice::class)->name('invoices.view');
});

// Public invoice URL — accessible by clients without authentication
// URL: /invoices/{company-slug}/{invoice-number}/pdf
Route::get('/invoices/{tenant}/{invoiceNumber}/pdf', [PublicInvoiceController::class, 'show'])
    ->name('invoices.public.pdf');
