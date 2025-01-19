<?php
namespace App\DataFixtures;

use App\Entity\BookingCustomer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $entity = new BookingCustomer();
        $entity->setOrganisation('testOrg');
        $entity->setRepresentative('testRepresentative');
        $entity->setPhone('07979090999');
        $entity->setemail('test@email.com');

        $manager->persist($entity);

        $manager->flush();
    }
}