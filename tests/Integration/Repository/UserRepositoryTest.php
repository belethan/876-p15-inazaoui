<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(User::class);

        // Nettoyage de la table user avant chaque test
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testFindAllGuests(): void
    {
        // --- Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setNom('Admin');
        $admin->setPrenom('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('password');
        $this->em->persist($admin);

        // --- User 1
        $user1 = new User();
        $user1->setEmail('user1@test.com');
        $user1->setNom('User');
        $user1->setPrenom('One');
        $user1->setRoles(['ROLE_USER']);
        $user1->setPassword('password');
        $this->em->persist($user1);

        // --- User 2
        $user2 = new User();
        $user2->setEmail('user2@test.com');
        $user2->setNom('User');
        $user2->setPrenom('Two');
        $user2->setRoles(['ROLE_USER']);
        $user2->setPassword('password');
        $this->em->persist($user2);

        $this->em->flush();
        $this->em->clear();

        // --- Test
        $guests = $this->repository->findAllGuests();

        $this->assertCount(2, $guests);

        foreach ($guests as $guest) {
            $this->assertNotContains('ROLE_ADMIN', $guest->getRoles());
        }
    }

    public function testFindAdmin(): void
    {
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('password');
        $this->em->persist($admin);

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('password');
        $this->em->persist($user);

        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findAdmin();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame('admin@test.com', $result->getEmail());
        $this->assertContains('ROLE_ADMIN', $result->getRoles());
    }

}

