<?php

namespace Winpay\Core\Enums;

enum VaChannel: string
{
    case BRI = 'BRI';
    case BNI = 'BNI';
    case MANDIRI = 'MANDIRI';
    case MANDIRI_PC = 'MANDIRIPC';
    case PERMATA = 'PERMATA';
    case BSI = 'BSI';
    case MUAMALAT = 'MUAMALAT';
    case BCA = 'BCA';
    case CIMB = 'CIMB';
    case SINARMAS = 'SINARMAS';
    case BNC = 'BNC';
    case MAYBANK = 'MAYBANK';
}
