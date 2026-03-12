<?php

namespace App\Repository;

use App\Entity\Project;
use App\Enums\Project\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findActiveForUser($user): ?Project
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->andWhere('p.status = :status')
            ->setParameter('status', Status::Working)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countActive(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->setParameter('status', Status::Working)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
