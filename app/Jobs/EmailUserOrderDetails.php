<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Http\BmdCacheObjects\OrderStatusCacheObject;

class EmailUserOrderDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;



    public $orderId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = Order::find($this->orderId);
        Mail::to($order->email)
            ->bcc(BmdGlobalConstants::EMAIL_FOR_ORDER_EMAILS_TRACKER)
            ->send(new OrderReceived($order));

        $order->status_code = OrderStatusCacheObject::getCodeByName('ORDER_DETAILS_EMAILED_TO_USER');
        $order->save();
    }
}
