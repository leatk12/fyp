<?php

namespace App\DataFixtures;

use App\Entity\Manager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ManagerFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $entity = new Manager();
        $entity->setEmail('manager@example.com');


        $entity->setRoles(['ROLE_MANAGER', 'ROLE_ADMIN']);

        // Hash the password
        $hashedPassword = $this->passwordHasher->hashPassword($entity, '123456');
        $entity->setPassword($hashedPassword);

        $manager->persist($entity);
        $manager->flush();
    }
}
