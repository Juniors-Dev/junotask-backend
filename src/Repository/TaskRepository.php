<?php

namespace App\Repository;

use App\Entity\Task;
use App\Enums\Task\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function countActive(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.status IN (:statuses)')
            ->setParameter('statuses', [Status::Working, Status::Review])
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countCompleted(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.status = :status')
            ->setParameter('status', Status::Done)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
