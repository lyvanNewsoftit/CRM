<?php

namespace App\Controller\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends AbstractController
{

    #[Route('/nsit-api/login_check', name: 'login_check', methods: ['POST'])]
    public function login(JWTTokenManagerInterface $jwtManager, UserInterface $user)
    {
        /*
            classic Authentication is now handle by the CustomAuthenticator in Security\CustomAuthenticator.php
         */
    }
}