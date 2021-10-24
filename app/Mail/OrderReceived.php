<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Bmd\Constants\BmdGlobalConstants;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderReceived extends Mailable
{
    use Queueable, SerializesModels;



    public $order;
    public $subject = 'Thank You - We\'ve Received Your Order';
    public $extraData;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;

        $total = $order->charged_subtotal + $order->charged_shipping_fee + $order->charged_tax;

        $latestDeliveryDays = $order->projected_total_delivery_days;
        $earliestDeliveryDays = $latestDeliveryDays - BmdGlobalConstants::PAYMENT_TO_FUNDS_PERIOD - BmdGlobalConstants::ORDER_PROCESSING_PERIOD;
        $arrivesInMsg = 'Arrives in ' . $earliestDeliveryDays . '-' . $latestDeliveryDays . ' Business Days';

        $this->extraData = [
            'orderLink' => env('APP_BMDFE_URL') . '/order?id=' . $order->id, // BMD-ON-ITER: TEST-ITER-001: Make sure this links to the frondend.
            'total' => $total,
            'arrivesInMsg' => $arrivesInMsg,
            'earliestDeliveryDateInStr' => Order::getReadableDate($order->earliest_delivery_date),
            'latestDeliveryDateInStr' => Order::getReadableDate($order->latest_delivery_date)
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // BMD-ON-STAGING: bcc the appropriate @bmd.com email.
        return $this->from(BmdGlobalConstants::EMAIL_SENDER_FOR_ORDER_RECEIVED)
            ->subject($this->subject)
            ->markdown('emails.order-confirmation.OrderReceived');
    }
}
