<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientHealthProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientService
{
    public function create(array $data, int $gymId, ?int $trainerId = null, ?string $referralCode = null): Client
    {
        return DB::transaction(function () use ($data, $gymId, $trainerId, $referralCode) {
            $name = $data['name'];

            $email = $data['email'] ?? null;
            $phone = $data['phone'] ?? null;

            $userData = [
                'gym_id' => $gymId,
                'name' => $name,
                'phone' => $phone,
                'password' => isset($data['password']) && $data['password']
                    ? $data['password']
                    : Str::random(12),
                'status' => 'active',
            ];

            if ($email) {
                $userData['email'] = $email;
            }

            $user = User::create($userData);

            $referrer = null;
            if ($referralCode) {
                $referrer = Client::where('referral_code', $referralCode)->first();
            }

            $memberId = $this->generateMemberId($gymId);

            $client = Client::create([
                'gym_id' => $gymId,
                'user_id' => $user->id,
                'member_id' => $memberId,
                'joining_date' => $data['joining_date'] ?? now()->toDateString(),
                'lead_source' => $data['lead_source'] ?? null,
                'assigned_trainer_id' => $trainerId,
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,
                'phone' => $phone,
                'address' => $data['address'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'emergency_phone' => $data['emergency_phone'] ?? null,
                'referral_code' => Str::upper(Str::random(8)),
                'referred_by' => $referrer?->id,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);

            ClientHealthProfile::create([
                'client_id' => $client->id,
                'height' => $data['height'] ?? null,
                'weight' => $data['weight'] ?? null,
                'bmi' => $this->calculateBmi($data['height'] ?? null, $data['weight'] ?? null),
                'body_fat' => $data['body_fat'] ?? null,
                'goal_weight' => $data['goal_weight'] ?? null,
                'fitness_goal' => $data['fitness_goal'] ?? null,
                'activity_level' => $data['activity_level'] ?? null,
                'medical_notes' => $data['medical_notes'] ?? null,
                'injuries' => $data['injuries'] ?? null,
                'limitations' => $data['limitations'] ?? null,
                'allergies' => $data['allergies'] ?? null,
                'important_notes' => $data['important_notes'] ?? null,
            ]);

            if ($referrer) {
                $referrer->referrals()->create([
                    'gym_id' => $gymId,
                    'referrer_client_id' => $referrer->id,
                    'referred_client_id' => $client->id,
                    'status' => 'pending',
                ]);
            }

            $clientRole = \App\Models\Role::where('slug', 'client')->first();
            if ($clientRole) {
                $client->user->roles()->attach($clientRole->id);
            }

            audit_log('client.created', 'clients', $client->id, "Created client {$client->display_name}", [], $client->toArray());

            return $client;
        });
    }

    public function update(Client $client, array $data): Client
    {
        return DB::transaction(function () use ($client, $data) {
            $userData = [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
            ];

            if (! empty($data['email'])) {
                $userData['email'] = $data['email'];
            }

            $client->user->update($userData);

            $client->update([
                'assigned_trainer_id' => $data['assigned_trainer_id'] ?? $client->assigned_trainer_id,
                'gender' => $data['gender'] ?? $client->gender,
                'dob' => $data['dob'] ?? $client->dob,
                'phone' => $data['phone'] ?? $client->phone,
                'address' => $data['address'] ?? $client->address,
                'emergency_contact' => $data['emergency_contact'] ?? $client->emergency_contact,
                'emergency_phone' => $data['emergency_phone'] ?? $client->emergency_phone,
                'lead_source' => $data['lead_source'] ?? $client->lead_source,
                'notes' => $data['notes'] ?? $client->notes,
            ]);

            $health = $client->healthProfile;

            if ($health) {
                $health->update([
                    'height' => $data['height'] ?? $health->height,
                    'weight' => $data['weight'] ?? $health->weight,
                    'bmi' => $this->calculateBmi($data['height'] ?? $health->height, $data['weight'] ?? $health->weight),
                    'body_fat' => $data['body_fat'] ?? $health->body_fat,
                    'goal_weight' => $data['goal_weight'] ?? $health->goal_weight,
                    'fitness_goal' => $data['fitness_goal'] ?? $health->fitness_goal,
                    'activity_level' => $data['activity_level'] ?? $health->activity_level,
                    'medical_notes' => $data['medical_notes'] ?? $health->medical_notes,
                    'injuries' => $data['injuries'] ?? $health->injuries,
                    'limitations' => $data['limitations'] ?? $health->limitations,
                    'allergies' => $data['allergies'] ?? $health->allergies,
                    'important_notes' => $data['important_notes'] ?? $health->important_notes,
                ]);
            }

            audit_log('client.updated', 'clients', $client->id, "Updated client {$client->display_name}", [], $data);

            return $client;
        });
    }

    public function generateMemberId(int $gymId): string
    {
        $prefix = 'JG';

        $last = Client::withTrashed()->orderByDesc('id')->first();

        return $prefix . str_pad((string) (($last?->id ?? 0) + 1), 5, '0', STR_PAD_LEFT);
    }

    private function calculateBmi(?float $heightCm, ?float $weightKg): ?float
    {
        if (! $heightCm || ! $weightKg || $heightCm <= 0) {
            return null;
        }

        $heightM = $heightCm / 100;

        return round($weightKg / ($heightM * $heightM), 1);
    }
}
