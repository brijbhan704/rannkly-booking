<?php

use Illuminate\Support\Facades\Route;

// This will match all routes except those starting with 'api'
Route::get('{any}', function () {
    return view('app');
})->where('any', '.*');
