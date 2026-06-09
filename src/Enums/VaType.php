<?php

namespace Winpay\Core\Enums;

enum VaType: string
{
    case ONE_OFF = 'c';
    case OPEN_RECURRING = 'o';
    case CLOSE_RECURRING = 'r';
}
