<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Find a user by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function findUserById($id): JsonResponse
    {
        $user = User::with('tickets')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        return response()->json(['user' => $user]);
    }

    /**
     * Find a user by phone number
     *
     * @param string $phoneNumber
     * @return \Illuminate\Http\JsonResponse
     */
    public function findUserByPhoneNumber($phoneNumber): JsonResponse
    {
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        return response()->json(['user' => $user]);
    }

    /**
     * Display a listing of users
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function call()
    {
        return view('users.call');
    }

    /**
     * Display a specific user with their tickets
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::with('tickets')->findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for creating a new user
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'balance' => 'nullable|numeric',
            'payment_status' => 'nullable|string|in:paid,unpaid',
            'active_status' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['balance'] = $validated['balance'] ?? 0;
        $validated['payment_status'] = $validated['payment_status'] ?? 'unpaid';
        $validated['active_status'] = $validated['active_status'] ?? true;

        User::create($validated);

        return redirect()->route('users.index')
                         ->with('success', 'User created successfully');
    }

    /**
     * Show the form for editing the specified user
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone_number' => 'required|string|max:20|unique:users,phone_number,' . $id,
            'password' => 'nullable|string|min:8',
            'balance' => 'nullable|numeric',
            'payment_status' => 'nullable|string|in:paid,unpaid',
            'active_status' => 'nullable|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.show', $user->id)
                         ->with('success', 'User updated successfully');
    }
}
