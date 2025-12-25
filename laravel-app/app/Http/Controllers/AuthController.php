<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        // If already authenticated, redirect to expenses
        if (session('authenticated') === true) {
            return redirect()->route('expenses.index');
        }

        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        // Validate input
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');
        $ip = $request->ip();

        // Rate limiting keys
        $userKey = "login-user:$username";
        $ipKey = "login-ip:$ip";

        // Check rate limits
        if (RateLimiter::tooManyAttempts($userKey, 5)) {
            $seconds = RateLimiter::availableIn($userKey);
            throw ValidationException::withMessages([
                'username' => "Too many login attempts. Please try again in $seconds seconds.",
            ]);
        }

        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            throw ValidationException::withMessages([
                'username' => "Too many login attempts from this IP. Please try again in $seconds seconds.",
            ]);
        }

        // Credential validation with hashed password
        $envUsername = env('AUTH_USERNAME', '');
        $envPasswordHash = env('PASSWORD_HASH', '');
        
        $validUsername = hash_equals($envUsername, $username);
        $validPassword = Hash::check($password, $envPasswordHash);

        if ($validUsername && $validPassword) {
            // Clear rate limits on successful login
            RateLimiter::clear($userKey);

            // Set authenticated session
            $request->session()->put('authenticated', true);

            // Redirect to intended URL or expenses
            return redirect()->intended(route('expenses.index'));
        }

        // Increment rate limit counters (15 minutes TTL)
        RateLimiter::hit($userKey, 15 * 60);
        RateLimiter::hit($ipKey, 15 * 60);

        // Return validation error
        throw ValidationException::withMessages([
            'username' => 'Invalid username or password.',
        ]);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        // Clear all session data
        $request->session()->flush();

        return redirect()->route('login');
    }
}
