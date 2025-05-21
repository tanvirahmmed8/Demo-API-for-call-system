<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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

    public function createUserTicketPhn(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Normalize phone number
        $normalizedPhone = preg_replace('/^00/', '+', $request->phone);

        $user = User::where('phone_number', $normalizedPhone)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found!',
                'status' => false
            ], 404);
        }

        // Generate a unique ticket number
        $ticketNumber = 'TKT-' . strtoupper(Str::random(8));

        $ticket = Ticket::create([
            'user_id' => $user->id,
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

    /**
     * Display a listing of tickets
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $tickets = Ticket::with('user')->get();
        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        $users = User::all();
        return view('tickets.create', compact('users'));
    }

    /**
     * Store a newly created ticket in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        // Generate a unique ticket number
        $ticketNumber = 'TKT-' . strtoupper(Str::random(8));

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'user_id' => $validated['user_id'],
            'ticket_number' => $ticketNumber,
            'status' => 'open',
        ]);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket created successfully');
    }

    /**
     * Display the specified ticket
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id): View
    {
        $ticket = Ticket::with('user')->findOrFail($id);
        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified ticket
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id): View
    {
        $ticket = Ticket::findOrFail($id);
        $users = User::all();
        return view('tickets.edit', compact('ticket', 'users'));
    }

    /**
     * Update the specified ticket in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $ticket = Ticket::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket->id)
            ->with('success', 'Ticket updated successfully');
    }
}
