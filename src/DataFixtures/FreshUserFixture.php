<?php

namespace App\DataFixtures;

use App\Entity\Users;
use App\Entity\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FreshUserFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        /** @var UserType $adminType */
        $adminType = $this->getReference(UserTypeFixture::ADMIN_REFERENCE, UserType::class);


        $admin = new Users();
        $admin->setEmail('manager@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, '123456'));
        $admin->setUserType($adminType);

        $manager->persist($admin);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserTypeFixture::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['fresh'];
    }
}
