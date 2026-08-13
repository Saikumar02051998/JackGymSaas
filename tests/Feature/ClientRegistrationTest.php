<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SaasSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(SaasSeeder::class);
    }

    private function makeOwnerGym(): array
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
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $owner = User::create([
            'gym_id' => $gym->id,
            'name' => 'Owner',
            'email' => 'owner-' . uniqid() . '@jackgym.test',
            'password' => 'secret123',
            'status' => 'active',
        ]);
        $owner->roles()->attach(Role::where('slug', 'owner')->first()->id);

        return [$gym, $owner];
    }

    public function test_client_creation_is_rejected_when_active_user_exists_in_same_gym(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        User::create([
            'gym_id' => $gym->id,
            'name' => 'Existing Client',
            'email' => 'duplicate@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->post(route('clients.store'), [
                'name' => 'New Client',
                'email' => 'duplicate@example.com',
            ])
            ->assertSessionHasErrors(['email' => 'User already registered with this email.']);

        $this->assertDatabaseMissing('clients', ['name' => 'New Client']);
    }

    public function test_client_creation_is_rejected_when_active_user_exists_in_another_gym(): void
    {
        [$gymA] = $this->makeOwnerGym();
        [$gymB, $ownerB] = $this->makeOwnerGym();

        User::create([
            'gym_id' => $gymA->id,
            'name' => 'Existing Client',
            'email' => 'other-gym@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $this->actingAs($ownerB)
            ->post(route('clients.store'), [
                'name' => 'New Client',
                'email' => 'other-gym@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('clients', ['name' => 'New Client']);
    }

    public function test_client_creation_succeeds_for_a_fresh_email(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)
            ->post(route('clients.store'), [
                'name' => 'Brand New Client',
                'email' => 'fresh@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('clients', ['gym_id' => $gym->id, 'status' => 'active']);
        $this->assertDatabaseHas('users', ['email' => 'fresh@example.com', 'status' => 'active']);
    }

    public function test_client_creation_succeeds_with_email_of_a_deleted_client(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)->post(route('clients.store'), [
            'name' => 'Client To Delete',
            'email' => 'deleted@example.com',
        ])->assertSessionHasNoErrors();

        $created = \App\Models\Client::whereHas('user', fn ($q) => $q->where('email', 'deleted@example.com'))->first();
        $this->assertNotNull($created);

        $this->actingAs($owner)->delete(route('clients.destroy', $created))->assertRedirect();

        $this->assertSoftDeleted('clients', ['id' => $created->id]);
        $this->assertSoftDeleted('users', ['id' => $created->user_id]);

        $this->actingAs($owner)
            ->post(route('clients.store'), [
                'name' => 'Re-registered Client',
                'email' => 'deleted@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'deleted@example.com', 'status' => 'active']);
    }

    public function test_client_creation_succeeds_with_email_of_a_soft_deleted_user(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $user = User::create([
            'gym_id' => $gym->id,
            'name' => 'Old Member',
            'email' => 'old@example.com',
            'password' => 'secret123',
            'status' => 'active',
        ]);
        $user->delete();

        $this->actingAs($owner)
            ->post(route('clients.store'), [
                'name' => 'New Member',
                'email' => 'old@example.com',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'old@example.com', 'status' => 'active']);
    }

    public function test_member_ids_are_unique_even_when_sequence_has_drifted(): void
    {
        $service = app(\App\Services\ClientService::class);
        [, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)->post(route('clients.store'), ['name' => 'Member A', 'email' => 'a@example.com']);
        $this->actingAs($owner)->post(route('clients.store'), ['name' => 'Member B', 'email' => 'b@example.com']);

        \App\Models\Client::orderBy('id')->first()->update(['member_id' => 'JG00099']);

        $generated = $service->generateMemberId(current_gym()->id);

        $this->assertNotSame('JG00099', $generated);
        $this->assertSame('JG00100', $generated);
        $this->assertSame(
            \App\Models\Client::withTrashed()->pluck('member_id')->count(),
            \App\Models\Client::withTrashed()->pluck('member_id')->unique()->count()
        );
    }

    public function test_creating_client_with_trial_creates_a_free_trial_membership(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)
            ->post(route('clients.store'), [
                'name' => 'Trial Client',
                'email' => 'trial@example.com',
                'start_trial' => '1',
                'trial_days' => '10',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $client = \App\Models\Client::whereHas('user', fn ($q) => $q->where('email', 'trial@example.com'))->first();
        $this->assertNotNull($client);

        $this->assertDatabaseHas('trials', ['client_id' => $client->id, 'status' => 'active']);

        $membership = $client->activeMembership;
        $this->assertNotNull($membership);
        $this->assertSame('Free Trial', $membership->plan->name);
        $this->assertSame('active', $membership->status);
        $this->assertSame('free', $membership->payment_status);
        $this->assertSame(0, (int) $membership->final_amount);
        $this->assertSame(now()->addDays(9)->toDateString(), $membership->end_date);
    }

    public function test_creating_client_without_trial_does_not_create_trial_membership(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)
            ->post(route('clients.store'), [
                'name' => 'Paid Client',
                'email' => 'paid@example.com',
            ])
            ->assertSessionHasNoErrors();

        $client = \App\Models\Client::whereHas('user', fn ($q) => $q->where('email', 'paid@example.com'))->first();

        $this->assertDatabaseMissing('trials', ['client_id' => $client->id]);
        $this->assertSame(0, \App\Models\Membership::where('client_id', $client->id)->count());
    }

    public function test_process_expired_command_marks_overdue_trial_membership_and_trial_as_expired(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)->post(route('clients.store'), [
            'name' => 'Expiring Trial',
            'email' => 'expiring@example.com',
            'start_trial' => '1',
            'trial_days' => '7',
        ]);

        $client = \App\Models\Client::whereHas('user', fn ($q) => $q->where('email', 'expiring@example.com'))->first();

        $client->activeMembership->update(['end_date' => now()->subDay()->toDateString()]);
        $trial = \App\Models\Trial::where('client_id', $client->id)->first();
        $trial->update(['trial_end' => now()->subDay()->toDateString()]);

        $this->artisan('memberships:process-expired')->assertSuccessful();

        $this->assertSame('expired', $client->activeMembership->fresh()->status);
        $this->assertSame('expired', $trial->fresh()->status);
    }

    public function test_client_create_page_defaults_to_free_trial(): void
    {
        [$gym, $owner] = $this->makeOwnerGym();

        $this->actingAs($owner)->get(route('clients.create'))->assertStatus(200);
    }
}
