<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Enums;

enum SafeSearch: string
{
    case Off      = 'off';
    case Moderate = 'moderate';
    case Strict   = 'strict';
}
