<?php

namespace App\Jobs;

use App\Models\Dispatch;
use App\Mail\EpBatchUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class EmailEpBatchUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $dispatch;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dispatchId)
    {
        $this->dispatch = Dispatch::find($dispatchId);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to(BmdGlobalConstants::EMAIL_RECIPIENT_FOR_EP_BATCH_UPDATES)
            ->cc(BmdGlobalConstants::EMAIL_FOR_ORDER_EMAILS_TRACKER)
            ->send(new EpBatchUpdated($this->dispatch));
    }
}
