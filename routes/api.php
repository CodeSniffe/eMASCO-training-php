<?php

use Illuminate\Support\Facades\Route;


Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel API!']);
});

use App\Http\Controllers\BookController;
Route::get('/books', [BookController::class, 'listBooks']);         // List all books
Route::get('/books/{id}', [BookController::class, 'getBookById']);  // Get a book by ID
Route::post('/books', [BookController::class, 'addBook']);