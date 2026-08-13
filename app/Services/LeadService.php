<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Followup;
use App\Models\Lead;
use App\Models\Membership;
use App\Services\MembershipService;
use App\Models\Trial;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function create(array $data, int $gymId): Lead
    {
        $lead = Lead::create([
            'gym_id' => $gymId,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'source' => $data['source'] ?? null,
            'interested_plan_id' => $data['interested_plan_id'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'status' => $data['status'] ?? 'new',
            'follow_up_date' => $data['follow_up_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        audit_log('lead.created', 'leads', $lead->id, "Lead {$lead->name} created");

        return $lead;
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? $lead->email,
            'source' => $data['source'] ?? $lead->source,
            'interested_plan_id' => $data['interested_plan_id'] ?? $lead->interested_plan_id,
            'assigned_to' => $data['assigned_to'] ?? $lead->assigned_to,
            'status' => $data['status'] ?? $lead->status,
            'follow_up_date' => $data['follow_up_date'] ?? $lead->follow_up_date,
            'notes' => $data['notes'] ?? $lead->notes,
        ]);

        audit_log('lead.updated', 'leads', $lead->id, "Lead {$lead->name} updated");

        return $lead;
    }

    public function convertToClient(Lead $lead, array $data = []): array
    {
        return DB::transaction(function () use ($lead, $data) {
            $clientService = app(ClientService::class);

            $client = $clientService->create([
                'name' => $data['name'] ?? $lead->name,
                'phone' => $data['phone'] ?? $lead->phone,
                'email' => $data['email'] ?? $lead->email,
                'lead_source' => $data['lead_source'] ?? $lead->source,
                'joining_date' => $data['joining_date'] ?? now()->toDateString(),
                'assigned_trainer_id' => $data['assigned_trainer_id'] ?? null,
            ], $lead->gym_id);

            $lead->update([
                'status' => 'converted',
                'converted_at' => now()->toDateString(),
            ]);

            if (isset($data['start_trial']) && $data['start_trial']) {
                Trial::create([
                    'gym_id' => $lead->gym_id,
                    'client_id' => $client->id,
                    'lead_id' => $lead->id,
                    'trial_start' => now()->toDateString(),
                    'trial_end' => now()->addDays((int) ($data['trial_days'] ?? 7))->toDateString(),
                    'assigned_trainer_id' => $data['assigned_trainer_id'] ?? null,
                    'status' => 'active',
                    'follow_up_date' => now()->addDays((int) ($data['trial_days'] ?? 7))->toDateString(),
                ]);

                app(MembershipService::class)->createTrial($client, (int) ($data['trial_days'] ?? 7));
            }

            audit_log('lead.converted', 'leads', $lead->id, "Lead {$lead->name} converted to client {$client->display_name}");

            return ['success' => true, 'client' => $client];
        });
    }

    public function createFollowup(Lead $lead, array $data): \App\Models\LeadFollowup
    {
        $followup = $lead->followups()->create([
            'follow_up_date' => $data['follow_up_date'],
            'follow_up_time' => $data['follow_up_time'] ?? null,
            'type' => $data['type'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        if ($data['follow_up_date'] ?? null) {
            $lead->update(['follow_up_date' => $data['follow_up_date']]);
        }

        audit_log('lead_followup.created', 'leads', $followup->id, "Follow-up created for lead {$lead->name}");

        return $followup;
    }
}
