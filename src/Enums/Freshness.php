<?php

declare(strict_types=1);

namespace GraystackIT\BraveSearch\Enums;

enum Freshness: string
{
    case PastDay   = 'pd';
    case PastWeek  = 'pw';
    case PastMonth = 'pm';
    case PastYear  = 'py';
}
