<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            $order = Order::with('orderItems.product')->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Send the email
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $order->customer_email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order confirmation email sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $request->order_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send order confirmation email by order number
     */
    public function sendOrderConfirmationByNumber(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        try {
            $order = Order::where('order_number', $request->order_number)
                ->with('orderItems.product')
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Send the email
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

            Log::info('Order confirmation email sent by order number', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'customer_email' => $order->customer_email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order confirmation email sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email by order number', [
                'order_number' => $request->order_number,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send custom email notification
     */
    public function sendCustomNotification(Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        try {
            Mail::raw($request->message, function ($mail) use ($request) {
                $mail->to($request->to)
                     ->subject($request->subject);
            });

            Log::info('Custom email notification sent', [
                'to' => $request->to,
                'subject' => $request->subject
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email notification sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send custom email notification', [
                'to' => $request->to,
                'subject' => $request->subject,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }
}
