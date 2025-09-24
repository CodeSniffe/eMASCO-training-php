<?php

use Illuminate\Support\Facades\Route;


Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel API!']);
});

use App\Http\Controllers\BookController;

Route::get('/books', [BookController::class, 'listBooks']);         // List all books
Route::get('/books/{id}', [BookController::class, 'getBookById']);  // Get a book by ID
Route::post('/books', [BookController::class, 'addBook']);

use App\Http\Controllers\UserController;

Route::post('/login', [UserController::class, 'login']);
