<?php

namespace App\Enums\Leave;

enum LeaveState: int
{
    case Pending = 1;
    case Approved = 2;
    case Declined = 3;
    case Cancelled = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
        };
    }
}
