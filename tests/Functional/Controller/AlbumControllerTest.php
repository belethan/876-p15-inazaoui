<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
{
    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
    }

    private function truncateTables(EntityManagerInterface $em): void
    {
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement($platform->getTruncateTableSQL('album', true));
        $connection->executeStatement($platform->getTruncateTableSQL('user', true));
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function loginAsAdmin($client, EntityManagerInterface $em): void
    {
        $admin = TestUserFactory::getOrCreateIna($em);
        $client->loginUser($admin);
    }

    public function testIndexPageIsAccessible(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        $client->request('GET', '/admin/album');

        self::assertResponseIsSuccessful();
    }

    public function testAddAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        // 🔑 Initialise la session
        $crawler = $client->request('GET', '/admin/album/add');
        self::assertResponseIsSuccessful();

        // 🔑 Soumission sans dépendre du bouton
        $form = $crawler->filter('form')->form([
            'album[name]' => 'Album fonctionnel',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/admin/album');

        $repo = $client->getContainer()->get(AlbumRepository::class);
        self::assertNotNull($repo->findOneBy(['name' => 'Album fonctionnel']));
    }

    public function testUpdateAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        $album = (new Album())->setName('Album original');
        $em->persist($album);
        $em->flush();

        $albumId = $album->getId();

        $crawler = $client->request(
            'GET',
            '/admin/album/update/' . $albumId
        );
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'album[name]' => 'Album modifié',
        ]);

        $client->submit($form);
        self::assertResponseRedirects('/admin/album');

        // 🔑 relire l’entité depuis la DB
        $updatedAlbum = $em->getRepository(Album::class)->find($albumId);

        self::assertSame('Album modifié', $updatedAlbum->getName());
    }


    public function testDeleteAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        // Création d’un album
        $album = (new \App\Entity\Album())->setName('Album à supprimer');
        $em->persist($album);
        $em->flush();

        $albumId = $album->getId();

        // 1️⃣ Charger la page index (session + CSRF généré par Twig)
        $crawler = $client->request('GET', '/admin/album');
        self::assertResponseIsSuccessful();

        // 2️⃣ Récupérer le bouton Supprimer correspondant à l’album
        $buttonSelector = sprintf(
            'button[data-delete-url="/admin/album/delete/%d"]',
            $albumId
        );

        self::assertSelectorExists($buttonSelector);

        $button = $crawler->filter($buttonSelector);

        $deleteUrl = $button->attr('data-delete-url');
        $csrfToken = $button->attr('data-delete-token');

        self::assertNotEmpty($deleteUrl);
        self::assertNotEmpty($csrfToken);

        // 3️⃣ Simuler exactement la requête JS (POST avec _token)
        $client->request('POST', $deleteUrl, [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/admin/album');

        // 4️⃣ Vérifier suppression en base
        $deleted = $em->getRepository(\App\Entity\Album::class)->find($albumId);
        self::assertNull($deleted);
    }


}
