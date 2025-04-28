@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2>User List</h2>
        <a href="{{ url('/users/create') }}" class="btn btn-light">Create New User</a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

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
                        <a href="/users/{{ $user->id }}/edit" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
