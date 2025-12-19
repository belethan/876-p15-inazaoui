<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

final class MediaDescriptionUpdateFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $conn = $manager->getConnection();

        // Récupérer uniquement les IDs (pas d'hydratation Doctrine)
        $ids = $conn->fetchFirstColumn('SELECT id FROM media');

        foreach ($ids as $id) {
            $description = $faker->text(200);

            // Met à jour uniquement si description est NULL ou vide
            $conn->executeStatement(
                'UPDATE media SET description = :description WHERE id = :id AND (description IS NULL OR description = \'\')',
                compact('description', 'id')
            );
        }
    }

    public static function getGroups(): array
    {
        return ['media_description'];
    }
}
