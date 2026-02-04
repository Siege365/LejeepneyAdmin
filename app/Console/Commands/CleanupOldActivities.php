<?php

namespace App\Console\Commands;

use App\Models\RecentActivity;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupOldActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete activities older than 90 days and guest activities older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of old activities...');

        // Delete user activities older than 90 days
        $userActivitiesDeleted = RecentActivity::whereNotNull('user_id')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->delete();

        $this->info("Deleted {$userActivitiesDeleted} user activities older than 90 days.");

        // Delete guest activities older than 7 days
        $guestActivitiesDeleted = RecentActivity::whereNull('user_id')
            ->where('created_at', '<', Carbon::now()->subDays(7))
            ->delete();

        $this->info("Deleted {$guestActivitiesDeleted} guest activities older than 7 days.");

        $totalDeleted = $userActivitiesDeleted + $guestActivitiesDeleted;
        $this->info("Total activities deleted: {$totalDeleted}");

        return Command::SUCCESS;
    }
}
