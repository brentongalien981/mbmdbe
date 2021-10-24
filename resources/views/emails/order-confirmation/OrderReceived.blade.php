@component('mail::message')

<h1>Order Confirmation</h1>
<br><br>

@component('emails.order-confirmation.Greetings', ['order' => $order ])@endcomponent
<br><br>


<h2>Order ID: <a href="{{ $extraData['orderLink'] }}">{{ $order->id }}</a></h2>


@component('mail::table')
| | | |||
|-|-|-|-:|-:|
| |||Sub-total|${{ $order->charged_subtotal }}|
| |||Shipping|${{ $order->charged_shipping_fee }}|
| |||Tax|${{ $order->charged_tax }}|
| |||Total|${{ $extraData['total'] }}|
@endcomponent



@component('mail::table')
| | | |
|-|-:|-:|
@foreach($order->orderItems as $i)
| {{ $i->product->name }} <br> ${{ $i->price }} <br> x{{ $i->quantity }} ||${{ $i->price * $i->quantity }}|
@endforeach
@endcomponent
@component('mail::table')
| | | |
|-|-|-|
@endcomponent




<h2>Shipping</h2>
@component('emails.order-confirmation.ShippingTo', ['order' => $order ])@endcomponent
@component('emails.order-confirmation.ShippingWhere', ['order' => $order ])@endcomponent
@component('emails.order-confirmation.ShippingWhen', ['extraData' => $extraData ])@endcomponent



@endcomponent