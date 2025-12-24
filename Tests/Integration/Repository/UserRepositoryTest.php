<?php

declare(strict_types=1);

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

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testFindAllGuests(): void
    {
        $admin = (new User())
            ->setEmail('admin@test.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('test')
            ->setUserActif(true);

        $guest1 = (new User())
            ->setEmail('guest1@test.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test')
            ->setUserActif(true);

        $guest2 = (new User())
            ->setEmail('guest2@test.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test')
            ->setUserActif(true);

        $this->em->persist($admin);
        $this->em->persist($guest1);
        $this->em->persist($guest2);
        $this->em->flush();
        $this->em->clear();

        $guests = $this->repository->findAllGuests();

        self::assertCount(2, $guests);

        foreach ($guests as $user) {
            self::assertNotContains('ROLE_ADMIN', $user->getRoles());
        }
    }

    public function testFindAdmin(): void
    {
        $admin = (new User())
            ->setEmail('admin@test.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('test')
            ->setUserActif(true);

        $this->em->persist($admin);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repository->findAdmin();

        self::assertInstanceOf(User::class, $result);
        self::assertSame('admin@test.com', $result->getEmail());
    }

    public function testUserCanBeDeleted(): void
    {
        $user = (new User())
            ->setEmail('delete@test.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('test')
            ->setUserActif(true);

        $this->em->persist($user);
        $this->em->flush();

        $id = $user->getId();

        $this->em->remove($user);
        $this->em->flush();

        self::assertNull(
            $this->repository->find($id)
        );
    }

    public function testFindAllUsersReturnsArray(): void
    {
        $users = $this->repository->findAll();

        self::assertIsArray($users);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}
