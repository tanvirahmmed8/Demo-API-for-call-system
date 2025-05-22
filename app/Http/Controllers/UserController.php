<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Search;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Hash;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;

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
        $search = new Search;
        $search->raw_query = $phoneNumber;
        $normalizedPhone = preg_replace('/^00/', '+', $phoneNumber);
        $search->modified_query = $normalizedPhone;
        $user = User::where('phone_number', $normalizedPhone)->first();
        $search->output = json_encode($user);
        $search->save();

        if (!$user) {
            return response()->json(['message' => 'User not found']);
        }

        return response()->json(['user' => $user]);
    }

    public function createOrUpdateUser(Request $request)
    {
        // API key verification
        if ($request->key !== "pia-123") {
            return response()->json(['message' => 'Invalid key'], 403);
        }

        // Validate required fields except for unique rules (we'll handle that manually)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'balance' => 'nullable|numeric',
            'payment_status' => 'nullable|string|in:paid,unpaid',
            'active_status' => 'nullable|boolean',
            'stage' => 'nullable|string',
            'preferred_country' => 'nullable|string',
            'preferred_program' => 'nullable|string',
        ]);

        // Normalize phone number using libphonenumber
        $normalizedPhone = null;
        if (!empty($validated['phone_number'])) {
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();

                // Smart region detection
                $defaultRegion = preg_match('/^(\+|00)/', $validated['phone_number']) ? null : 'BD';

                $numberProto = $phoneUtil->parse($validated['phone_number'], $defaultRegion);
                $normalizedPhone = $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
                $validated['phone_number'] = $normalizedPhone;
            } catch (NumberParseException $e) {
                return response()->json(['message' => 'Invalid phone number format'], 422);
            }
        }

        // Set default values
        $validated['password'] = Hash::make($validated['password'] ?? '12345678');
        $validated['balance'] = $validated['balance'] ?? 0;
        $validated['payment_status'] = $validated['payment_status'] ?? 'unpaid';
        $validated['active_status'] = $validated['active_status'] ?? true;

        // Check if user exists by email or phone
        $user = User::where('email', $validated['email']);

        if ($normalizedPhone) {
            $user->orWhere('phone_number', $normalizedPhone);
        }

        $user = $user->first();

        if ($user) {
            // Update user
            $user->update($validated);
            $message = 'User successfully updated';
        } else {
            // Create new user
            $user = User::create($validated);
            $message = 'User successfully created';
        }

        return response()->json(['message' => $message, 'user' => $user]);
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

    public function search()
    {
        $search = Search::latest()->paginate(25);
        return $search;
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
