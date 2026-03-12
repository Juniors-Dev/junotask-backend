<?php

namespace App\Enums\Project;

enum Status: int
{
    case Working = 1;
    case Done = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Working => 'Working',
            self::Done => 'Done',
        };
    }
}
