<?php

namespace App\Repository;

use App\Entity\Leave;
use App\Enums\Leave\LeaveState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Leave>
 */
class LeaveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Leave::class);
    }

    /** @return Leave[] */
    public function filterLeavesByStatus(LeaveState $state): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.state = :state')
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }

    public function findActiveForUser($user): ?Leave
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->setParameter('user', $user)
            ->andWhere('l.state = :state')
            ->setParameter('state', LeaveState::Approved)
            ->andWhere('l.startDate <= :today')
            ->andWhere('l.endDate >= :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getRemainingLeaveDaysForUser($user): int
    {
        $leaves = $this->createQueryBuilder('l')
            ->andWhere('l.user = :user')
            ->setParameter('user', $user)
            ->andWhere('l.state = :state')
            ->setParameter('state', LeaveState::Approved)
            ->andWhere('l.endDate >= :today')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getResult();

        $days = 0;
        foreach ($leaves as $leave) {
            $start = max($leave->getStartDate(), new \DateTime());
            $diff = $start->diff($leave->getEndDate());
            $days += $diff->days + 1;
        }

        return $days;
    }

    //    /**
    //     * @return Leave[] Returns an array of Leave objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Leave
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
