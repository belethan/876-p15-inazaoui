<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AlbumFixtures extends Fixture
{
    // Groupe "album" (sans s)
    public static function getGroups(): array
    {
        return ['album'];
    }
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $album = new Album();
            $album->setName('Album ' . $i);

            $manager->persist($album);
        }

        $manager->flush();
    }
}

