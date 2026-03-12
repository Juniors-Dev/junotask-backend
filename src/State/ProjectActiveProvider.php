<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\ProjectRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ProjectActiveProvider implements ProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private ProjectRepository $repo
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new UnauthorizedHttpException('Bearer', 'Not authenticated');
        }

        $project = $this->repo->findActiveForUser($user);

        if (!$project) {
            throw new NotFoundHttpException('No active project found for user');
        }

        return $project;
    }
}
