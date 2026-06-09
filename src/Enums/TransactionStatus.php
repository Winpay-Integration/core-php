<?php

namespace Winpay\Core\Enums;

enum TransactionStatus: string
{
    case SUCCESS = '00';
    case INITIATED = '01';
    case PAYING = '02';
    case PENDING = '03';
    case REFUNDED = '04';
    case CANCELED = '05';
    case FAILED = '06';
    case NOT_FOUND = '07';
}
