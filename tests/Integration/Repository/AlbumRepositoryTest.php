<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Album;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->truncateAlbumTable();
    }

    private function truncateAlbumTable(): void
    {
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement(
            $platform->getTruncateTableSQL('album', true)
        );
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testPersistAndFindAlbum(): void
    {
        $album = new Album();
        $album->setName('Album Repository Test');

        $this->em->persist($album);
        $this->em->flush();
        $this->em->clear();

        $repo = $this->em->getRepository(Album::class);

        $found = $repo->findOneBy(['name' => 'Album Repository Test']);

        $this->assertNotNull($found);
        $this->assertSame('Album Repository Test', $found->getName());
    }

    public function testFindAll(): void
    {
        $album1 = (new Album())->setName('Album 1');
        $album2 = (new Album())->setName('Album 2');

        $this->em->persist($album1);
        $this->em->persist($album2);
        $this->em->flush();

        $albums = $this->em->getRepository(Album::class)->findAll();

        $this->assertCount(2, $albums);
    }
}

