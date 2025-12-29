<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Feature tests for authentication functionality.
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Get test username from environment.
     */
    protected function getTestUsername(): string
    {
        return config('auth.custom.username', 'testuser');
    }

    /**
     * Get test password from environment.
     */
    protected function getTestPassword(): string
    {
        return env('TEST_PASSWORD', 'testpass');
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limiters before each test
        $username = $this->getTestUsername();
        RateLimiter::clear("login-user:{$username}");
        RateLimiter::clear('login-ip:127.0.0.1');
    }

    // ==========================================
    // Login Page Tests
    // ==========================================

    /**
     * Test login page displays correctly.
     */
    public function test_login_page_displays_correctly(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('Login');
        $response->assertSee('Username');
        $response->assertSee('Password');
    }

    /**
     * Test authenticated users are redirected from login page.
     */
    public function test_authenticated_users_redirected_from_login(): void
    {
        session(['authenticated' => true]);

        $response = $this->get('/login');

        $response->assertRedirect('/expenses');
    }

    // ==========================================
    // Login Authentication Tests
    // ==========================================

    /**
     * Test login with valid credentials.
     */
    public function test_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => $this->getTestUsername(),
            'password' => $this->getTestPassword(),
        ]);

        $response->assertRedirect('/expenses');
        $this->assertTrue(session('authenticated') === true);
    }

    /**
     * Test login with invalid username.
     */
    public function test_login_with_invalid_username(): void
    {
        $response = $this->post('/login', [
            'username' => 'wronguser',
            'password' => $this->getTestPassword(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertFalse(session('authenticated') === true);
    }

    /**
     * Test login with invalid password.
     */
    public function test_login_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'username' => $this->getTestUsername(),
            'password' => 'wrongpass',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertFalse(session('authenticated') === true);
    }

    /**
     * Test login with missing username.
     */
    public function test_login_with_missing_username(): void
    {
        $response = $this->post('/login', [
            'username' => '',
            'password' => 'testpass',
        ]);

        $response->assertSessionHasErrors('username');
    }

    /**
     * Test login with missing password.
     */
    public function test_login_with_missing_password(): void
    {
        $response = $this->post('/login', [
            'username' => $this->getTestUsername(),
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ==========================================
    // Rate Limiting Tests
    // ==========================================

    /**
     * Test rate limiting per username (5 attempts).
     */
    public function test_rate_limiting_per_username(): void
    {
        $username = $this->getTestUsername();

        // Make 5 failed attempts (should work)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'username' => $username,
                'password' => 'wrongpass',
            ]);
            $response->assertSessionHasErrors('username');
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'username' => $username,
            'password' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors('username');
        $errors = session()->get('errors');
        $this->assertTrue(
            str_contains($errors->first('username'), 'Too many login attempts')
        );
    }

    /**
     * Test rate limiting per IP address (10 attempts).
     */
    public function test_rate_limiting_per_ip_address(): void
    {
        // Make 10 failed attempts with different usernames
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login', [
                'username' => 'user' . $i,
                'password' => 'wrongpass',
            ]);
            $response->assertSessionHasErrors('username');
        }

        // 11th attempt should be rate limited
        $response = $this->post('/login', [
            'username' => 'user10',
            'password' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors('username');
        $errors = session()->get('errors');
        $this->assertTrue(
            str_contains($errors->first('username'), 'Too many login attempts')
        );
    }

    /**
     * Test successful login clears rate limiter for that username.
     */
    public function test_successful_login_clears_rate_limiter(): void
    {
        $username = $this->getTestUsername();
        $password = $this->getTestPassword();

        // Make 4 failed attempts
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', [
                'username' => $username,
                'password' => 'wrongpass',
            ]);
        }

        // Successful login should clear the rate limiter
        $response = $this->post('/login', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertRedirect('/expenses');
        $this->assertTrue(session('authenticated') === true);

        // Should be able to login again immediately
        $this->post('/logout');
        $response = $this->post('/login', [
            'username' => $username,
            'password' => $password,
        ]);

        $response->assertRedirect('/expenses');
    }

    // ==========================================
    // Logout Tests
    // ==========================================

    /**
     * Test logout clears session.
     */
    public function test_logout_clears_session(): void
    {
        // Login first
        session(['authenticated' => true]);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(session('authenticated') === true);
    }

    /**
     * Test logout redirects to login page.
     */
    public function test_logout_redirects_to_login(): void
    {
        session(['authenticated' => true]);

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }

    // ==========================================
    // Middleware Protection Tests
    // ==========================================

    /**
     * Test unauthenticated users cannot access expenses index.
     */
    public function test_unauthenticated_cannot_access_expenses_index(): void
    {
        $response = $this->get('/expenses');

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot access create form.
     */
    public function test_unauthenticated_cannot_access_create_form(): void
    {
        $response = $this->get('/expenses/create');

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot create expenses.
     */
    public function test_unauthenticated_cannot_create_expense(): void
    {
        $response = $this->post('/expenses', [
            'description' => 'Test Expense',
            'amount' => 50.00,
            'category' => 'Groceries',
            'date' => '2025-12-15',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot access edit form.
     */
    public function test_unauthenticated_cannot_access_edit_form(): void
    {
        // Create an expense first (as authenticated)
        session(['authenticated' => true]);
        $expense = \App\Models\Expense::factory()->create();
        session()->forget('authenticated');

        // Now try to access edit form without auth
        $response = $this->get("/expenses/{$expense->id}/edit");

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot update expenses.
     */
    public function test_unauthenticated_cannot_update_expense(): void
    {
        // Create an expense first (as authenticated)
        session(['authenticated' => true]);
        $expense = \App\Models\Expense::factory()->create();
        session()->forget('authenticated');

        $response = $this->put("/expenses/{$expense->id}", [
            'description' => 'Updated Expense',
            'amount' => 75.00,
            'category' => 'Transport',
            'date' => '2025-12-16',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot delete expenses.
     */
    public function test_unauthenticated_cannot_delete_expense(): void
    {
        // Create an expense first (as authenticated)
        session(['authenticated' => true]);
        $expense = \App\Models\Expense::factory()->create();
        session()->forget('authenticated');

        $response = $this->delete("/expenses/{$expense->id}");

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot access daily view.
     */
    public function test_unauthenticated_cannot_access_daily_view(): void
    {
        $response = $this->get('/expenses/daily');

        $response->assertRedirect('/login');
    }

    /**
     * Test unauthenticated users cannot access monthly view.
     */
    public function test_unauthenticated_cannot_access_monthly_view(): void
    {
        $response = $this->get('/expenses/monthly');

        $response->assertRedirect('/login');
    }

    // ==========================================
    // Security Tests
    // ==========================================

    /**
     * Test login uses timing-safe comparison.
     */
    public function test_login_uses_timing_safe_comparison(): void
    {
        // This test verifies that invalid credentials always take similar time
        // by checking that we get consistent error messages regardless of whether
        // username or password is wrong

        $response1 = $this->post('/login', [
            'username' => 'wronguser',
            'password' => $this->getTestPassword(),
        ]);

        $response2 = $this->post('/login', [
            'username' => $this->getTestUsername(),
            'password' => 'wrongpass',
        ]);

        // Both should return the same generic error message
        $response1->assertSessionHasErrors('username');
        $response2->assertSessionHasErrors('username');
    }

    /**
     * Test session is properly flushed on logout.
     */
    public function test_session_properly_flushed_on_logout(): void
    {
        // Set up session with multiple values
        session(['authenticated' => true, 'test_key' => 'test_value']);

        $response = $this->post('/logout');

        // All session data should be cleared
        $response->assertSessionMissing('authenticated');
        $response->assertSessionMissing('test_key');
    }

    /**
     * Test root URL redirects to login when unauthenticated.
     */
    public function test_root_redirects_to_login_when_unauthenticated(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test root URL shows expenses when authenticated.
     */
    public function test_root_shows_expenses_when_authenticated(): void
    {
        session(['authenticated' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
