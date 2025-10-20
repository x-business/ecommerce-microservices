<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .order-details {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            font-size: 18px;
            color: #28a745;
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #28a745;
        }
        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank You for Your Order!</h1>
        <p>Order Number: <strong>{{ $order->order_number }}</strong></p>
    </div>

    <div class="order-details">
        <h2>Order Details</h2>
        
        <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
        <p><strong>Email:</strong> {{ $order->customer_email }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y g:i A') }}</p>
        <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
        
        <h3>Shipping Address:</h3>
        <p>{{ $order->shipping_address }}</p>
        
        @if($order->billing_address)
        <h3>Billing Address:</h3>
        <p>{{ $order->billing_address }}</p>
        @endif
        
        @if($order->payment_method)
        <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
        @endif
        
        @if($order->notes)
        <p><strong>Notes:</strong> {{ $order->notes }}</p>
        @endif
    </div>

    <div class="order-details">
        <h2>Order Items</h2>
        
        @foreach($order->orderItems as $item)
        <div class="order-item">
            <h4>{{ $item->product->name }}</h4>
            <p>{{ $item->product->description }}</p>
            <p><strong>SKU:</strong> {{ $item->product->sku }}</p>
            <p><strong>Quantity:</strong> {{ $item->quantity }}</p>
            <p><strong>Unit Price:</strong> ${{ number_format($item->unit_price, 2) }}</p>
            <p><strong>Total:</strong> ${{ number_format($item->total_price, 2) }}</p>
        </div>
        @endforeach
        
        <div class="total">
            <strong>Order Total: ${{ number_format($order->total_amount, 2) }}</strong>
        </div>
    </div>

    <div class="footer">
        <p>Thank you for shopping with us!</p>
        <p>If you have any questions about your order, please contact our customer service.</p>
    </div>
</body>
</html>
