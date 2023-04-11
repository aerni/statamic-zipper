<?php

use Aerni\Zipper\Http\Controllers\ZipperController;
use Illuminate\Support\Facades\Route;

Route::get('/create/{id}', [ZipperController::class, 'create'])->name('zipper.create');
