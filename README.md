# 🚀 Laravel Backend Setup Guide

Welcome! 👋
This guide walks you step-by-step to set up a basic Laravel backend with authentication books, eMASCO jobs and Swagger API docs.

> \*Please make sure you have php and composer on your machine

> \***And you have replace the keys in the .env 

## 🛠️ Project Setup

1. Install dependencies:

```bash
composer install
```

2. Once installed, run

```bash
php artisan serve
```

3. Then go to [http://127.0.0.1:8000/api/health](http://127.0.0.1:8000/api/health) in the browser
4. You should see

```json
{ "status": "ok" }
```

## 🐘 Setup Swagger UI

1. 📦 Install wotzebra/laravel-swagger-ui

```bash
composer require wotz/laravel-swagger-ui
php artisan swagger-ui:install
```

This will:

-   Publish config: config/swagger-ui.php
-   Register provider: App\Providers\SwaggerUiServiceProvider
-   Setup default route for Swagger UI at: http://localhost:8000/swagger

2. 🗂 Update the OpenAPI Spec (JSON Format)

Update the file:
**resources/swagger/openapi.json**

Use this minimal working example:

```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "Hello API",
        "version": "1.0.0"
    },
    "paths": {
        "/api/hello": {
            "get": {
                "summary": "Say Hello",
                "responses": {
                    "200": {
                        "description": "Success",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": {
                                            "type": "string"
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
```

3. 🔧 Update the Swagger Config

Edit **config/swagger-ui.php**:

```php
return [
    'path' => 'swagger',

    'versions' => [
        'v1' => resource_path('swagger/openapi.json'),
    ],

    'default_version' => 'v1',

    'middleware' => [
        \Wotz\SwaggerUi\Http\Middleware\EnsureUserIsAuthorized::class,
    ],

    // Leave this false
    'modify_file' => false,
];
```

5. 🧪 Add Example Route

Edit **routes/api.php** and add:

```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel API!']);
});
```

6. 🧹 Clear Config and Serve

```bash
php artisan config:clear
php artisan serve
```

7. 🌐 View Swagger UI

Visit: http://localhost:8000/swagger

You should now see Swagger UI with your /api/hello endpoint listed and documented.

## Setup Book API

1. Create BookController

```bash
php artisan make:controller BookController
```

1. Then, copy the code below and then paste it in **app/Http/Controllers/BookController**

```
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
```

2. This is a simple logic for Create and Read action for the book
3. Now, paste the code below to **routes/api.php**

```php
use App\Http\Controllers\BookController;
Route::get('/books', [BookController::class, 'listBooks']);         // List all books
Route::get('/books/{id}', [BookController::class, 'getBookById']);  // Get a book by ID
Route::post('/books', [BookController::class, 'addBook']);
```

4. This will setup the routes for the Book to be called lated on the Swagger UI
5. Then, paste the code below to update the Swagger UI

```json
{
    "openapi": "3.0.0",
    "info": {
        "title": "Hello API",
        "version": "1.0.0"
    },
    "paths": {
        "/api/hello": {
            "get": {
                "summary": "Say Hello",
                "responses": {
                    "200": {
                        "description": "Success",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": {
                                            "type": "string"
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books": {
            "get": {
                "summary": "List all books",
                "tags": ["Books"],
                "responses": {
                    "200": {
                        "description": "A list of books",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "array",
                                    "items": {
                                        "$ref": "#/components/schemas/Book"
                                    }
                                }
                            }
                        }
                    }
                }
            },
            "post": {
                "summary": "Add a new book",
                "tags": ["Books"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "$ref": "#/components/schemas/BookInput"
                            }
                        }
                    }
                },
                "responses": {
                    "201": {
                        "description": "Book created successfully",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "400": {
                        "description": "Validation error",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "error": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books/{id}": {
            "get": {
                "summary": "Get a book by ID",
                "tags": ["Books"],
                "parameters": [
                    {
                        "name": "id",
                        "in": "path",
                        "required": true,
                        "schema": {
                            "type": "string"
                        }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Book found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "404": {
                        "description": "Book not found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    },
    "components": {
        "schemas": {
            "Book": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "string",
                        "example": "12345"
                    },
                    "title": {
                        "type": "string",
                        "example": "The Great Gatsby"
                    },
                    "author": {
                        "type": "string",
                        "example": "F. Scott Fitzgerald"
                    },
                    "publishedYear": {
                        "type": "integer",
                        "example": 1925
                    }
                }
            },
            "BookInput": {
                "type": "object",
                "required": ["title", "author", "publishedYear"],
                "properties": {
                    "title": {
                        "type": "string",
                        "example": "To Kill a Mockingbird"
                    },
                    "author": {
                        "type": "string",
                        "example": "Harper Lee"
                    },
                    "publishedYear": {
                        "type": "integer",
                        "example": 1960
                    }
                }
            }
        }
    }
}
```

6. Now, everything should be set. Now run

```bash
php artisan serve
```

7. Go to [http://localhost:8000/swagger](http://localhost:8000/swagger)

8. You should see a new Book APIs. Give it a try

## 🔐 Authentication Setup

This guide adds JWT-based authentication to your Laravel API using a mocked user

1. 📦 Install JWT Auth Package

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

> This publishes config/jwt.php and generates a signing key in .env. 2. Create Auth Middleware for checking the token

```bash
php artisan make:middleware JwtMiddleware
```

2. Update the middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) {
                throw new UnauthorizedHttpException('jwt-auth', 'Token not provided');
            }

            $payload = JWTAuth::getPayload($token);
            $userId = $payload->get('sub');

            if (!$userId) {
                throw new UnauthorizedHttpException('jwt-auth', 'User ID not found in token');
            }

            // Create mocked user instance with hardcoded data
            $user = new User();
            $user->id = $userId;
            $user->name = 'John Doe';
            $user->email = 'john@example.com';

            // Set the authenticated user for the current request
            auth()->setUser($user);

        } catch (\Exception $e) {
            throw new UnauthorizedHttpException('jwt-auth', $e->getMessage());
        }

        return $next($request);
    }
}
```

2. 🛠 Update the User Model

In **app/Models/User.php**, implement the JWT contract:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->id = 1;
        $this->name = 'John Doe';
        $this->email = 'john@example.com';
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

3. 🔐 Update Auth Guard (JWT)

In **config/auth.php**, under guards. Replace the **_guards_** block with this:

```bash
 'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],
```

4. Create UserController

```bash
php artisan make:controller UserController
```

5. Add Login & Profile Logic to the app/Http/Controllers/UserController

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function login(): JsonResponse
    {
        $user = new User();
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
```

6. Update **route/api.php** by adding these

```php
use App\Http\Controllers\UserController;

Route::post('/login', [UserController::class, 'login']);
```

7. In the end, your **routes/api.php** should look like this

```php
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

```

8. Lastly, update your **swagger/openapi.json**

```json
{
    "openapi": "3.0.3",
    "info": {
        "title": "Combined API",
        "description": "This API combines Hello, Books, and Auth (JWT) endpoints.",
        "version": "1.0.0"
    },
    "tags": [
        {
            "name": "Hello",
            "description": "Simple greeting endpoint"
        },
        {
            "name": "Books",
            "description": "Manage books with mocked data"
        },
        {
            "name": "Auth",
            "description": "JWT Authentication and Profile"
        }
    ],
    "components": {
        "schemas": {
            "Book": {
                "type": "object",
                "properties": {
                    "id": { "type": "string", "example": "12345" },
                    "title": {
                        "type": "string",
                        "example": "The Great Gatsby"
                    },
                    "author": {
                        "type": "string",
                        "example": "F. Scott Fitzgerald"
                    },
                    "publishedYear": { "type": "integer", "example": 1925 }
                }
            },
            "BookInput": {
                "type": "object",
                "required": ["title", "author", "publishedYear"],
                "properties": {
                    "title": {
                        "type": "string",
                        "example": "To Kill a Mockingbird"
                    },
                    "author": { "type": "string", "example": "Harper Lee" },
                    "publishedYear": { "type": "integer", "example": 1960 }
                }
            }
        },
        "securitySchemes": {
            "bearerAuth": {
                "type": "http",
                "scheme": "bearer",
                "bearerFormat": "JWT"
            }
        }
    },
    "security": [
        {
            "bearerAuth": []
        }
    ],
    "paths": {
        "/api/hello": {
            "get": {
                "summary": "Say Hello",
                "tags": ["Hello"],
                "responses": {
                    "200": {
                        "description": "Success",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books": {
            "get": {
                "summary": "List all books",
                "tags": ["Books"],
                "responses": {
                    "200": {
                        "description": "A list of books",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "array",
                                    "items": {
                                        "$ref": "#/components/schemas/Book"
                                    }
                                }
                            }
                        }
                    }
                }
            },
            "post": {
                "summary": "Add a new book",
                "tags": ["Books"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "$ref": "#/components/schemas/BookInput"
                            }
                        }
                    }
                },
                "responses": {
                    "201": {
                        "description": "Book created successfully",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "400": {
                        "description": "Validation error",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "error": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books/{id}": {
            "get": {
                "summary": "Get a book by ID",
                "tags": ["Books"],
                "parameters": [
                    {
                        "name": "id",
                        "in": "path",
                        "required": true,
                        "schema": { "type": "string" }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Book found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "404": {
                        "description": "Book not found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/profile": {
            "get": {
                "summary": "Get user profile",
                "tags": ["Auth"],
                "security": [
                    {
                        "bearerAuth": []
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Returns mock user profile"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },
        "/api/login": {
            "post": {
                "summary": "Login and get JWT token",
                "tags": ["Auth"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "type": "object",
                                "required": ["email", "password"],
                                "properties": {
                                    "email": {
                                        "type": "string",
                                        "example": "john@example.com"
                                    },
                                    "password": {
                                        "type": "string",
                                        "example": "password"
                                    }
                                }
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "JWT Token",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "token": { "type": "string" },
                                        "token_type": { "type": "string" },
                                        "expires_in": { "type": "integer" }
                                    }
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Invalid credentials"
                    }
                }
            }
        }
    }
}
```

9. Now, you can try the login function. It should return a JWT token.

## eMASCO APIs

### Setup MASCO Jobs

1. Now create a **MASCO** Model

```bash
php artisan make:model Masco
```

2. Update the **app/Models/Masco.php**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masco extends Model
{
    protected $table = 'masco_base';
    protected $primaryKey = 'id';

    protected $fillable = [
        'edition',
        'code',
        'digit_group',
        'digit_1',
        'digit_2',
        'digit_3',
        'digit_4',
        'digit_6',
        'parent',
        'sort_order',
        'title_my',
        'title_alt_my',
        'desc_my',
        'title_en',
        'title_alt_en',
        'desc_en',
        'salary_basic',
        'salary_increme'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'active' => 'boolean',
        'deleted' => 'boolean',
    ];
}
```

3. Then, create MascoController

```bash
php artisan make:controller MascoController
```

4. Let's add some logic to fetch the jobs data

Update the **app/Http/Controller/MascoController.php**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Masco;
use Illuminate\Http\Request;

class MascoController extends Controller
{

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = (int) $request->query('page', 1);
        $search = $request->query('search');

        $query = Masco::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'LIKE', "%{$search}%")
                    ->orWhere('title_my', 'LIKE', "%{$search}%");
            });
        }

        $masco = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $masco->items(),
            'pagination' => [
                'total' => $masco->total(),
                'per_page' => $masco->perPage(),
                'current_page' => $masco->currentPage(),
                'last_page' => $masco->lastPage(),
            ],
        ]);
    }
}
```

5. Add the new route to routes/api.php

This way the api/masco is protected by the authentication

```php
use App\Http\Controllers\MascoController;
use App\Http\Middleware\JwtMiddleware;

Route::middleware([JwtMiddleware::class])
    ->get('/masco', [MascoController::class, 'index']);
```

6. Do update the **resource/swagger/openapi.json** too

```json
{
    "openapi": "3.0.3",
    "info": {
        "title": "Combined API",
        "description": "This API combines Hello, Books, and Auth (JWT) endpoints.",
        "version": "1.0.0"
    },
    "tags": [
        {
            "name": "Hello",
            "description": "Simple greeting endpoint"
        },
        {
            "name": "Books",
            "description": "Manage books with mocked data"
        },
        {
            "name": "Auth",
            "description": "JWT Authentication and Profile"
        },
        {
            "name": "MASCO",
            "description": "Malaysia Standard Classification of Occupations"
        }
    ],
    "components": {
        "schemas": {
            "Book": {
                "type": "object",
                "properties": {
                    "id": { "type": "string", "example": "12345" },
                    "title": {
                        "type": "string",
                        "example": "The Great Gatsby"
                    },
                    "author": {
                        "type": "string",
                        "example": "F. Scott Fitzgerald"
                    },
                    "publishedYear": { "type": "integer", "example": 1925 }
                }
            },
            "BookInput": {
                "type": "object",
                "required": ["title", "author", "publishedYear"],
                "properties": {
                    "title": {
                        "type": "string",
                        "example": "To Kill a Mockingbird"
                    },
                    "author": { "type": "string", "example": "Harper Lee" },
                    "publishedYear": { "type": "integer", "example": 1960 }
                }
            },
            "MascoItem": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer",
                        "example": 1
                    },
                    "name": {
                        "type": "string",
                        "example": "Sample Item"
                    }
                }
            },
            "MascoPaginatedResponse": {
                "type": "object",
                "properties": {
                    "data": {
                        "type": "array",
                        "items": {
                            "$ref": "#/components/schemas/MascoItem"
                        }
                    },
                    "pagination": {
                        "type": "object",
                        "properties": {
                            "total": {
                                "type": "integer",
                                "example": 25
                            },
                            "per_page": {
                                "type": "integer",
                                "example": 10
                            },
                            "current_page": {
                                "type": "integer",
                                "example": 1
                            },
                            "last_page": {
                                "type": "integer",
                                "example": 3
                            }
                        }
                    }
                }
            }
        },
        "securitySchemes": {
            "bearerAuth": {
                "type": "http",
                "scheme": "bearer",
                "bearerFormat": "JWT"
            }
        }
    },
    "security": [
        {
            "bearerAuth": []
        }
    ],
    "paths": {
        "/api/hello": {
            "get": {
                "summary": "Say Hello",
                "tags": ["Hello"],
                "responses": {
                    "200": {
                        "description": "Success",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books": {
            "get": {
                "summary": "List all books",
                "tags": ["Books"],
                "responses": {
                    "200": {
                        "description": "A list of books",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "array",
                                    "items": {
                                        "$ref": "#/components/schemas/Book"
                                    }
                                }
                            }
                        }
                    }
                }
            },
            "post": {
                "summary": "Add a new book",
                "tags": ["Books"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "$ref": "#/components/schemas/BookInput"
                            }
                        }
                    }
                },
                "responses": {
                    "201": {
                        "description": "Book created successfully",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "400": {
                        "description": "Validation error",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "error": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books/{id}": {
            "get": {
                "summary": "Get a book by ID",
                "tags": ["Books"],
                "parameters": [
                    {
                        "name": "id",
                        "in": "path",
                        "required": true,
                        "schema": { "type": "string" }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Book found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "404": {
                        "description": "Book not found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/login": {
            "post": {
                "summary": "Login and get JWT token",
                "tags": ["Auth"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "type": "object",
                                "required": ["email", "password"],
                                "properties": {
                                    "email": {
                                        "type": "string",
                                        "example": "john@example.com"
                                    },
                                    "password": {
                                        "type": "string",
                                        "example": "password"
                                    }
                                }
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "JWT Token",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "token": { "type": "string" },
                                        "token_type": { "type": "string" },
                                        "expires_in": { "type": "integer" }
                                    }
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Invalid credentials"
                    }
                }
            }
        },
        "/api/masco": {
            "get": {
                "summary": "Get paginated Masco data",
                "tags": ["MASCO"],
                "security": [
                    {
                        "bearerAuth": []
                    }
                ],
                "parameters": [
                    {
                        "name": "page",
                        "in": "query",
                        "description": "Page number",
                        "required": false,
                        "schema": {
                            "type": "integer",
                            "example": 1
                        }
                    },
                    {
                        "name": "per_page",
                        "in": "query",
                        "description": "Number of items per page",
                        "required": false,
                        "schema": {
                            "type": "integer",
                            "example": 10
                        }
                    },
                    {
                        "name": "search",
                        "in": "query",
                        "description": "Search term to filter by title_en or title_my",
                        "required": false,
                        "schema": {
                            "type": "string",
                            "example": "keyword"
                        }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Paginated list of Masco records",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/MascoPaginatedResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        }
    }
}
```

### Setup MASCO STEM Jobs

1. Let's create a new controller file for STEM jobs for easier management

```bash
php artisan make:controller CategoryController
```

2. Then, update the **app/Http/Controller/CategoryController.js**

This is the logic for getting the category of the MASCO jobs based on the selection.

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request, string $type)
    {
        // map URL type -> boolean flag column in masco_base
        $map = [
            'tvet'  => 'cat_tvet',
            'stem'  => 'cat_stem',
            'green' => 'cat_green',
            'gig'   => 'cat_gig',
        ];

        abort_unless(isset($map[$type]), 404);

        $flagColumn = $map[$type];

        // Optional: simple search by title/code
        $q = trim((string) $request->query('q', ''));

        $items = DB::table('masco_base')
            ->select(['code', 'title_en', 'title_my'])
            ->where($flagColumn, 1)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('code', 'like', "%{$q}%")
                        ->orWhere('title_en', 'like', "%{$q}%")
                        ->orWhere('title_my', 'like', "%{$q}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('code')
            ->paginate(5)
            ->withQueryString();

        return response()->json(
            [
                'type'  => $type,
                'items' => $items,
                'q'     => $q,
            ]
        );
    }
}
```

3. Lastly, update your resource/swagger/openapi.json

```json
{
    "openapi": "3.0.3",
    "info": {
        "title": "Combined API",
        "description": "This API combines Hello, Books, and Auth (JWT) endpoints.",
        "version": "1.0.0"
    },
    "tags": [
        {
            "name": "Hello",
            "description": "Simple greeting endpoint"
        },
        {
            "name": "Books",
            "description": "Manage books with mocked data"
        },
        {
            "name": "Auth",
            "description": "JWT Authentication and Profile"
        },
        {
            "name": "MASCO",
            "description": "Malaysia Standard Classification of Occupations"
        }
    ],
    "components": {
        "schemas": {
            "Book": {
                "type": "object",
                "properties": {
                    "id": { "type": "string", "example": "12345" },
                    "title": {
                        "type": "string",
                        "example": "The Great Gatsby"
                    },
                    "author": {
                        "type": "string",
                        "example": "F. Scott Fitzgerald"
                    },
                    "publishedYear": { "type": "integer", "example": 1925 }
                }
            },
            "BookInput": {
                "type": "object",
                "required": ["title", "author", "publishedYear"],
                "properties": {
                    "title": {
                        "type": "string",
                        "example": "To Kill a Mockingbird"
                    },
                    "author": { "type": "string", "example": "Harper Lee" },
                    "publishedYear": { "type": "integer", "example": 1960 }
                }
            },
            "MascoItem": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer",
                        "example": 1
                    },
                    "name": {
                        "type": "string",
                        "example": "Sample Item"
                    }
                }
            },
            "MascoPaginatedResponse": {
                "type": "object",
                "properties": {
                    "data": {
                        "type": "array",
                        "items": {
                            "$ref": "#/components/schemas/MascoItem"
                        }
                    },
                    "pagination": {
                        "type": "object",
                        "properties": {
                            "total": {
                                "type": "integer",
                                "example": 25
                            },
                            "per_page": {
                                "type": "integer",
                                "example": 10
                            },
                            "current_page": {
                                "type": "integer",
                                "example": 1
                            },
                            "last_page": {
                                "type": "integer",
                                "example": 3
                            }
                        }
                    }
                }
            }
        },
        "securitySchemes": {
            "bearerAuth": {
                "type": "http",
                "scheme": "bearer",
                "bearerFormat": "JWT"
            }
        }
    },
    "security": [
        {
            "bearerAuth": []
        }
    ],
    "paths": {
        "/api/hello": {
            "get": {
                "summary": "Say Hello",
                "tags": ["Hello"],
                "responses": {
                    "200": {
                        "description": "Success",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books": {
            "get": {
                "summary": "List all books",
                "tags": ["Books"],
                "responses": {
                    "200": {
                        "description": "A list of books",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "array",
                                    "items": {
                                        "$ref": "#/components/schemas/Book"
                                    }
                                }
                            }
                        }
                    }
                }
            },
            "post": {
                "summary": "Add a new book",
                "tags": ["Books"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "$ref": "#/components/schemas/BookInput"
                            }
                        }
                    }
                },
                "responses": {
                    "201": {
                        "description": "Book created successfully",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "400": {
                        "description": "Validation error",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "error": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/books/{id}": {
            "get": {
                "summary": "Get a book by ID",
                "tags": ["Books"],
                "parameters": [
                    {
                        "name": "id",
                        "in": "path",
                        "required": true,
                        "schema": { "type": "string" }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Book found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/Book"
                                }
                            }
                        }
                    },
                    "404": {
                        "description": "Book not found",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "message": { "type": "string" }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        "/api/login": {
            "post": {
                "summary": "Login and get JWT token",
                "tags": ["Auth"],
                "requestBody": {
                    "required": true,
                    "content": {
                        "application/json": {
                            "schema": {
                                "type": "object",
                                "required": ["email", "password"],
                                "properties": {
                                    "email": {
                                        "type": "string",
                                        "example": "john@example.com"
                                    },
                                    "password": {
                                        "type": "string",
                                        "example": "password"
                                    }
                                }
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "JWT Token",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "token": { "type": "string" },
                                        "token_type": { "type": "string" },
                                        "expires_in": { "type": "integer" }
                                    }
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Invalid credentials"
                    }
                }
            }
        },
        "/api/masco": {
            "get": {
                "summary": "Get paginated Masco data",
                "tags": ["MASCO"],
                "security": [
                    {
                        "bearerAuth": []
                    }
                ],
                "parameters": [
                    {
                        "name": "page",
                        "in": "query",
                        "description": "Page number",
                        "required": false,
                        "schema": {
                            "type": "integer",
                            "example": 1
                        }
                    },
                    {
                        "name": "per_page",
                        "in": "query",
                        "description": "Number of items per page",
                        "required": false,
                        "schema": {
                            "type": "integer",
                            "example": 10
                        }
                    },
                    {
                        "name": "search",
                        "in": "query",
                        "description": "Search term to filter by title_en or title_my",
                        "required": false,
                        "schema": {
                            "type": "string",
                            "example": "keyword"
                        }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Paginated list of Masco records",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "$ref": "#/components/schemas/MascoPaginatedResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },

        "/category/{type}": {
            "get": {
                "summary": "Show category items filtered by type",
                "tags": ["Category"],
                "parameters": [
                    {
                        "name": "type",
                        "in": "path",
                        "required": true,
                        "description": "Category type (one of: tvet, stem, green, gig)",
                        "schema": {
                            "type": "string",
                            "enum": ["tvet", "stem", "green", "gig"]
                        }
                    },
                    {
                        "name": "q",
                        "in": "query",
                        "required": false,
                        "description": "Search query to filter by code or title",
                        "schema": {
                            "type": "string"
                        }
                    },
                    {
                        "name": "page",
                        "in": "query",
                        "required": false,
                        "description": "Page number for pagination",
                        "schema": {
                            "type": "integer",
                            "minimum": 1,
                            "default": 1
                        }
                    }
                ],
                "responses": {
                    "200": {
                        "description": "Paginated list of category items",
                        "content": {
                            "application/json": {
                                "schema": {
                                    "type": "object",
                                    "properties": {
                                        "data": {
                                            "type": "array",
                                            "items": {
                                                "type": "object",
                                                "properties": {
                                                    "code": {
                                                        "type": "string",
                                                        "example": "ABC123"
                                                    },
                                                    "title": {
                                                        "type": "string",
                                                        "example": "Sample Title"
                                                    }
                                                }
                                            }
                                        },
                                        "pagination": {
                                            "type": "object",
                                            "properties": {
                                                "total": {
                                                    "type": "integer",
                                                    "example": 100
                                                },
                                                "per_page": {
                                                    "type": "integer",
                                                    "example": 20
                                                },
                                                "current_page": {
                                                    "type": "integer",
                                                    "example": 1
                                                },
                                                "last_page": {
                                                    "type": "integer",
                                                    "example": 5
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    "404": {
                        "description": "Invalid category type"
                    }
                }
            }
        }
    }
}
```

4. Don't forget to update the **routes/api.php**
    > The file will look like this

```php
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

use App\Http\Controllers\MascoController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\JwtMiddleware;

Route::middleware([JwtMiddleware::class])->group(function () {
    Route::get('/masco', [MascoController::class, 'index']);
    Route::get('/category', [CategoryController::class, 'index']);
});
```

5. Restart the server in the terminal. Then, run

```bash
php artisan config:clear
php artisan route:clear
php artisan serve
```

6. Now, you should be able to call the API with Swagger UI.

Try going to http://127.0.0.1:8000/swagger on your web browser.
