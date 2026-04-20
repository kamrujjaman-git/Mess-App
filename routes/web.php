<?php

use App\Http\Controllers\AdvancePaymentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseRentController;
use App\Http\Controllers\MaidBillController;
use App\Http\Controllers\MarketExpenseController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\MonthlySummaryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/monthly-summary', [MonthlySummaryController::class, 'index'])->name('monthly-summary.index');

Route::resource('users', UserController::class);
Route::resource('expenses', MarketExpenseController::class)
    ->parameters(['expenses' => 'market_expense']);
Route::get('/expenses/bulk', [MarketExpenseController::class, 'bulkForm'])->name('expenses.bulkForm');
Route::post('/expenses/bulk-store', [MarketExpenseController::class, 'bulkStore'])->name('expenses.bulkStore');
Route::delete('/expenses/bulk-delete', [MarketExpenseController::class, 'bulkDelete'])->name('expenses.bulk.delete');
Route::resource('meals', MealController::class);
// BULK ROUTES (IMPORTANT: keep AFTER resource)
Route::get('meals/bulk', [MealController::class, 'bulkForm'])->name('meals.bulk.form');
Route::get('meals/bulkForm', [MealController::class, 'bulkForm'])->name('meals.bulkForm');
Route::post('meals/bulk-store', [MealController::class, 'bulkStore'])->name('meals.bulk.store');
Route::post('meals/bulk-delete', [MealController::class, 'bulkDelete'])->name('meals.bulk.delete');
Route::resource('advance-payments', AdvancePaymentController::class);
Route::get('/advance-payments/bulk', [AdvancePaymentController::class, 'bulkForm'])->name('advance-payments.bulkForm');
Route::post('/advance-payments/bulk-store', [AdvancePaymentController::class, 'bulkStore'])->name('advance-payments.bulkStore');
Route::delete('/advance-payments/bulk-delete', [AdvancePaymentController::class, 'bulkDelete'])->name('advance-payments.bulk.delete');

Route::resource('house-rents', HouseRentController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::get('/house-rents/bulk', [HouseRentController::class, 'bulkForm'])->name('house-rents.bulkForm');
Route::post('/house-rents/bulk-store', [HouseRentController::class, 'bulkStore'])->name('house-rents.bulkStore');
Route::delete('/house-rents/bulk-delete', [HouseRentController::class, 'bulkDelete'])->name('house-rents.bulk.delete');
Route::resource('maid-bills', MaidBillController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::get('/maid-bills/bulk', [MaidBillController::class, 'bulkForm'])->name('maid-bills.bulkForm');
Route::post('/maid-bills/bulk-store', [MaidBillController::class, 'bulkStore'])->name('maid-bills.bulkStore');
Route::delete('/maid-bills/bulk-delete', [MaidBillController::class, 'bulkDelete'])->name('maid-bills.bulk.delete');
