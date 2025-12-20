<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserAjaxTest extends WebTestCase
{
    public function testUsersDataEndpointReturnsDatatableJsonWithStatut(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $admin = TestUserFactory::getOrCreateIna($em);
        $client->loginUser($admin);

        // On crée un invité actif pour vérifier le rendu "Actif"
        $guest = new User();
        $guest->setEmail('guest_' . uniqid('', true) . '@test.fr');
        $guest->setRoles(['ROLE_GUEST']);
        $guest->setPassword('test');
        $guest->setUserActif(true);

        $em->persist($guest);
        $em->flush();

        $client->xmlHttpRequest('POST', '/admin/users/data', [
            // paramètres DataTables minimum
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'order' => [['column' => 0, 'dir' => 'asc']],
            'columns' => [
                ['data' => 'id'],
                ['data' => 'email'],
                ['data' => 'prenom'],
                ['data' => 'nom'],
                ['data' => 'statut'],
                ['data' => 'actions'],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $payload = json_decode($client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('draw', $payload);
        self::assertArrayHasKey('recordsTotal', $payload);
        self::assertArrayHasKey('recordsFiltered', $payload);
        self::assertArrayHasKey('data', $payload);
        self::assertIsArray($payload['data']);

        // On vérifie qu'on retrouve notre invité et que le statut correspond à user_actif=true
        $row = null;
        foreach ($payload['data'] as $r) {
            if (($r['email'] ?? null) === $guest->getEmail()) {
                $row = $r;
                break;
            }
        }

        self::assertNotNull($row, 'La ligne DataTables pour l’invité créé n’a pas été trouvée.');
        self::assertStringContainsString('Actif', (string) ($row['statut'] ?? ''));
    }
}
