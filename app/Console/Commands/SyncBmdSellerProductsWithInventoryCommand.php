<?php

namespace App\Console\Commands;

use Exception;
use App\Models\ProductSeller;
use App\Models\ScheduledTask;
use Illuminate\Console\Command;
use App\Models\ScheduledTaskLog;
use App\Bmd\Generals\GeneralHelper;
use App\Models\ScheduledTaskStatus;

class SyncBmdSellerProductsWithInventoryCommand extends Command
{
    public const scheduledDispatchTime = '04:36';


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncBmdSellerProductsWithInventory:Execute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync BMD seller-products size-availability quantites with inventory.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $executionStartTimeInSec = microtime(true);
        $resultMsg = 'Automated command execution in CLASS: SyncBmdSellerProductsWithInventoryCommand.\n';
        $isResultOk = false;


        $scheduledTask = ScheduledTask::where('command_signature', $this->signature)->get()[0];
        $availableStatus = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0];

        if ($scheduledTask->status_code != $availableStatus->code) {
            return -1;
        }

        $scheduledTask->status_code = ScheduledTaskStatus::where('name', 'PROCESSING')->get()[0]->code;
        $scheduledTask->save();



        try {

            ProductSeller::syncBmdSellerProductsSizeAvailabilityQuantitiesWithInventory();
            $resultMsg .= 'Executed METHOD: ProductSeller::syncBmdSellerProductsSizeAvailabilityQuantitiesWithInventory().\n';

            $isResultOk = true;
        } catch (Exception $e) {
            $eLogStr = GeneralHelper::extractErrorTrace($e);
            $resultMsg .= $eLogStr . '\n';
        }



        $executionEndTimeInSec = microtime(true);
        $executionPeriod = $executionEndTimeInSec - $executionStartTimeInSec;

        $scheduleTaskLog = new ScheduledTaskLog();
        $scheduleTaskLog->scheduled_task_id = $scheduledTask->id;
        $scheduleTaskLog->execution_period = $executionPeriod;
        $scheduleTaskLog->status_code = $isResultOk ? ScheduledTaskStatus::where('name', 'PROCESS_SUCCEEDED')->get()[0]->code : ScheduledTaskStatus::where('name', 'PROCESS_FAILED')->get()[0]->code;
        $scheduleTaskLog->is_successful = $isResultOk ? 1 : 0;
        $scheduleTaskLog->entire_process_logs = $resultMsg;
        $scheduleTaskLog->save();


        $scheduledTask->status_code = ScheduledTaskStatus::where('name', 'AVAILABLE')->get()[0]->code;
        $scheduledTask->save();

        return 0;
    }
}
