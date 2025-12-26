<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Test-only authentication controller
 * Only available in local and testing environments
 */
class TestAuthController extends Controller
{
    /**
     * Authenticate for testing purposes
     * This endpoint bypasses normal authentication for e2e tests
     */
    public function authenticate(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'username' => 'required|string',
                'password_hash' => 'required|string',
            ]);

            $username = $validated['username'];
            $passwordHash = $validated['password_hash'];

            // Verify credentials match environment
            $envUsername = env('AUTH_USERNAME', '');
            $envPasswordHash = env('PASSWORD_HASH', '');

            if (hash_equals($envUsername, $username) && hash_equals($envPasswordHash, $passwordHash)) {
                // Set authenticated session
                $request->session()->put('authenticated', true);

                // Regenerate session ID for security
                $request->session()->regenerate();

                // Force save to ensure session is written immediately
                $request->session()->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Authenticated successfully',
                    'session_id' => session()->getId(), // For debugging
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
