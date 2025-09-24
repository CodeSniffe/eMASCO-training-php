<?php

use Illuminate\Support\Facades\Route;


Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel API!']);
});