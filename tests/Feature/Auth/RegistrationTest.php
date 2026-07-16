<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'organization_name' => 'Test Org',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    /**
     * Regression test: registration must create an organization and attach
     * the new user to it, since every authenticated route requires one
     * (ScopeToOrganization middleware aborts 403 otherwise). Without this,
     * a freshly registered user could never actually use the app.
     */
    public function test_a_freshly_registered_user_can_actually_reach_the_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'organization_name' => 'Acme Test Co',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $organization = $user->organizations()->first();

        $this->assertNotNull($organization, 'Registration did not attach the user to an organization.');
        $this->assertSame('Acme Test Co', $organization->name);
        $this->assertTrue((bool) $organization->pivot->is_owner);

        $this->followingRedirects()->get('/dashboard')
            ->assertOk()
            ->assertSee("You're logged in!");
    }
}
