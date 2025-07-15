<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViewerController;

Route::get('/', [ViewerController::class, 'form'])->name('form');
Route::post('/validate', [ViewerController::class, 'validate'])->name('validate');
Route::post('/viewer', [ViewerController::class, 'viewer'])->name('viewer');
Route::get('/viewer', function () {
  return redirect()->route('form');
})->name('viewer.redirect');
