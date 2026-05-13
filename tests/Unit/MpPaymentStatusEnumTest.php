<?php

namespace Tests\Unit;

use App\Enums\MpPaymentStatusEnum;
use App\Enums\PaymentStatusEnum;
use PHPUnit\Framework\TestCase;

class MpPaymentStatusEnumTest extends TestCase
{
    public function test_approved_maps_to_paid(): void
    {
        $this->assertSame(
            PaymentStatusEnum::PAID,
            MpPaymentStatusEnum::APPROVED->toPaymentStatus()
        );
    }

    public function test_authorized_maps_to_paid(): void
    {
        $this->assertSame(
            PaymentStatusEnum::PAID,
            MpPaymentStatusEnum::AUTHORIZED->toPaymentStatus()
        );
    }

    public function test_pending_states_map_to_pending(): void
    {
        $this->assertSame(PaymentStatusEnum::PENDING, MpPaymentStatusEnum::PENDING->toPaymentStatus());
        $this->assertSame(PaymentStatusEnum::PENDING, MpPaymentStatusEnum::IN_PROCESS->toPaymentStatus());
        $this->assertSame(PaymentStatusEnum::PENDING, MpPaymentStatusEnum::IN_MEDIATION->toPaymentStatus());
    }

    public function test_negative_states_map_to_failed(): void
    {
        $this->assertSame(PaymentStatusEnum::FAILED, MpPaymentStatusEnum::REJECTED->toPaymentStatus());
        $this->assertSame(PaymentStatusEnum::FAILED, MpPaymentStatusEnum::CANCELLED->toPaymentStatus());
        $this->assertSame(PaymentStatusEnum::FAILED, MpPaymentStatusEnum::CHARGED_BACK->toPaymentStatus());
    }

    public function test_refunded_maps_to_refunded(): void
    {
        $this->assertSame(
            PaymentStatusEnum::REFUNDED,
            MpPaymentStatusEnum::REFUNDED->toPaymentStatus()
        );
    }
}
