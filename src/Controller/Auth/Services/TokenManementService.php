<?php

namespace App\Controller\Auth\Services;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenManementService
{
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function checkAuth(Request $request): Response
    {
        // Récupérer le token dans les cookies
        $token = $request->cookies->get('access_token'); // 'access_token' est le nom du cookie

        // Si le token existe, essayer de le valider
        if ($token) {
            try {
                // Utilisez la méthode parse() pour obtenir le token à partir de la chaîne JWT
                $parsedToken = $this->jwtManager->parse($token); // Cette méthode accepte une chaîne de caractères

                // Vérifier si le token est valide
                if ($parsedToken) {
                    return new Response('Authenticated', Response::HTTP_OK);
                }
            } catch (\Exception $e) {
                // En cas d'erreur (jeton invalide, expiré, etc.), retourner une erreur 401
                return new Response($e->getMessage(), Response::HTTP_UNAUTHORIZED);
            }
        }

        // Si pas de token ou si le token est invalide, retour 401
        return new Response('Not authenticated', Response::HTTP_UNAUTHORIZED);
    }
}

