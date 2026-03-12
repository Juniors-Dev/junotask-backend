<?php

namespace App\State;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\DashboardItem;
use App\Repository\LeaveRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Dto\DashboardCount;
use Symfony\Bundle\SecurityBundle\Security;

class DashboardProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepo,
        private ProjectRepository $projectRepo,
        private LeaveRepository $leaveRepo,
        private TaskRepository $taskRepo,
        private Security $security,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        if ($operation instanceof HttpOperation && $operation->getUriTemplate() === '/dashboardCount') {
            return $this->provideCount();
        }
        $users = $this->userRepo->findAll();
        $items = [];

        foreach ($users as $user) {
            $item = new DashboardItem();
            $item->id = $user->getId();
            $item->name = $user->getName();
            $item->jobPosition = $user->getJobPosition()?->value;

            $activeProject = $this->projectRepo->findActiveForUser($user);
            if ($activeProject) {
                $item->activeProject = [
                    'id' => $activeProject->getId(),
                    'name' => $activeProject->getName(),
                    'statusLabel' => $activeProject->getStatusLabel(),
                ];
            }

            $activeLeave = $this->leaveRepo->findActiveForUser($user);
            if ($activeLeave) {
                $item->activeLeave = [
                    'id' => $activeLeave->getId(),
                    'type' => $activeLeave->getType()->value,
                    'stateLabel' => $activeLeave->getStateLabel(),
                    'startDate' => $activeLeave->getStartDate()->format('Y-m-d'),
                    'endDate' => $activeLeave->getEndDate()->format('Y-m-d'),
                ];
            }

            $items[] = $item;
        }

        return $items;
    }

    private function provideCount(): array
    {
        $item = new DashboardCount();
        $item->activeProjects = $this->projectRepo->countActive();
        $item->activeTasks = $this->taskRepo->countActive();
        $item->completedTasks = $this->taskRepo->countCompleted();

        $user = $this->security->getUser();
        if ($user) {
            $item->remainingLeaveDays = $this->leaveRepo->getRemainingLeaveDaysForUser($user);
        }

        return [$item];
    }
}
