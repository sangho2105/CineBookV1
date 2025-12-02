<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // Show the registration form
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Handle registration
    public function register(RegisterRequest $request)
    {
        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password), // Hash the password
            'age' => $request->age,
            'date_of_birth' => $request->date_of_birth,
            'preferred_language' => $request->preferred_language,
            'preferred_city' => $request->preferred_city,
            'role' => 'user', // Default role is 'user'
        ]);

        // Automatically login after registration
        Auth::login($user);

        // Redirect to home page with success message
        return redirect()->route('home')->with('success', 'Registration successful!');
    }
}
