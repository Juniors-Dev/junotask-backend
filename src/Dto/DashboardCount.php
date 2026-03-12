<?php

namespace App\Dto;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\State\DashboardProvider;

#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/dashboardCount'),
    ],
    provider: DashboardProvider::class,
)]
class DashboardCount
{
    public int $activeProjects = 0;
    public int $activeTasks = 0;
    public int $completedTasks = 0;
    public int $remainingLeaveDays = 0;
}
