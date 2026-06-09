<?php

namespace Winpay\Core\Tests\Enums;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Enums\TransactionStatus;

class TransactionStatusTest extends TestCase
{
    public function testValues(): void
    {
        $this->assertSame('00', TransactionStatus::SUCCESS->value);
        $this->assertSame('01', TransactionStatus::INITIATED->value);
        $this->assertSame('02', TransactionStatus::PAYING->value);
        $this->assertSame('03', TransactionStatus::PENDING->value);
        $this->assertSame('04', TransactionStatus::REFUNDED->value);
        $this->assertSame('05', TransactionStatus::CANCELED->value);
        $this->assertSame('06', TransactionStatus::FAILED->value);
        $this->assertSame('07', TransactionStatus::NOT_FOUND->value);
    }

    public function testCount(): void
    {
        $this->assertCount(8, TransactionStatus::cases());
    }
}
