<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    // Mock data
    private $books = [
        [
            "id" => "12345",
            "title" => "The Great Gatsby",
            "author" => "F. Scott Fitzgerald",
            "publishedYear" => 1925
        ],
        [
            "id" => "67890",
            "title" => "1984",
            "author" => "George Orwell",
            "publishedYear" => 1949
        ]
    ];

    public function listBooks(): JsonResponse
    {
        return response()->json($this->books);
    }

    public function getBookById(string $id): JsonResponse
    {
        foreach ($this->books as $book) {
            if ($book['id'] === $id) {
                return response()->json($book);
            }
        }

        return response()->json(['message' => 'Book not found'], 404);
    }

    public function addBook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'publishedYear' => 'required|integer',
        ]);

        $newBook = [
            'id' => uniqid(),
            'title' => $validated['title'],
            'author' => $validated['author'],
            'publishedYear' => $validated['publishedYear'],
        ];

        // For demonstration: not actually saving since $books is not persistent
        $this->books[] = $newBook;

        return response()->json($newBook, 201);
    }
}