<?php

namespace App\DataFixtures\Test;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserTestFixtures extends Fixture
{
    public const INA_USER = 'user_ina';

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('ina@free.fr');
        $user->setRoles(['ROLE_ADMIN']);

        $user->setPassword(
            $this->hasher->hashPassword($user, 'password')
        );

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::INA_USER, $user);
    }
}

