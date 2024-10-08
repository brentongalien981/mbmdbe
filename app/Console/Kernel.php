<?php

namespace App\Console;

use App\Console\Commands\ChangeTestRoleDescriptionCommand;
use App\Console\Commands\PrepareBmdPurchasesCommand;
use App\Console\Commands\ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsCommand;
use App\Console\Commands\SyncBmdSellerProductsWithInventoryCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command(ChangeTestRoleDescriptionCommand::class)->everyMinute();
        $schedule->command(PrepareBmdPurchasesCommand::class)->dailyAt(PrepareBmdPurchasesCommand::scheduledDispatchTime);
        $schedule->command(ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsCommand::class)->dailyAt(ResetSizeAvailabilityQuantitiesOfNonBmdSellerProductsCommand::scheduledDispatchTime);
        $schedule->command(SyncBmdSellerProductsWithInventoryCommand::class)->dailyAt(SyncBmdSellerProductsWithInventoryCommand::scheduledDispatchTime);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
