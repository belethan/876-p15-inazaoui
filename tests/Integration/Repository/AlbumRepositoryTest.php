<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\Album;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlbumRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private AlbumRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = $this->em->getRepository(Album::class);

        $this->truncateTable();
    }

    private function truncateTable(): void
    {
        $conn = $this->em->getConnection();
        $platform = $conn->getDatabasePlatform();

        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $conn->executeStatement(
            $platform->getTruncateTableSQL('album', true)
        );
        $conn->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testPersistAndFindAlbum(): void
    {
        $album = (new Album())->setName('Album Test');

        $this->em->persist($album);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repository->findOneBy(['name' => 'Album Test']);

        self::assertNotNull($found);
        self::assertSame('Album Test', $found->getName());
    }

    public function testFindAllReturnsArray(): void
    {
        $album1 = (new Album())->setName('Album 1');
        $album2 = (new Album())->setName('Album 2');

        $this->em->persist($album1);
        $this->em->persist($album2);
        $this->em->flush();

        $albums = $this->repository->findAll();

        self::assertIsArray($albums);
        self::assertCount(2, $albums);
    }

    public function testCountAlbums(): void
    {
        $album = (new Album())->setName('Album Count');

        $this->em->persist($album);
        $this->em->flush();

        $count = $this->repository->count([]);

        self::assertSame(1, $count);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
    }
}
