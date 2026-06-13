<?php

namespace Tests\Feature;

use App\Enums\PaymentStatusEnum;
use App\Mail\PendingPaymentReminderMail;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PendingPaymentReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_reminder_is_sent_after_twelve_hours(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(hoursAgo: 13);

        $this->artisan('orders:payment-reminders')->assertSuccessful();

        Mail::assertQueued(PendingPaymentReminderMail::class, fn (PendingPaymentReminderMail $mail) => $mail->order->is($order) && $mail->reminder === 1);

        $this->assertNotNull($order->fresh()->payment_reminder_1_sent_at);
        $this->assertNull($order->fresh()->payment_reminder_2_sent_at);
    }

    public function test_second_reminder_is_sent_after_twenty_four_hours(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(hoursAgo: 25);

        $this->artisan('orders:payment-reminders')->assertSuccessful();

        Mail::assertQueued(PendingPaymentReminderMail::class, fn (PendingPaymentReminderMail $mail) => $mail->reminder === 1);
        Mail::assertQueued(PendingPaymentReminderMail::class, fn (PendingPaymentReminderMail $mail) => $mail->reminder === 2);

        $fresh = $order->fresh();
        $this->assertNotNull($fresh->payment_reminder_1_sent_at);
        $this->assertNotNull($fresh->payment_reminder_2_sent_at);
    }

    public function test_recent_order_receives_no_reminder(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(hoursAgo: 2);

        $this->artisan('orders:payment-reminders')->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertNull($order->fresh()->payment_reminder_1_sent_at);
    }

    public function test_non_pending_order_is_skipped(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(hoursAgo: 13, status: PaymentStatusEnum::PAID);

        $this->artisan('orders:payment-reminders')->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertNull($order->fresh()->payment_reminder_1_sent_at);
    }

    public function test_reminder_is_not_resent(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(hoursAgo: 13);
        $order->forceFill(['payment_reminder_1_sent_at' => now()])->saveQuietly();

        $this->artisan('orders:payment-reminders')->assertSuccessful();

        Mail::assertNotQueued(PendingPaymentReminderMail::class, fn (PendingPaymentReminderMail $mail) => $mail->reminder === 1);
    }

    public function test_order_too_old_is_skipped(): void
    {
        Mail::fake();
        $order = $this->pendingOrder(hoursAgo: 80);

        $this->artisan('orders:payment-reminders')->assertSuccessful();

        Mail::assertNothingQueued();
        $this->assertNull($order->fresh()->payment_reminder_1_sent_at);
    }

    private function pendingOrder(int $hoursAgo, PaymentStatusEnum $status = PaymentStatusEnum::PENDING): Order
    {
        $order = Order::create([
            'email' => 'comprador@example.com',
            'postal_code' => '7607',
            'subtotal' => 10000,
            'total' => 10000,
            'shipping_cost' => 0,
            'discount_amount' => 0,
            'payment_status' => $status,
        ]);

        $order->forceFill(['created_at' => now()->subHours($hoursAgo)])->saveQuietly();

        return $order;
    }
}
