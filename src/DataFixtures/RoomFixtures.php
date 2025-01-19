<?php

namespace App\DataFixtures;

use App\Entity\Room;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoomFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $entity = new Room();
        $entity->setRoomName('Conway Room');
        $entity->setCostIndividual(20.00);
        $entity->setCostPrivate(40.00);
        $entity->setCostCommunity(20.00);
        $entity->setMaxOccupancy(40);
        $entity->setOrganisation("Northmoor Community Association");
        $manager->persist($entity);

        $entity2 = new Room();
        $entity2->setRoomName('Brooks Room');
        $entity2->setCostIndividual(20.00);
        $entity2->setCostPrivate(40.00);
        $entity2->setCostCommunity(20.00);
        $entity2->setMaxOccupancy(40);
        $entity2->setOrganisation("Northmoor Community Association");
        $manager->persist($entity2);

        $entity3 = new Room();
        $entity3->setRoomName('IT Suite');
        $entity3->setCostIndividual(20.00);
        $entity3->setCostPrivate(40.00);
        $entity3->setCostCommunity(20.00);
        $entity3->setMaxOccupancy(40);
        $entity3->setOrganisation("Northmoor Community Association");
        $manager->persist($entity3);

        $entity4 = new Room();
        $entity4->setRoomName('Classroom');
        $entity4->setCostIndividual(20.00);
        $entity4->setCostPrivate(40.00);
        $entity4->setCostCommunity(20.00);
        $entity4->setMaxOccupancy(20);
        $entity4->setOrganisation("Northmoor Community Association");
        $manager->persist($entity4);

        $entity5 = new Room();
        $entity5->setRoomName('Small office');
        $entity5->setCostIndividual(20.00);
        $entity5->setCostPrivate(40.00);
        $entity5->setCostCommunity(20.00);
        $entity5->setMaxOccupancy(3);
        $entity5->setOrganisation("Northmoor Community Association");
        $manager->persist($entity5);

        $entity6 = new Room();
        $entity6->setRoomName('Hub Office');
        $entity6->setCostIndividual(20.00);
        $entity6->setCostPrivate(40.00);
        $entity6->setCostCommunity(20.00);
        $entity6->setMaxOccupancy(40);
        $entity6->setOrganisation("Northmoor Community Association");
        $manager->persist($entity6);

        $entity7 = new Room();
        $entity7->setRoomName('Hub Front Room');
        $entity7->setCostIndividual(20.00);
        $entity7->setCostPrivate(40.00);
        $entity7->setCostCommunity(20.00);
        $entity7->setMaxOccupancy(15);
        $entity7->setOrganisation("Northmoor Community Association");
        $manager->persist($entity7);

        $entity8 = new Room();
        $entity8->setRoomName('Hub Back Room ');
        $entity8->setCostIndividual(20.00);
        $entity8->setCostPrivate(40.00);
        $entity8->setCostCommunity(20.00);
        $entity8->setMaxOccupancy(7);
        $entity8->setOrganisation("Northmoor Community Association");
        $manager->persist($entity8);

        $manager->flush();
    }
}
