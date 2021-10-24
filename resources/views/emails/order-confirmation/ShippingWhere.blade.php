<div>
    <b>Where</b>
    <p>
        {{ $order->street }}<br />
        {{ $order->city . ', ' . $order->province  }}<br>
        {{ $order->country . ', ' . $order->postal_code  }}<br>
    </p>
</div>