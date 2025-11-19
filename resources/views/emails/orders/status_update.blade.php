@component('mail::message')
# Order Status Updated

Hello {{ $order->user->name }},

Your order **#{{ $order->order_number }}** status has been updated.

**New Status:** {{ ucfirst($status) }}

@component('mail::table')
| Product | Quantity | Price |
|---------|---------|-------|
@foreach ($order->orderItems as $item)
| {{ $item->productVariant->product->name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
@endcomponent

**Total Amount:** ${{ number_format($order->total_amount, 2) }}

Thanks,<br>
{{ config('app.name') }}
@endcomponent
