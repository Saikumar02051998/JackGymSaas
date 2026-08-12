<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SaasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function test_saas_admin_can_reset_gym_owner_password(): void
    {
        $admin = User::where('email', env('SAAS_ADMIN_EMAIL', 'saas@jackgym.test'))->firstOrFail();

        [, $owner] = $this->makeOwnerGym('active', now()->addMonth());

        $this->actingAs($admin)
            ->post(route('saas.gyms.owner-password', $owner->gym), [
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertTrue(Hash::check('newsecret123', $owner->fresh()->password));

        Auth::logout();

        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'newsecret123',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_saas_admin_cannot_reset_password_with_short_input(): void
    {
        $admin = User::where('email', env('SAAS_ADMIN_EMAIL', 'saas@jackgym.test'))->firstOrFail();

        [, $owner] = $this->makeOwnerGym('active', now()->addMonth());

        $this->actingAs($admin)
            ->post(route('saas.gyms.owner-password', $owner->gym), [
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors('password');

        $this->assertFalse(Hash::check('short', $owner->fresh()->password));
    }

    public function test_expired_gym_owner_is_sent_to_renewal_page_after_login(): void
    {
        [$gym, $owner] = $this->makeOwnerGym('expired', now()->subDay());

        $this->post('/login', [
            'email' => $owner->email,
            'password' => 'secret123',
        ])->assertRedirect(route('subscription.index'));

        $this->assertFalse($gym->isSubscriptionActive());
    }

    public function test_expired_gym_owner_is_redirected_from_other_pages_to_renewal(): void
    {
        [, $owner] = $this->makeOwnerGym('expired', now()->subDay());

        $this->actingAs($owner)
            ->get('/dashboard')
            ->assertRedirect(route('subscription.index'));

        $this->actingAs($owner)
            ->get(route('subscription.index'))
            ->assertStatus(200);
    }

    public function test_owner_can_access_subscription_page(): void
    {
        [, $owner] = $this->makeOwnerGym('active', now()->addMonth());

        $this->actingAs($owner)->get('/subscription')->assertStatus(200);
    }

    public function test_non_owner_cannot_access_subscription_page_when_active(): void
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

    public function test_non_owner_can_see_renewal_notice_when_expired(): void
    {
        $gym = Gym::create([
            'name' => 'Client Gym',
            'slug' => 'client-gym-' . uniqid(),
            'status' => 'active',
            'subscription_status' => 'expired',
            'subscription_expires_at' => now()->subDay(),
        ]);

        $client = User::create([
            'gym_id' => $gym->id,
            'name' => 'Client',
            'email' => 'client-' . uniqid() . '@jackgym.test',
            'password' => 'secret123',
            'status' => 'active',
        ]);
        $client->roles()->attach(Role::where('slug', 'client')->first()->id);

        $this->actingAs($client)->get('/subscription')->assertStatus(200);
    }
}
