@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2>Ticket Details</h2>
        <div>
            <a href="{{ url('/tickets/' . $ticket->id . '/edit') }}" class="btn btn-light">Edit Ticket</a>
            <a href="{{ url('/tickets') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Ticket Information</h4>
                <table class="table">
                    <tr>
                        <th style="width: 150px">Ticket Number:</th>
                        <td>{{ $ticket->ticket_number }}</td>
                    </tr>
                    <tr>
                        <th>Title:</th>
                        <td>{{ $ticket->title }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $ticket->status == 'open' ? 'danger' : ($ticket->status == 'in_progress' ? 'warning' : ($ticket->status == 'resolved' ? 'success' : 'secondary')) }}">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td>{{ $ticket->updated_at->format('M d, Y H:i') }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h4>User Information</h4>
                <table class="table">
                    <tr>
                        <th style="width: 150px">Name:</th>
                        <td>{{ $ticket->user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $ticket->user->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $ticket->user->phone_number }}</td>
                    </tr>
                    <tr>
                        <th>Balance:</th>
                        <td>${{ number_format($ticket->user->balance, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            <span class="badge bg-{{ $ticket->user->payment_status == 'paid' ? 'success' : 'danger' }}">
                                {{ ucfirst($ticket->user->payment_status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4>Description</h4>
            </div>
            <div class="card-body">
                {{ $ticket->description }}
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ url('/tickets') }}" class="btn btn-secondary">Back to Tickets</a>
            <a href="{{ url('/users/' . $ticket->user_id) }}" class="btn btn-info">View User Details</a>
        </div>
    </div>
</div>
@endsection
