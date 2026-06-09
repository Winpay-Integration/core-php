<?php

namespace Winpay\Core\Enums;

enum EwalletChannel: string
{
    case SPEEDCASH = 'SC';
    case OVO = 'OVO';
    case DANA = 'DANA';
    case SHOPEEPAY = 'SPAY';
    case ASTRAPAY = 'ASTRA';
}
