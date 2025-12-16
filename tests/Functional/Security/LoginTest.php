<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginTest extends WebTestCase
{
    private function createUser(
        string $email,
        string $plainPassword,
        array $roles,
        bool $actif,
        UserPasswordHasherInterface $passwordHasher
    ): void {
        $em = self::getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setUserActif($actif);

        $user->setPassword(
            $passwordHasher->hashPassword($user, $plainPassword)
        );

        $em->persist($user);
        $em->flush();
    }

    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
    }

    public function testActiveUserCanLogin(): void
    {
        $client = static::createClient();

        $passwordHasher = self::getContainer()
            ->get(UserPasswordHasherInterface::class);

        $email = 'user_' . uniqid() . '@test.com';

        $this->createUser(
            $email,
            'password123',
            ['ROLE_USER'],
            true,
            $passwordHasher
        );

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'email'    => $email,
            'password' => 'password123',
        ]);

        $client->submit($form);

        //login réussi = redirection
        $this->assertResponseRedirects();
    }

    public function testInactiveUserCannotLogin(): void
    {
        $client = static::createClient();

        $passwordHasher = self::getContainer()
            ->get(UserPasswordHasherInterface::class);

        $email = 'inactive_' . uniqid() . '@test.com';

        $this->createUser(
            $email,
            'password123',
            ['ROLE_USER'],
            false,
            $passwordHasher
        );

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form([
            'email'    => $email,
            'password' => 'password123',
        ]);

        $client->submit($form);

        //  login refusé = redirection (Symfony)
        $this->assertResponseRedirects();
    }
}
