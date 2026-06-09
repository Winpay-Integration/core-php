<?php

namespace Winpay\Core\Tests\Context;

use PHPUnit\Framework\TestCase;
use Winpay\Core\Context\CheckoutSnap;
use Winpay\Core\Context\CreditCardSnap;
use Winpay\Core\Context\EwalletSnap;
use Winpay\Core\Context\QrisSnap;
use Winpay\Core\Context\ReportSnap;
use Winpay\Core\Context\RetailSnap;
use Winpay\Core\Context\SnapContext;
use Winpay\Core\Context\VirtualAccountSnap;
use Winpay\Core\Contracts\HttpClientInterface;
use Winpay\Core\Models\WinpayResponse;
use Winpay\Core\Services\CheckoutService;
use Winpay\Core\Services\CreditCardService;
use Winpay\Core\Services\EwalletService;
use Winpay\Core\Services\QrisService;
use Winpay\Core\Services\ReportService;
use Winpay\Core\Services\RetailService;
use Winpay\Core\Services\VirtualAccountService;

class ContextTest extends TestCase
{
    private SnapContext $snap;

    protected function setUp(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $http->method('request')->willReturn(new WinpayResponse('00', 'OK', 200));

        $this->snap = new SnapContext(
            vaService: new VirtualAccountService($http),
            qrisService: new QrisService($http),
            ewalletService: new EwalletService($http),
            creditCardService: new CreditCardService($http),
            retailService: new RetailService($http),
            reportService: new ReportService($http),
            checkoutService: new CheckoutService($http),
        );
    }

    public function testReturnsVirtualAccountSnap(): void
    {
        $this->assertInstanceOf(VirtualAccountSnap::class, $this->snap->va());
    }

    public function testReturnsQrisSnap(): void
    {
        $this->assertInstanceOf(QrisSnap::class, $this->snap->qris());
    }

    public function testReturnsEwalletSnap(): void
    {
        $this->assertInstanceOf(EwalletSnap::class, $this->snap->ewallet());
    }

    public function testReturnsCreditCardSnap(): void
    {
        $this->assertInstanceOf(CreditCardSnap::class, $this->snap->creditCard());
    }

    public function testReturnsReportSnap(): void
    {
        $this->assertInstanceOf(ReportSnap::class, $this->snap->report());
    }

    public function testVaMethods(): void
    {
        $va = $this->snap->va();

        $this->assertInstanceOf(WinpayResponse::class, $va->create([]));
        $this->assertInstanceOf(WinpayResponse::class, $va->inquiry([]));
        $this->assertInstanceOf(WinpayResponse::class, $va->status([]));
        $this->assertInstanceOf(WinpayResponse::class, $va->delete([]));
    }

    public function testQrisMethods(): void
    {
        $qris = $this->snap->qris();

        $this->assertInstanceOf(WinpayResponse::class, $qris->create([]));
        $this->assertInstanceOf(WinpayResponse::class, $qris->status([]));
        $this->assertInstanceOf(WinpayResponse::class, $qris->cancel([]));
    }

    public function testEwalletMethods(): void
    {
        $ewallet = $this->snap->ewallet();

        $this->assertInstanceOf(WinpayResponse::class, $ewallet->create([]));
        $this->assertInstanceOf(WinpayResponse::class, $ewallet->status([]));
        $this->assertInstanceOf(WinpayResponse::class, $ewallet->cancel([]));
    }

    public function testCreditCardMethods(): void
    {
        $cc = $this->snap->creditCard();

        $this->assertInstanceOf(WinpayResponse::class, $cc->create([]));
        $this->assertInstanceOf(WinpayResponse::class, $cc->status([]));
        $this->assertInstanceOf(WinpayResponse::class, $cc->cancel([]));
    }

    public function testReturnsRetailSnap(): void
    {
        $this->assertInstanceOf(RetailSnap::class, $this->snap->retail());
    }

    public function testRetailMethods(): void
    {
        $retail = $this->snap->retail();

        $this->assertInstanceOf(WinpayResponse::class, $retail->create([]));
        $this->assertInstanceOf(WinpayResponse::class, $retail->status([]));
        $this->assertInstanceOf(WinpayResponse::class, $retail->cancel([]));
    }

    public function testReportMethods(): void
    {
        $report = $this->snap->report();

        $this->assertInstanceOf(WinpayResponse::class, $report->balance([]));
        $this->assertInstanceOf(WinpayResponse::class, $report->history([]));
        $this->assertInstanceOf(WinpayResponse::class, $report->statement([]));
    }

    public function testReturnsCheckoutSnap(): void
    {
        $this->assertInstanceOf(CheckoutSnap::class, $this->snap->checkout());
    }

    public function testCheckoutMethods(): void
    {
        $checkout = $this->snap->checkout();

        $this->assertInstanceOf(WinpayResponse::class, $checkout->create(['customer' => ['name' => 'Test']]));
        $this->assertInstanceOf(WinpayResponse::class, $checkout->find('inv-001'));
        $this->assertInstanceOf(WinpayResponse::class, $checkout->findByRef('REF-001'));
        $this->assertInstanceOf(WinpayResponse::class, $checkout->update('inv-001', ['customer' => ['name' => 'Updated']]));
        $this->assertInstanceOf(WinpayResponse::class, $checkout->delete('inv-001'));
        $this->assertInstanceOf(WinpayResponse::class, $checkout->deleteByRef('REF-001'));
    }
}
