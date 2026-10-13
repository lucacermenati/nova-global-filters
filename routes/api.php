<?php

use Illuminate\Support\Facades\Route;
use LucaCermenati\NovaGlobalFilters\Http\Controllers\GlobalFilterController;

Route::get('/filters/{filter}', [GlobalFilterController::class, 'show'])->name('nova-global-filters.show');
Route::post('/filters/{filter}', [GlobalFilterController::class, 'update'])->name('nova-global-filters.update');
Route::delete('/filters/{filter}', [GlobalFilterController::class, 'destroy'])->name('nova-global-filters.destroy');
