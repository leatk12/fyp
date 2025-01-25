<?php

namespace App\DataFixtures;

use App\Entity\Recurrence;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class recurrences extends Fixture
{
    public function load(ObjectManager $manager): void
    {
// $product = new Product();
// $manager->persist($product);
        $entity = new Recurrence();
        $entity->setRecurring('None');
        $manager->persist($entity);

        $entity2 = new Recurrence();
        $entity2->setRecurring('Daily');
        $manager->persist($entity2);

        $entity3 = new Recurrence();
        $entity3->setRecurring('Weekly');
        $manager->persist($entity3);

        $entity4 = new Recurrence();
        $entity4->setRecurring('Monthly');
        $manager->persist($entity4);

        $entity5 = new Recurrence();
        $entity5->setRecurring('Yearly');
        $manager->persist($entity5);


        $manager->flush();
    }
}