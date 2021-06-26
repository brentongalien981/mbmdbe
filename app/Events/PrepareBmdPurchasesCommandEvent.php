<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrepareBmdPurchasesCommandEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $commandData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($commandData)
    {
        $this->commandData = $commandData;
    }
}
