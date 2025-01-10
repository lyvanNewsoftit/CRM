<?php

namespace App\Controller\Auth;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/nsit-api/delete-token', name: 'delete_token', methods: ['POST'])]
    public function deleteToken(Request $request)
    {
        dd($request->cookies);
        // Récupérer le refresh_token depuis les cookies
        $refreshToken = $request->cookies->get('refresh_token');

        // Supprimer le cookie access_token expiré
        $expiredAccessTokenCookie = Cookie::create(
            'access_token',  // Nom du cookie
            '',  // Valeur vide
            time() - 3600,  // Expiration dans le passé pour supprimer le cookie
            '/',  // Path
            null,  // Domaine
            true,  // Secure : HTTPS uniquement
            true,  // HttpOnly
            false, // Raw
            Cookie::SAMESITE_LAX // SameSite policy
        );

    }

}