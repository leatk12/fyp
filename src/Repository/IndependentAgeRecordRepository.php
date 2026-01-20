<?php

namespace App\Repository;

use App\Entity\IndependentAgeRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IndependentAgeRecord>
 */
class IndependentAgeRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndependentAgeRecord::class);
    }

    /**
     * Eager-load actions for index page (avoid N+1).
     *
     * @return IndependentAgeRecord[]
     */
    public function findAllWithActions(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.actions', 'a')
            ->addSelect('a')
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->addOrderBy('a.date', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByUuid(string $uuid): ?IndependentAgeRecord
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
