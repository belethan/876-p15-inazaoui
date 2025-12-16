<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\Support\TestUserFactory;

class MediaControllerTest extends WebTestCase
{
    private function loginIna()
    {
        $client = static::createClient();

        $em = static::getContainer()->get('doctrine')->getManager();

        // Récupère ou crée Ina de manière fiable
        $user = TestUserFactory::getOrCreateIna($em);

        // Authentification Symfony propre (sans formulaire)
        $client->loginUser($user);

        return $client;
    }

    public function testMediaIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/media');

        $this->assertResponseRedirects('/login');
    }

    public function testMediaIndexAsAdmin(): void
    {
        $client = $this->loginIna();
        $client->request('GET', '/admin/media');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testDeleteMedia(): void
    {
        $client = $this->loginIna();

        $em = static::getContainer()->get('doctrine')->getManager();

        // Récupère Ina (garanti)
        $user = TestUserFactory::getOrCreateIna($em);

        //  Crée un Media dédié au test (ne dépend pas de la DB)
        $media = new Media();
        $media->setTitle('Media test');
        $media->setPath('uploads/test.jpg'); // pas besoin que le fichier existe
        $media->setUser($user);

        $em->persist($media);
        $em->flush();

        $mediaId = $media->getId();

        // appel de la vraie route (GET)
        $client->request(
            'GET',
            '/admin/media/delete/' . $mediaId
        );

        // redirection après suppression
        $this->assertResponseRedirects('/admin/media');

        // vérification suppression DB
        $deleted = $em->getRepository(Media::class)->find($mediaId);
        $this->assertNull($deleted);
    }
}
