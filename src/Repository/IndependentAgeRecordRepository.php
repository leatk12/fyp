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

    public function findPage(int $page, int $limit): array
    {
        return $this->findPageSearchByFullName($page, $limit, '');
    }

    public function countAllRecords(): int
    {
        return $this->countSearchByFullName('');
    }

    public function findPageSearchByFullName(int $page, int $limit, string $q): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        if ($q !== '') {
            $qb->andWhere('LOWER(r.fullName) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countSearchByFullName(string $q): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        if ($q !== '') {
            $qb->andWhere('LOWER(r.fullName) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}