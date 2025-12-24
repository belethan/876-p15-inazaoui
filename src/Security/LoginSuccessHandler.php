<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

readonly class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function onAuthenticationSuccess(
        Request $request,
        #[\SensitiveParameter] TokenInterface $token,
    ): RedirectResponse {
        $user = $token->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse(
                $this->router->generate('admin_dashboard')
            );
        }

        return new RedirectResponse(
            $this->router->generate('home')
        );
    }
}
