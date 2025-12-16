<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Media;
use App\Entity\User;
use App\Repository\MediaRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class MediaRepositoryTest extends KernelTestCase
{
    private MediaRepository $mediaRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->mediaRepository = self::getContainer()
            ->get(MediaRepository::class);
    }

    public function testFindByUser(): void
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $passwordHasher = self::getContainer()
            ->get(UserPasswordHasherInterface::class);

        // User de test
        $user = new User();
        $user->setEmail('repo_' . uniqid() . '@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setUserActif(true);
        $user->setPassword(
            $passwordHasher->hashPassword($user, 'password')
        );

        $em->persist($user);

        // Media associé
        $media = new Media();
        $media->setTitle('Media repo test');
        $media->setUser($user);

        $em->persist($media);
        $em->flush();

        // Test repository
        $result = $this->mediaRepository->findBy([
            'user' => $user,
        ]);

        $this->assertCount(1, $result);
        $this->assertSame($media, $result[0]);
    }

    public function testFindAllReturnsArray(): void
    {
        $result = $this->mediaRepository->findAll();

        $this->assertIsArray($result);
    }
}
