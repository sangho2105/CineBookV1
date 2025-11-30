<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    // Show the login form
    public function showLoginForm(Request $request)
    {
        // Lưu URL redirect vào session nếu có
        if ($request->has('redirect')) {
            session()->put('url.intended', $request->get('redirect'));
        }
        
        return view('auth.login');
    }

    // Handle login request
    public function login(LoginRequest $request)
    {
        // Get login credentials from request
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember'); // "Remember Me" checkbox

        // Attempt to log in
        if (Auth::attempt($credentials, $remember)) {
            // Login successful
            $request->session()->regenerate(); // Prevent session fixation

            // Check role and redirect accordingly
            if (Auth::user()->role === 'admin') {
                return redirect()->intended(route('admin.movies.index'))->with('success', 'Login successful!');
            }

            return redirect()->intended(route('home'))->with('success', 'Login successful!');
        }

        // Login failed
        return back()->withErrors([
            'email' => 'The provided email or password is incorrect.',
        ])->withInput($request->only('email'));
    }

    // Handle logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate(); // Invalidate session
        $request->session()->regenerateToken(); // Regenerate CSRF token

        return redirect()->route('home')->with('success', 'You have been logged out successfully!');
    }
}
