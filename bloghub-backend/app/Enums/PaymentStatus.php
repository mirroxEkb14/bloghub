<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'Pending';
    case Completed = 'Completed';
    case Failed = 'Failed';
}
