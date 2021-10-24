<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Bmd\Constants\BmdGlobalConstants;
use App\Models\DispatchStatus;
use Illuminate\Contracts\Queue\ShouldQueue;

class EpBatchUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $dispatch;
    public $subject;
    public $dispatchStatusName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($dispatch)
    {
        $this->dispatch = $dispatch;
        $this->dispatchStatusName = DispatchStatus::where('code', $dispatch->status_code)->get()[0]->name;
        $this->subject = "EP Batch Update - $this->dispatchStatusName - Dispatch ID: $dispatch->id";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(BmdGlobalConstants::EMAIL_SENDER_FOR_EP_BATCH_UPDATES)
            ->subject($this->subject)
            ->view('emails.dispatches.EpBatchUpdated');
    }
}
