<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the current user's profile page 
     */
    public function index()
    {
        $user = Auth::user(); // Get the logged-in user
        // Lấy lịch sử đặt vé của user
        $bookings = $user->bookings()
            ->with(['showtime.movie', 'showtime.theater', 'seats'])
            ->orderByDesc('booking_date')
            ->get();
        return view('profile.index', compact('user', 'bookings'));
    }

    /**
     * Show the edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate input data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'age' => 'nullable|integer|min:1|max:120',
            'preferred_language' => 'nullable|string|max:50',
            'preferred_city' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update basic information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->age = $request->age;
        $user->preferred_language = $request->preferred_language;
        $user->preferred_city = $request->preferred_city;

        // Update password only if a new one is entered
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.index')
            ->with('success', 'Profile updated successfully!');
    }
}