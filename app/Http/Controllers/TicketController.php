<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Create a new ticket for a user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUserTicket(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Generate a unique ticket number
        $ticketNumber = 'TKT-' . strtoupper(Str::random(8));

        $ticket = Ticket::create([
            'user_id' => $request->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'ticket_number' => $ticketNumber,
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket
        ], 201);
    }

    /**
     * Find a specific ticket by ID
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function findUserTicket($id): JsonResponse
    {
        $ticket = Ticket::with('user')->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found']);
        }

        return response()->json(['ticket' => $ticket]);
    }

    /**
     * Find the last issued ticket for a user
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function findLastIssuedTicket($userId): JsonResponse
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        $lastTicket = Ticket::where('user_id', $userId)
                           ->latest()
                           ->first();

        if (!$lastTicket) {
            return response()->json(['message' => 'No tickets found for this user']);
        }

        return response()->json(['ticket' => $lastTicket]);
    }

    /**
     * Update the status of a ticket
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateTicketStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found']);
        }

        $ticket->status = $request->status;
        $ticket->save();

        return response()->json([
            'message' => 'Ticket status updated successfully',
            'ticket' => $ticket
        ]);
    }
}
