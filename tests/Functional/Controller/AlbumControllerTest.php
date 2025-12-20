<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class AlbumControllerTest extends WebTestCase
{
    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get(EntityManagerInterface::class);
    }

    private function getRouter($client): RouterInterface
    {
        return $client->getContainer()->get(RouterInterface::class);
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

        $crawler = $client->request('GET', '/admin/album/add');
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'album[name]' => 'Album fonctionnel',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/admin/album');

        $repo = $client->getContainer()->get(AlbumRepository::class);
        self::assertNotNull(
            $repo->findOneBy(['name' => 'Album fonctionnel'])
        );
    }

    public function testUpdateAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);
        $router = $this->getRouter($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        //  Création album
        $album = (new Album())->setName('Album original');
        $em->persist($album);
        $em->flush();

        $albumId = $album->getId();
        self::assertNotNull($albumId);

        //  Route EDIT (pas UPDATE)
        $editUrl = $router->generate('admin_album_edit', [
            'id' => $albumId,
        ]);

        $crawler = $client->request('GET', $editUrl);
        self::assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form([
            'album[name]' => 'Album modifié',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/admin/album');

        //  Rechargement depuis la DB
        $em->clear();
        $updatedAlbum = $em->getRepository(Album::class)->find($albumId);

        self::assertNotNull($updatedAlbum);
        self::assertSame('Album modifié', $updatedAlbum->getName());
    }

    public function testDeleteAlbum(): void
    {
        $client = static::createClient();
        $em = $this->getEntityManager($client);

        $this->truncateTables($em);
        $this->loginAsAdmin($client, $em);

        $album = (new Album())->setName('Album à supprimer');
        $em->persist($album);
        $em->flush();

        $albumId = $album->getId();
        self::assertNotNull($albumId);

        // Charger index pour générer CSRF
        $crawler = $client->request('GET', '/admin/album');
        self::assertResponseIsSuccessful();

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

        //  POST réel
        $client->request('POST', $deleteUrl, [
            '_token' => $csrfToken,
        ]);

        self::assertResponseRedirects('/admin/album');

        $em->clear();
        self::assertNull(
            $em->getRepository(Album::class)->find($albumId)
        );
    }
}
