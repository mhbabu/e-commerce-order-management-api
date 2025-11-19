<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice</h1>
        <p>Order Number: {{ $order->order_number }}</p>
    </div>

    <div class="details">
        <p><strong>Customer:</strong> {{ $order->user->name }}</p>
        <p><strong>Email:</strong> {{ $order->user->email }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('Y-m-d') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Variant</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $item)
            <tr>
                <td>{{ $item->productVariant->product->name }}</td>
                <td>{{ $item->productVariant->variant_name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->price, 2) }}</td>
                <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total">Total Amount</td>
                <td class="total">${{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>