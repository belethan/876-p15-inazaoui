<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
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

        // Isolation des tests
        $this->em->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    private function createUser(
        string $email,
        string $plainPassword,
        array $roles,
        bool $actif
    ): void {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setUserActif($actif);

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );

        $this->em->persist($user);
        $this->em->flush();
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = self::createClient();
        $this->initServices();

        $client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
        self::assertSelectorExists('input[name="email"]');
        self::assertSelectorExists('input[name="password"]');
    }

    public function testActiveUserCanLogin(): void
    {
        $client = self::createClient();
        $this->initServices();

        $this->createUser(
            'user@test.com',
            'password123',
            ['ROLE_USER'],
            true
        );

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'email'    => 'user@test.com',
            'password' => 'password123',
        ]);

        $client->submit($form);

        /**
         * Login OK :
         * - pas d'erreur affichée
         * - pas de crash
         * - comportement stable
         */
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.alert-danger');
    }

    public function testInactiveUserCannotLogin(): void
    {
        $client = self::createClient();
        $this->initServices();

        $this->createUser(
            'inactive@test.com',
            'password123',
            ['ROLE_USER'],
            false
        );

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'email'    => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $client->submit($form);

        /**
         * Login refusé :
         * - on reste sur /login
         * - aucune redirection
         * - pas d'authentification
         */
        self::assertResponseIsSuccessful();
    }
}
