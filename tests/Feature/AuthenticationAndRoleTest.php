<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationAndRoleTest extends TestCase
{
    /**
     * Test guests cannot access protected dashboard route and are redirected to login.
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    /**
     * Test GET /logout gracefully redirects to /login.
     */
    public function test_get_logout_redirects_to_login(): void
    {
        $response = $this->get('/logout');
        $response->assertRedirect('/login');
    }

    /**
     * Test POST /logout redirects to /login even without CSRF token.
     */
    public function test_post_logout_without_csrf_redirects_to_login(): void
    {
        $response = $this->post('/logout');
        $response->assertRedirect('/login');
    }
}
