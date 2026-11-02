<?php

namespace App\Repository;

use App\Entity\HubRecord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<HubRecord> */
class HubRecordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HubRecord::class);
    }

    public function findOneByUuid(string $uuid): ?HubRecord
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }

    public function findOneByUuidWithActions(string $uuid): ?HubRecord
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.actions', 'a')
            ->addSelect('a')
            ->andWhere('r.uuid = :uuid')
            ->setParameter('uuid', $uuid)
            ->orderBy('a.date', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /** @return HubRecord[] */
    public function findPageSearchByFullName(
        int $page,
        int $limit,
        string $q
    ): array {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));

        return $this->searchQuery($q)
            ->orderBy('r.date', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countSearchByFullName(string $q): int
    {
        return (int) $this->searchQuery($q)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function searchQuery(string $q): QueryBuilder
    {
        $qb = $this->createQueryBuilder('r');
        $q = trim($q);

        if ($q !== '') {
            $qb->andWhere('LOCATE(:q, LOWER(r.fullName)) > 0')
                ->setParameter('q', mb_strtolower($q));
        }

        return $qb;
    }
}