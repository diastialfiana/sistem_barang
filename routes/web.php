<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AuthController;

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

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // List Export Routes (placed before resource to avoid collision)
    Route::get('/requests/export-pdf-list', [RequestController::class, 'exportPdfList'])->name('requests.export_pdf_list');
    Route::get('/requests/export-excel-list', [RequestController::class, 'exportExcelList'])->name('requests.export_excel_list');
    Route::get('/requests/export-recap', [RequestController::class, 'exportRecap'])->name('requests.export_recap');

    Route::resource('requests', RequestController::class);
    
    // Approval Actions
    Route::post('requests/{request}/submit', [RequestController::class, 'submit'])->name('requests.submit');
    Route::post('requests/{request}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    Route::post('requests/{request}/reject', [RequestController::class, 'reject'])->name('requests.reject');
    Route::get('requests/{request}/export', [RequestController::class, 'export'])->name('requests.export');
});

Route::middleware(['auth', 'role:super_admin|admin_1|admin_2'])->group(function () {
    Route::resource('users', \App\Http\Controllers\UserManagementController::class);
    // Report Routes
    Route::get('reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export/pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Item Export Routes
    Route::get('items/export/pdf', [\App\Http\Controllers\ItemController::class, 'exportPdf'])->name('items.export.pdf');
    Route::get('items/export/excel', [\App\Http\Controllers\ItemController::class, 'exportExcel'])->name('items.export.excel');
    
    // Item Import Routes
    Route::post('items/import/preview', [\App\Http\Controllers\ItemController::class, 'previewImport'])->name('items.import.preview');
    Route::post('items/import/process', [\App\Http\Controllers\ItemController::class, 'processImport'])->name('items.import.process');
    Route::get('items/import/template', [\App\Http\Controllers\ItemController::class, 'downloadTemplate'])->name('items.import.template');
    
    Route::resource('items', \App\Http\Controllers\ItemController::class);
});

