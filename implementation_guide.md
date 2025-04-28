# Laravel Call Ticket System Implementation Guide

This guide contains all the code and instructions needed to complete the implementation of the call ticket system.

## 1. Database Configuration

Update your `.env` file with the following database configuration:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=demo_api_for_call
DB_USERNAME=root
DB_PASSWORD=
```

## 2. Update User Migration

Create a new migration to update the users table:

```php
// database/migrations/xxxx_xx_xx_update_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->string('phone_number')->nullable()->unique();
            $table->enum('payment_status', ['paid', 'unpaid', 'pending'])->default('unpaid');
            $table->enum('active_status', ['active', 'inactive'])->default('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['balance', 'phone_number', 'payment_status', 'active_status']);
        });
    }
};
```

## 3. Create Ticket Migration and Model

```php
// database/migrations/xxxx_xx_xx_create_tickets_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('ticket_number')->unique();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

```php
// app/Models/Ticket.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'ticket_number',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

## 4. Update User Model

```php
// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'balance',
        'payment_status',
        'active_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
```

## 5. Create Controllers

```php
// app/Http/Controllers/UserController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function findUserById($id): JsonResponse
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        return response()->json($user);
    }
    
    public function findUserByPhoneNumber($phoneNumber): JsonResponse
    {
        $user = User::where('phone_number', $phoneNumber)->first();
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        return response()->json($user);
    }

    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with('tickets')->findOrFail($id);
        return view('users.show', compact('user'));
    }
}
```

```php
// app/Http/Controllers/TicketController.php
<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function createUserTicket(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);
        
        $ticketNumber = 'TKT-' . strtoupper(Str::random(8));
        
        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => $request->user_id,
            'ticket_number' => $ticketNumber,
            'status' => 'open',
        ]);
        
        return response()->json($ticket, 201);
    }
    
    public function findUserTicket($id): JsonResponse
    {
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        return response()->json($ticket);
    }
    
    public function findLastIssuedTicket($userId): JsonResponse
    {
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        $lastTicket = Ticket::where('user_id', $userId)
            ->latest()
            ->first();
        
        if (!$lastTicket) {
            return response()->json(['message' => 'No tickets found for this user'], 404);
        }
        
        return response()->json($lastTicket);
    }
    
    public function updateTicketStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);
        
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        
        $ticket->status = $request->status;
        $ticket->save();
        
        return response()->json($ticket);
    }
}
```

## 6. Create Blade Views

```php
// resources/views/layouts/app.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Ticket System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">Call Ticket System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/users">Users</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

```php
// resources/views/users/index.blade.php
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h2>User List</h2>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Payment Status</th>
                    <th>Active Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone_number }}</td>
                    <td>${{ number_format($user->balance, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $user->payment_status == 'paid' ? 'success' : ($user->payment_status == 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($user->payment_status) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $user->active_status == 'active' ? 'success' : 'danger' }}">
                            {{ ucfirst($user->active_status) }}
                        </span>
                    </td>
                    <td>
                        <a href="/users/{{ $user->id }}" class="btn btn-sm btn-info">View Tickets</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
```

```php
// resources/views/users/show.blade.php
@extends('layouts.app')

@section('content')
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h2>User Details</h2>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone_number }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}</p>
                <p>
                    <strong>Payment Status:</strong>
                    <span class="badge bg-{{ $user->payment_status == 'paid' ? 'success' : ($user->payment_status == 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($user->payment_status) }}
                    </span>
                </p>
                <p>
                    <strong>Active Status:</strong>
                    <span class="badge bg-{{ $user->active_status == 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($user->active_status) }}
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2>User Tickets</h2>
    </div>
    <div class="card-body">
        @if($user->tickets->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ticket Number</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($user->tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->ticket_number }}</td>
                        <td>{{ $ticket->title }}</td>
                        <td>
                            <span class="badge bg-{{ $ticket->status == 'open' ? 'danger' : ($ticket->status == 'in_progress' ? 'warning' : ($ticket->status == 'resolved' ? 'success' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-info">No tickets found for this user.</div>
        @endif
    </div>
</div>
@endsection
```

## 7. Update Web Routes

```php
// routes/web.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect('/users');
});

Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
```

## 8. Create Database Seeders

```php
// database/seeders/UserSeeder.php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '1234567890',
                'balance' => 150.00,
                'payment_status' => 'paid',
                'active_status' => 'active',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '0987654321',
                'balance' => 0.00,
                'payment_status' => 'unpaid',
                'active_status' => 'active',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'password' => Hash::make('password'),
                'phone_number' => '5555555555',
                'balance' => 75.50,
                'payment_status' => 'pending',
                'active_status' => 'inactive',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}
```

```php
// database/seeders/TicketSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        $tickets = [
            [
                'title' => 'Network Connection Issue',
                'description' => 'Unable to connect to the internet since yesterday.',
                'ticket_number' => 'TKT-12345678',
                'status' => 'open',
            ],
            [
                'title' => 'Billing Question',
                'description' => 'I have a question about my last invoice.',
                'ticket_number' => 'TKT-23456789',
                'status' => 'in_progress',
            ],
            [
                'title' => 'Service Upgrade Request',
                'description' => 'I would like to upgrade my current service plan.',
                'ticket_number' => 'TKT-34567890',
                'status' => 'resolved',
            ],
            [
                'title' => 'Technical Support',
                'description' => 'My device is not working properly.',
                'ticket_number' => 'TKT-45678901',
                'status' => 'closed',
            ],
        ];

        foreach ($tickets as $index => $ticketData) {
            $userId = $users[($index % count($users))]->id;
            Ticket::create([
                'title' => $ticketData['title'],
                'description' => $ticketData['description'],
                'user_id' => $userId,
                'ticket_number' => $ticketData['ticket_number'],
                'status' => $ticketData['status'],
            ]);
        }
    }
}
```

```php
// database/seeders/DatabaseSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TicketSeeder::class,
        ]);
    }
}
```

## 9. Installation and Setup Instructions

1. Configure your database in the `.env` file
2. Generate application key: `php artisan key:generate`
3. Run migrations: `php artisan migrate`
4. Seed the database: `php artisan db:seed`
5. Start the development server: `php artisan serve`
6. Visit http://localhost:8000 to access the application

## API Endpoints

- `GET /api/user/{id}` - Find user by ID
- `GET /api/user-phone/{phonenumber}` - Find user by phone number
- `POST /api/user/ticket-create` - Create a new ticket
- `GET /api/user/ticket/{id}` - Find ticket by ID
- `GET /api/user/last-ticket/{id}` - Find last issued ticket for a user
- `PUT /api/ticket-status/{id}` - Update ticket status

## Web Routes

- `GET /users` - View list of all users
- `GET /users/{id}` - View user details and their tickets
