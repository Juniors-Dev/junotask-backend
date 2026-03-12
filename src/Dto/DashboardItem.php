<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\DashboardProvider;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/dashboard'),
    ],
    provider: DashboardProvider::class,
)]
class DashboardItem
{
    public int $id;
    public string $name;
    public ?string $jobPosition;
    public ?array $activeProject = null;
    public ?array $activeLeave = null;
}
