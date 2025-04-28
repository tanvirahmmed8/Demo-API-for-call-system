@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3>User Details</h3>
                <a href="/users/{{ $user->id }}/edit" class="btn btn-light">Edit User</a>
            </div>
            <div class="card-body">
                <h4>{{ $user->name }}</h4>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Phone:</strong> {{ $user->phone_number }}</p>
                <p><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}</p>
                <p>
                    <strong>Payment Status:</strong>
                    <span class="badge bg-{{ $user->payment_status == 'paid' ? 'success' : ($user->payment_status == 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($user->payment_status) }}
                    </span>
                </p>
                <p>
                    <strong>Account Status:</strong>
                    <span class="badge bg-{{ $user->active_status == 'active' ? 'success' : 'danger' }}">
                        {{ ucfirst($user->active_status) }}
                    </span>
                </p>
                <a href="/users" class="btn btn-secondary">Back to Users</a>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3>User Tickets</h3>
                <a href="/tickets/create?user_id={{ $user->id }}" class="btn btn-light">Create Ticket</a>
            </div>
            <div class="card-body">
                @if($user->tickets->count() > 0)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ticket #</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->ticket_number }}</td>
                                <td><a href="/tickets/{{ $ticket->id }}">{{ $ticket->title }}</a></td>
                                <td>
                                    <span class="badge bg-{{ $ticket->status == 'open' ? 'danger' : ($ticket->status == 'in_progress' ? 'warning' : ($ticket->status == 'resolved' ? 'info' : 'success')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                </td>
                                <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">
                        This user has no tickets yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
