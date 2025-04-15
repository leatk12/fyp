<?php

namespace App\DataFixtures;

use App\Entity\UserType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserTypeFixture extends Fixture implements FixtureGroupInterface
{
    public const ADMIN_REFERENCE = 'user_type_admin';
    public const USER_REFERENCE = 'user_type_user';

    public static function getGroups(): array
    {
        return ['user_type'];
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new UserType();
        $admin->setUserType('Admin');
        $manager->persist($admin);

        $user = new UserType();
        $user->setUserType('User');
        $manager->persist($user);

        // Save references for later use in UsersFixture
        $this->addReference(self::ADMIN_REFERENCE, $admin);
        $this->addReference(self::USER_REFERENCE, $user);

        $manager->flush();
    }
}
