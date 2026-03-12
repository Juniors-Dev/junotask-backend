<?php

namespace App\Enums\Leave;

enum Type: string
{
    case Vacation = 'VACATION';
    case Sick = 'SICK';
    case Personal = 'PERSONAL';
}
