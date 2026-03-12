<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Leave;
use App\Enums\Leave\LeaveState;
use App\Repository\LeaveRepository;
use Symfony\Bundle\SecurityBundle\Security;

class LeaveProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security,
        private readonly LeaveRepository $leaveRepo,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Leave) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        $user = $this->security->getUser();
        $userName = $user?->getName() ?? 'System';
        $timestamp = (new \DateTime())->format('Y-m-d H:i');
        $newComment = $data->getComment();

        // Restore the original comment from DB before appending
        $original = $context['previous_data'] ?? null;
        $originalComment = $original instanceof Leave ? $original->getComment() : null;

        if ($newComment !== null && $newComment !== $originalComment) {
            // Determine action label from state
            $actionLabel = $this->getActionLabel($data, $original);
            $entry = "[{$timestamp}] {$userName}{$actionLabel}: {$newComment}";

            $data->setComment($originalComment);
            $data->appendComment($entry);
        } elseif ($newComment === null && $originalComment !== null) {
            // Don't clear existing comments
            $data->setComment($originalComment);
        }

        // On new leave creation, cancel any existing pending/approved leaves for the user
        if ($operation instanceof Post && $data->getUser()) {
            $this->cancelExistingLeaves($data);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function cancelExistingLeaves(Leave $newLeave): void
    {
        $existing = $this->leaveRepo->findBy([
            'user' => $newLeave->getUser(),
        ]);

        $timestamp = (new \DateTime())->format('Y-m-d H:i');

        foreach ($existing as $leave) {
            if (!in_array($leave->getState(), [LeaveState::Pending, LeaveState::Approved], true)) {
                continue;
            }

            $leave->setState(LeaveState::Cancelled);
            $leave->appendComment("[{$timestamp}] System — Cancelled: Superseded by new leave request");
            $this->persistProcessor->process($leave, new \ApiPlatform\Metadata\Patch(), [], []);
        }
    }

    private function getActionLabel(Leave $data, ?Leave $original): string
    {
        if ($original === null) {
            return ' — Requested';
        }

        $newState = $data->getState();
        $oldState = $original->getState();

        if ($newState !== $oldState) {
            return match ($newState) {
                LeaveState::Approved => ' — Approved',
                LeaveState::Declined => ' — Declined',
                default => '',
            };
        }

        return '';
    }
}
