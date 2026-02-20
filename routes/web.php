<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExportController;
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
    return view('welcome');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/docs', function () {
    return view('docs');
})->name('docs');

// PDF Routes (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/view', ViewInvoice::class)->name('invoices.view');
    Route::get('/export/{model}', [ExportController::class, 'export'])->name('export.data');
});

// Public invoice URL — accessible by clients without authentication.
// Uses a UUID public_token (opaque, no invoice number exposed) and a
// cryptographic signature so the URL cannot be forged or enumerated.
// URL: /invoices/{company-slug}/{uuid}/pdf?signature=...
Route::get('/invoices/{tenant}/{publicToken}/pdf', [PublicInvoiceController::class, 'show'])
    ->name('invoices.public.pdf')
    ->middleware('signed');
