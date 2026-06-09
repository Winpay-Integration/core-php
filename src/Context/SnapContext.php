<?php

namespace Winpay\Core\Context;

use Winpay\Core\Services\CheckoutService;
use Winpay\Core\Services\CreditCardService;
use Winpay\Core\Services\EwalletService;
use Winpay\Core\Services\QrisService;
use Winpay\Core\Services\ReportService;
use Winpay\Core\Services\RetailService;
use Winpay\Core\Services\VirtualAccountService;

class SnapContext
{
    public function __construct(
        private readonly VirtualAccountService $vaService,
        private readonly QrisService $qrisService,
        private readonly EwalletService $ewalletService,
        private readonly CreditCardService $creditCardService,
        private readonly RetailService $retailService,
        private readonly ReportService $reportService,
        private readonly CheckoutService $checkoutService,
    ) {
    }

    public function va(): VirtualAccountSnap
    {
        return new VirtualAccountSnap($this->vaService);
    }

    public function qris(): QrisSnap
    {
        return new QrisSnap($this->qrisService);
    }

    public function ewallet(): EwalletSnap
    {
        return new EwalletSnap($this->ewalletService);
    }

    public function creditCard(): CreditCardSnap
    {
        return new CreditCardSnap($this->creditCardService);
    }

    public function retail(): RetailSnap
    {
        return new RetailSnap($this->retailService);
    }

    public function report(): ReportSnap
    {
        return new ReportSnap($this->reportService);
    }

    public function checkout(): CheckoutSnap
    {
        return new CheckoutSnap($this->checkoutService);
    }
}
