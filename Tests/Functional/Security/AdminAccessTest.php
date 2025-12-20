<?php

namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Tests\Support\TestUserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAccessTest extends WebTestCase
{
    public function testAdminAccessIsDeniedForUser(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // 🔑 Utilisateur non admin avec email UNIQUE
        $user = new User();
        $user->setEmail('user_' . uniqid() . '@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('test'); // valeur factice suffisante pour la DB

        $em->persist($user);
        $em->flush();

        // Authentification
        $client->loginUser($user);

        // Tentative d’accès à une route admin
        $client->request('GET', '/admin/media');

        // Accès refusé (utilisateur connecté mais sans rôle)
        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminAccessIsGrantedForAdmin(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();

        // 🔑 Récupère ou crée Ina (ROLE_ADMIN)
        $admin = TestUserFactory::getOrCreateIna($em);

        // Authentification admin
        $client->loginUser($admin);

        // Accès à la route admin
        $client->request('GET', '/admin/media');

        $this->assertResponseIsSuccessful();
    }
}
