<?php

namespace App\Repository;

use App\Entity\IndependentAgeAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IndependentAgeAction>
 */
class IndependentAgeActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndependentAgeAction::class);
    }

    public function findOneByUuid(string $uuid): ?IndependentAgeAction
    {
        return $this->findOneBy(['uuid' => $uuid]);
    }
}
