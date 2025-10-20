<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Get all orders with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with('orderItems.product');

        // Filter by customer email if provided
        if ($request->has('customer_email') && $request->customer_email) {
            $query->where('customer_email', 'like', "%{$request->customer_email}%");
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Order by creation date (newest first)
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 10);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully'
        ]);
    }

    /**
     * Create a new order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'customer_email' => 'required|email',
            'customer_name' => 'required|string|max:255',
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount and validate stock
            $totalAmount = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product || !$product->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => "Product with ID {$item['product_id']} not found or inactive"
                    ], 400);
                }

                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for product: {$product->name}. Available: {$product->stock_quantity}"
                    ], 400);
                }

                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                ];
            }

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'customer_email' => $request->customer_email,
                'customer_name' => $request->customer_name,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Create order items and update stock
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);

                // Update product stock
                Product::where('id', $item['product_id'])
                    ->decrement('stock_quantity', $item['quantity']);
            }

            // Load relationships for response
            $order->load('orderItems.product');

            DB::commit();

            // Send order confirmation email
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order created successfully'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details
     */
    public function getOrder($orderNumber): JsonResponse
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('orderItems.product')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order retrieved successfully'
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, $orderNumber): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'data' => $order,
            'message' => 'Order status updated successfully'
        ]);
    }

    /**
     * Trigger email notification for order
     */
    private function triggerOrderEmail(Order $order): void
    {
        // This would typically send a message to a queue or call the email service
        // For now, we'll just log it
        \Log::info('Order email notification triggered', [
            'order_number' => $order->order_number,
            'customer_email' => $order->customer_email,
            'total_amount' => $order->total_amount
        ]);
    }
}
