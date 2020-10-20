<?php

use Aerni\Zipper\ZipperController;
use Illuminate\Support\Facades\Route;

Route::get(config('zipper.route'), [ZipperController::class, 'create'])->name('zipper');
