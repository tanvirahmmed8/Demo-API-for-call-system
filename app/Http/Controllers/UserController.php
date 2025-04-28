<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}
