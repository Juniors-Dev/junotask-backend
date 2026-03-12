<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Enums\Leave\LeaveState;
use App\Repository\LeaveRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Metadata\HttpOperation;

class LeaveActiveProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private LeaveRepository $repo,
        private RequestStack $requestStack,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'Not authenticated');
        }

        if ($operation instanceof HttpOperation && $operation->getUriTemplate() === '/bystatus') {
            $request = $this->requestStack->getCurrentRequest();
            $statusValue = $request?->query->getInt('status');

            $state = LeaveState::tryFrom($statusValue);

            if (!$state) {
                throw new NotFoundHttpException('Status parameter is required');
            }

            return $this->repo->filterLeavesByStatus($state);
        }

        $leave = $this->repo->findActiveForUser($user);

        if (!$leave) {
            throw new NotFoundHttpException('No active leave found for user');
        }

        return $leave;
    }
}
