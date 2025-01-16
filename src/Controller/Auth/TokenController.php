<?php
namespace  App\Controller\Auth;


use App\Security\JwtTokenService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class TokenController extends AbstractController
{

    // Injection du service de gestion des tokens dans le contrôleur
    public function __construct()
    {
    }


    #[Route('/nsit-api/token/refresh',name:"api_refresh_token", methods:["GET"])]
    public function refreshToken(Request $request, JwtTokenService $jwtTokenService): Response
    {
        dd('controller token');

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

        if (!$refreshToken) {
            return $this->json(['error' => 'Refresh token not found'], Response::HTTP_UNAUTHORIZED);
        }


        // Vérifier si le refresh token est valide et obtenir un nouveau access token
        try {
            // Récupérer un nouveau access token à partir du refresh token
            $newAccessToken = $jwtTokenService->generateToken($request->getUser());

            // Créer le cookie HttpOnly pour le nouveau access token
            $accessTokenCookie = Cookie::create(
                'access_token', // Nom du cookie
                $newAccessToken, // Valeur du JWT
                time() + 3600, // Expiration : 1 heure (3600 secondes)
                '/', // Path
                null, // Domaine
                true, // Secure : HTTPS uniquement
                true, // HttpOnly
                false, // Raw
                Cookie::SAMESITE_LAX // SameSite policy
            );

            // Retourner la réponse avec le cookie du nouveau token
            return $this->json(['success' => true])
                ->headers->setCookie($accessTokenCookie);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to refresh token'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
