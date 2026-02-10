<?php

namespace App\Repository;

use App\Entity\IndependentAgeRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IndependentAgeRecord>
 *
 * @method IndependentAgeRecord|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndependentAgeRecord|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndependentAgeRecord[]    findAll()
 * @method IndependentAgeRecord[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndependentAgeRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndependentAgeRecord::class);
    }

    public function findOneByUuid(string $uuid): ?IndependentAgeRecord
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Fetch a single record AND its actions for the modal/detail view.
     * This is safe because it’s bounded to one parent record.
     */
    public function findOneByUuidWithActions(string $uuid): ?IndependentAgeRecord
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.actions', 'a')
            ->addSelect('a')
            ->andWhere('r.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->addOrderBy('a.date', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Paginated records list WITHOUT joining actions.
     * Prevents the “load everything + join everything” 120s timeout.
     */
    public function findPage(int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        return $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAllRecords(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
