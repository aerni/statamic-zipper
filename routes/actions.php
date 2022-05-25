<?php

use Aerni\Zipper\ZipperController;
use Illuminate\Support\Facades\Route;

Route::get('/create', [ZipperController::class, 'create'])->name('zipper.create');
