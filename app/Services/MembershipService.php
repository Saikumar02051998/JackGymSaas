<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Membership;
use App\Models\MembershipHistory;
use App\Models\MembershipPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function create(Client $client, MembershipPlan $plan, array $data = []): Membership
    {
        return DB::transaction(function () use ($client, $plan, $data) {
            $startDate = $data['start_date'] ?? now()->toDateString();
            $start = Carbon::parse($startDate);
            $end = $start->copy()->addDays($plan->duration_days)->subDay();

            $membership = Membership::create([
                'gym_id' => $client->gym_id,
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'membership_no' => next_sequence(Membership::class, 'membership_no', 'MS-'),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => $start->isFuture() ? 'upcoming' : 'active',
                'amount' => $plan->price,
                'discount' => $data['discount'] ?? $plan->discount,
                'tax' => $plan->tax,
                'final_amount' => $plan->final_amount - ($data['discount'] ?? 0),
                'payment_status' => 'pending',
                'created_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $membership->histories()->create([
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'action' => 'created',
                'previous_end_date' => null,
                'new_end_date' => $membership->end_date,
                'amount' => $membership->final_amount,
                'changed_by' => auth()->id(),
                'notes' => 'Membership created',
            ]);

            audit_log('membership.created', 'memberships', $membership->id, "Created membership {$membership->membership_no} for {$client->display_name}");

            return $membership;
        });
    }

    public function createTrial(Client $client, int $days): Membership
    {
        return DB::transaction(function () use ($client, $days) {
            $start = now()->startOfDay();
            $end = $start->copy()->addDays($days)->subDay();

            $plan = MembershipPlan::firstOrCreate(
                ['gym_id' => $client->gym_id, 'name' => 'Free Trial'],
                [
                    'duration_days' => $days,
                    'duration_label' => "{$days} days",
                    'price' => 0,
                    'discount' => 0,
                    'tax' => 0,
                    'final_amount' => 0,
                    'status' => 'active',
                ]
            );

            $membership = Membership::create([
                'gym_id' => $client->gym_id,
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'membership_no' => next_sequence(Membership::class, 'membership_no', 'MS-'),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => 'active',
                'amount' => 0,
                'discount' => 0,
                'tax' => 0,
                'final_amount' => 0,
                'payment_status' => 'free',
                'created_by' => auth()->id(),
                'notes' => 'Free trial — no payment required',
            ]);

            $membership->histories()->create([
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'action' => 'created',
                'previous_end_date' => null,
                'new_end_date' => $membership->end_date,
                'amount' => 0,
                'changed_by' => auth()->id(),
                'notes' => 'Free trial started',
            ]);

            audit_log('membership.trial_created', 'memberships', $membership->id, "Free trial started for {$client->display_name}");

            return $membership;
        });
    }

    public function renew(Client $client, MembershipPlan $plan, array $data = []): Membership
    {
        return DB::transaction(function () use ($client, $plan, $data) {
            $current = $client->activeMembership;
            $base = $current && $current->end_date >= now()->toDateString()
                ? Carbon::parse($current->end_date)->addDay()
                : now();

            $end = $base->copy()->addDays($plan->duration_days)->subDay();

            $membership = Membership::create([
                'gym_id' => $client->gym_id,
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'membership_no' => next_sequence(Membership::class, 'membership_no', 'MS-'),
                'start_date' => $base->toDateString(),
                'end_date' => $end->toDateString(),
                'status' => 'active',
                'amount' => $plan->price,
                'discount' => $data['discount'] ?? $plan->discount,
                'tax' => $plan->tax,
                'final_amount' => $plan->final_amount - ($data['discount'] ?? 0),
                'payment_status' => 'pending',
                'created_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            $membership->histories()->create([
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'action' => 'renewed',
                'previous_end_date' => $current?->end_date,
                'new_end_date' => $membership->end_date,
                'amount' => $membership->final_amount,
                'changed_by' => auth()->id(),
                'notes' => 'Membership renewed',
            ]);

            audit_log('membership.renewed', 'memberships', $membership->id, "Renewed membership for {$client->display_name}");

            return $membership;
        });
    }

    public function changePlan(Client $client, Membership $membership, MembershipPlan $plan, string $direction): Membership
    {
        return DB::transaction(function () use ($client, $membership, $plan, $direction) {
            $previousEnd = $membership->end_date;
            $base = Carbon::parse($membership->start_date);
            $newEnd = $base->copy()->addDays($plan->duration_days)->subDay();

            $membership->update([
                'plan_id' => $plan->id,
                'end_date' => $newEnd->toDateString(),
                'amount' => $plan->price,
                'discount' => $plan->discount,
                'tax' => $plan->tax,
                'final_amount' => $plan->final_amount,
            ]);

            $membership->histories()->create([
                'client_id' => $client->id,
                'plan_id' => $plan->id,
                'action' => $direction === 'upgrade' ? 'upgraded' : 'downgraded',
                'previous_end_date' => $previousEnd,
                'new_end_date' => $membership->end_date,
                'amount' => $plan->final_amount,
                'changed_by' => auth()->id(),
                'notes' => "Plan {$direction} to {$plan->name}",
            ]);

            audit_log("membership.{$direction}", 'memberships', $membership->id, "Membership {$direction} for {$client->display_name} to {$plan->name}");

            return $membership;
        });
    }

    public function extend(Membership $membership, int $days, ?string $notes = null): Membership
    {
        return DB::transaction(function () use ($membership, $days, $notes) {
            $previousEnd = $membership->end_date;
            $newEnd = Carbon::parse($membership->end_date)->addDays($days);

            $membership->update(['end_date' => $newEnd->toDateString()]);

            $membership->histories()->create([
                'client_id' => $membership->client_id,
                'plan_id' => $membership->plan_id,
                'action' => 'extended',
                'previous_end_date' => $previousEnd,
                'new_end_date' => $membership->end_date,
                'amount' => 0,
                'changed_by' => auth()->id(),
                'notes' => $notes ?? "Extended by {$days} days",
            ]);

            audit_log('membership.extended', 'memberships', $membership->id, "Extended membership by {$days} days");

            return $membership;
        });
    }

    public function setStatus(Membership $membership, string $status, ?string $notes = null): Membership
    {
        return DB::transaction(function () use ($membership, $status, $notes) {
            $membership->update(['status' => $status]);

            $membership->histories()->create([
                'client_id' => $membership->client_id,
                'plan_id' => $membership->plan_id,
                'action' => $status,
                'previous_end_date' => $membership->end_date,
                'new_end_date' => $membership->end_date,
                'amount' => 0,
                'changed_by' => auth()->id(),
                'notes' => $notes ?? "Status changed to {$status}",
            ]);

            audit_log("membership.status.{$status}", 'memberships', $membership->id, "Membership status changed to {$status}");

            return $membership;
        });
    }

    public function activateOnPayment(Membership $membership): void
    {
        if ($membership->status === 'upcoming') {
            $membership->update(['status' => 'active']);
        }

        if ($membership->payment_status === 'pending') {
            $membership->update(['payment_status' => 'paid']);
        }
    }

    public function processExpired(): int
    {
        $count = 0;

        Membership::where('status', 'active')
            ->where('end_date', '<', now()->toDateString())
            ->each(function (Membership $membership) use (&$count) {
                $membership->update(['status' => 'expired']);

                $membership->histories()->create([
                    'client_id' => $membership->client_id,
                    'plan_id' => $membership->plan_id,
                    'action' => 'expired',
                    'previous_end_date' => $membership->end_date,
                    'new_end_date' => $membership->end_date,
                    'amount' => 0,
                    'notes' => 'Membership expired automatically',
                ]);

                $count++;
            });

        return $count;
    }

    public function markTrialExpired(): int
    {
        return \App\Models\Trial::where('status', 'active')
            ->where('trial_end', '<', now()->toDateString())
            ->update(['status' => 'expired']);
    }
}
