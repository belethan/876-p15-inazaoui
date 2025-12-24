<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Security\LoginSuccessHandler;
use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandlerTest extends WebTestCase
{
    public function testHandlerImplementsInterface(): void
    {
        self::bootKernel();

        $router = self::getContainer()->get(RouterInterface::class);

        $handler = new LoginSuccessHandler($router);

        self::assertInstanceOf(
            AuthenticationSuccessHandlerInterface::class,
            $handler
        );
    }

    public function testAdminIsRedirectedToAdminDashboard(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $router = $container->get(RouterInterface::class);

        $admin = TestUserFactory::getOrCreateIna($em);

        $request = Request::create('/login');

        $token = new UsernamePasswordToken(
            $admin,
            'main',
            $admin->getRoles()
        );

        $handler = new LoginSuccessHandler($router);

        $response = $handler->onAuthenticationSuccess($request, $token);

        self::assertTrue($response->isRedirect());
        self::assertSame('/admin', $response->headers->get('Location'));
    }

    public function testGuestIsRedirectedToHome(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $router = $container->get(RouterInterface::class);

        $guest = new User();
        $guest->setEmail('guest_'.uniqid('', true).'@test.fr');
        $guest->setPassword('test');
        $guest->setRoles(['ROLE_GUEST']);
        $guest->setUserActif(true);

        $em->persist($guest);
        $em->flush();

        $request = Request::create('/login');

        $token = new UsernamePasswordToken(
            $guest,
            'main',
            $guest->getRoles()
        );

        $handler = new LoginSuccessHandler($router);

        $response = $handler->onAuthenticationSuccess($request, $token);

        self::assertTrue($response->isRedirect());
        self::assertSame('/', $response->headers->get('Location'));
    }
}
