<?php

namespace App\Enums\Task;

enum Status: string
{
    case Working = 'WORKING';
    case Review = 'REVIEW';
    case Done = 'DONE';
}
