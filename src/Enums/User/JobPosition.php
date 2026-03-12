<?php

namespace App\Enums\User;

enum JobPosition: string
{
    case Frontend = 'FRONTEND';
    case Backend = 'BACKEND';
    case Fullstack = 'FULLSTACK';
    case Design = 'DESIGN';
}
