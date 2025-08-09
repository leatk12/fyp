<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * Return bookings whose DATE is between start and end (inclusive),
     * ordered by date then time_start.
     */
    public function findBetweenDates(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.date >= :start')
            ->andWhere('b.date <= :end')
            ->setParameter('start', $start->format('Y-m-d')) // four-digit year
            ->setParameter('end',   $end->format('Y-m-d'))
            ->orderBy('b.date', 'ASC')
            ->addOrderBy('b.time_start', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
