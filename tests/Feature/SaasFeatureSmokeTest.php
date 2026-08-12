<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SaasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasFeatureSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SaasSeeder::class);
    }

    protected function makeOwnerGym(string $status, ?string $expiry = null): array
    {
        $gym = Gym::create([
            'name' => 'Owner Gym',
            'slug' => 'owner-gym-' . uniqid(),
            'email' => 'owner-gym@jackgym.test',
            'currency' => 'INR',
            'currency_symbol' => '₹',
            'timezone' => 'Asia/Kolkata',
            'tax_percent' => 0,
            'invoice_prefix' => 'INV',
            'status' => 'active',
            'subscription_status' => $status,
            'subscription_expires_at' => $expiry,
        ]);

        $user = User::create([
            'gym_id' => $gym->id,
            'name' => 'Owner',
            'email' => 'owner-' . uniqid() . '@jackgym.test',
            'password' => 'secret123',
            'status' => 'active',
        ]);
        $user->roles()->attach(Role::where('slug', 'owner')->first()->id);

        return [$gym, $user];
    }

    public function test_saas_admin_can_access_portal(): void
    {
        $admin = User::where('email', env('SAAS_ADMIN_EMAIL', 'saas@jackgym.test'))->firstOrFail();

        $this->actingAs($admin)->get('/saas')->assertStatus(200);
        $this->actingAs($admin)->get('/saas/gyms')->assertStatus(200);
        $this->actingAs($admin)->get('/saas/plans')->assertStatus(200);
        $this->actingAs($admin)->get('/saas/payments')->assertStatus(200);
        $this->actingAs($admin)->get('/saas/settings')->assertStatus(200);
    }

    public function test_gym_owner_cannot_access_saas_portal(): void
    {
        [, $owner] = $this->makeOwnerGym('active', now()->addMonth());

        $this->actingAs($owner)->get('/saas')->assertStatus(403);
    }

    public function test_login_blocked_when_gym_subscription_expired(): void
    {
        [$gym, $owner] = $this->makeOwnerGym('expired', now()->subDay());

        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'secret123',
        ])->assertSessionHasErrors('email');

        $this->assertFalse($gym->isSubscriptionActive());
    }

    public function test_owner_can_access_subscription_page(): void
    {
        [, $owner] = $this->makeOwnerGym('active', now()->addMonth());

        $this->actingAs($owner)->get('/subscription')->assertStatus(200);
    }

    public function test_non_owner_cannot_access_subscription_page(): void
    {
        $gym = Gym::create([
            'name' => 'Client Gym',
            'slug' => 'client-gym-' . uniqid(),
            'status' => 'active',
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $client = User::create([
            'gym_id' => $gym->id,
            'name' => 'Client',
            'email' => 'client-' . uniqid() . '@jackgym.test',
            'password' => 'secret123',
            'status' => 'active',
        ]);
        $client->roles()->attach(Role::where('slug', 'client')->first()->id);

        $this->actingAs($client)->get('/subscription')->assertStatus(403);
    }
}
