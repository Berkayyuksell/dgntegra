<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\test;

Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index.home');

Route::get('/test',[test::class,'index']);

Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/data', [InvoiceController::class, 'get_table_data'])->name('invoices.data');


Route::get('/invoices/in',[InvoiceController::class,'indexIn'])->name('invoices.index_in');
Route::get('/invoices/in/data',[InvoiceController::class,'get_table_data_in'])->name('invoices.index_in.data');


Route::get('/invoices/archive',[InvoiceController::class,'indexArchive'])->name('invoices.archive');
Route::get('invoices/archive/data',[InvoiceController::class,'get_table_data_archive'])->name('invoices.archive.data');


Route::get('invoices/sync',[InvoiceController::class,'testtriggerSync'])->name('invoices.sync');

// Fatura HTML görüntüleme
Route::get('/invoice/html/{uuid}', [InvoiceController::class, 'showHtml'])->name('invoice.html');

