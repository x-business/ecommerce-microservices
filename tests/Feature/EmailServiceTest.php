<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake mail to prevent actual emails from being sent
        Mail::fake();
    }

    /**
     * Test sending order confirmation email by order ID
     */
    public function test_can_send_order_confirmation_email_by_id(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->postJson('/api/email/order-confirmation', [
            'order_id' => $order->id
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order confirmation email sent successfully'
                ]);

        // Verify email was sent
        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }

    /**
     * Test sending order confirmation email by order number
     */
    public function test_can_send_order_confirmation_email_by_number(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(2)->create(['order_id' => $order->id]);

        $response = $this->postJson('/api/email/order-confirmation-by-number', [
            'order_number' => $order->order_number
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Order confirmation email sent successfully'
                ]);

        // Verify email was sent
        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });
    }

    /**
     * Test sending custom email notification
     */
    public function test_can_send_custom_email_notification(): void
    {
        $emailData = [
            'to' => $this->faker->email,
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];

        $response = $this->postJson('/api/email/custom-notification', $emailData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Email notification sent successfully'
                ]);

        // Verify email was sent
        Mail::assertSent(\Illuminate\Mail\Mailable::class, function ($mail) use ($emailData) {
            return $mail->hasTo($emailData['to']);
        });
    }

    /**
     * Test sending email for non-existent order
     */
    public function test_cannot_send_email_for_non_existent_order(): void
    {
        $response = $this->postJson('/api/email/order-confirmation', [
            'order_id' => 999999
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Order not found'
                ]);

        // Verify no email was sent
        Mail::assertNothingSent();
    }

    /**
     * Test sending email for non-existent order number
     */
    public function test_cannot_send_email_for_non_existent_order_number(): void
    {
        $response = $this->postJson('/api/email/order-confirmation-by-number', [
            'order_number' => 'NONEXISTENT'
        ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Order not found'
                ]);

        // Verify no email was sent
        Mail::assertNothingSent();
    }

    /**
     * Test email validation for order confirmation
     */
    public function test_order_confirmation_email_validation(): void
    {
        $response = $this->postJson('/api/email/order-confirmation', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['order_id']);
    }

    /**
     * Test email validation for order confirmation by number
     */
    public function test_order_confirmation_by_number_email_validation(): void
    {
        $response = $this->postJson('/api/email/order-confirmation-by-number', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['order_number']);
    }

    /**
     * Test custom email validation
     */
    public function test_custom_email_validation(): void
    {
        $response = $this->postJson('/api/email/custom-notification', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['to', 'subject', 'message']);
    }

    /**
     * Test custom email with invalid email address
     */
    public function test_custom_email_with_invalid_email(): void
    {
        $response = $this->postJson('/api/email/custom-notification', [
            'to' => 'invalid-email',
            'subject' => 'Test Subject',
            'message' => 'Test message'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['to']);
    }

    /**
     * Test order confirmation email contains correct data
     */
    public function test_order_confirmation_email_contains_correct_data(): void
    {
        $order = Order::factory()->create([
            'customer_email' => 'test@example.com',
            'customer_name' => 'John Doe',
            'total_amount' => 99.99
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 2,
            'unit_price' => 49.99,
            'total_price' => 99.98
        ]);

        $response = $this->postJson('/api/email/order-confirmation', [
            'order_id' => $order->id
        ]);

        $response->assertStatus(200);

        // Verify email was sent with correct data
        Mail::assertSent(\App\Mail\OrderConfirmationMail::class, function ($mail) use ($order) {
            return $mail->order->customer_email === 'test@example.com' &&
                   $mail->order->customer_name === 'John Doe' &&
                   $mail->order->total_amount === 99.99;
        });
    }
}
