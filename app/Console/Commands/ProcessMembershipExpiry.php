<?php

namespace App\Console\Commands;

use App\Services\MembershipService;
use Illuminate\Console\Command;

class ProcessMembershipExpiry extends Command
{
    protected $signature = 'memberships:process-expired';

    protected $description = 'Mark expired memberships and trials as expired';

    public function handle(MembershipService $memberships): int
    {
        $expired = $memberships->processExpired();
        $trials = $memberships->markTrialExpired();

        $this->info("Marked {$expired} memberships and {$trials} trials as expired.");

        return self::SUCCESS;
    }
}
