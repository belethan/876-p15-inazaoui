<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminAccessTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
    }

    private function initServices(): void
    {
        $container = self::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);

        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    private function createUser(string $email, array $roles): void
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setUserActif(true);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, 'password123')
        );

        $this->em->persist($user);
        $this->em->flush();
    }

    public function testAdminAccessIsDeniedForUser(): void
    {
        $client = self::createClient();
        $this->initServices();

        $this->createUser('user@test.com', ['ROLE_USER']);

        $client->request('GET', '/admin');

        self::assertResponseStatusCodeSame(302); // redirection vers login
    }

    public function testAdminAccessIsGrantedForAdmin(): void
    {
        $client = self::createClient();
        $this->initServices();

        $this->createUser('admin@test.com', ['ROLE_ADMIN']);

        $client->request('GET', '/admin');

        // Si la route existe et que l'accès est autorisé
        self::assertResponseStatusCodeSame(200);
    }
}

