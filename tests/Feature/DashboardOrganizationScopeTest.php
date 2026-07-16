<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOrganizationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_an_organization_can_view_the_dashboard(): void
    {
        $organization = Organization::create(['name' => 'Acme', 'slug' => 'acme']);
        $user = User::factory()->create();
        $organization->users()->attach($user->id, ['is_owner' => true]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $this->assertTrue(app(Organization::class)->is($organization));
    }

    public function test_user_without_an_organization_is_forbidden(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertForbidden();
    }
}
