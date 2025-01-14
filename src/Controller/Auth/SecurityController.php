<?php

namespace App\Controller\Auth;

use App\Security\JwtTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OTPHP\TOTP;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/nsit-api/login_check', name: 'login_check', methods: ['POST'])]
    public function login(JWTTokenManagerInterface $jwtManager, UserInterface $user)
    {
        /*
            classic Authentication is now handle by the CustomAuthenticator in Security\CustomAuthenticator.php
         */
    }

    #[Route('/nsit-api/check_2FA', name: 'check_2FA', methods: ['POST'])]
    public function check2Fa(Request $request, GoogleAuthenticatorInterface $googleAuthenticator, JwtTokenService $jwtTokenService, RefreshTokenGeneratorInterface $refreshTokenGenerator)
    {
        $session = $request->getSession();
        $user = $session->get('user');
        $rememberMe = $session->get(('rememberMe'));

        $payload = $request->getPayload();
        $authCode = $payload->get('authCode');
        $isAuthCodeValid = $googleAuthenticator->checkCode($user, $authCode);

        $secret = $user->getGoogleAuthenticatorSecret();

        // Créer un TOTP basé sur le secret
        $totp = TOTP::create($secret);

        // Optionnel : Afficher des informations pour le débogage
        $serverTime = time();
        $expectedCode = $totp->at($serverTime); // Code généré pour l'heure actuelle

        // Log de débogage
//        dd([
//            'server_time' => date('Y-m-d H:i:s', $serverTime),
//            'expected_code' => $expectedCode,
//            'provided_code' => $authCode,
//        ]);
//
//        dd('security', $isAuthCodeValid);

        // generate the token with the custom service JwtTokenService
        $jwt = $jwtTokenService->generateToken($user);
        $refreshTokenCookie = null;

        if ($isAuthCodeValid === true) {
            if ($rememberMe) {

                $refreshToken = $refreshTokenGenerator->createForUserWithTtl(
                    $user,
                    time() + (3600 * 24 * 3)// durée de vie du refresh token 3 jours
                );

                // Sauvegarde du refresh token en base
                $this->entityManager->persist($refreshToken);
                $this->entityManager->flush();

                // Création du cookie HTTP-only pour le refresh token
                $refreshTokenCookie = Cookie::create(
                    'refresh_token', // Nom du cookie
                    $refreshToken->getRefreshToken(), // Valeur
                    time() + (3600 * 24 * 3), // Expiration : 3 jours
                    '/', // Path
                    null, // Domaine (null pour par défaut)
                    true, // Secure : HTTPS uniquement
                    true,
                    false, // Raw
                    Cookie::SAMESITE_NONE// SameSite policy
                );


            }


            // création du cookie http only pour le token
            $jwtCookie = Cookie::create(
                'access_token', // Nom du cookie
                $jwt, // Valeur du JWT
                time() + (3600 *2), // expiration 1heure: 3600 * 2 par rapport à lheure utc qui est en décalage de 1H
                '/', // Path
                null, // Domaine (null pour par défaut)
                true, // Secure : HTTPS uniquement
                true, // HttpOnly
                false, // Raw
                Cookie::SAMESITE_NONE // SameSite policy
            );

            // JSON Response
            $response = new JsonResponse([
                'success' => true,
                'message' => 'Authentification réussie',
                //'token' => $jwt,
                // 'refresh_token' => $refreshToken->getRefreshToken(),
            ], Response::HTTP_OK);

            if ($refreshTokenCookie) {
                $response->headers->setCookie($refreshTokenCookie);
            }

            $response->headers->setCookie($jwtCookie);

            if ($refreshTokenCookie) {
                $session->set('refresh_token', $refreshToken->getRefreshToken());
            }
            return $response;
        } else {
            return new JsonResponse([
                'success' => false,
                'message' => 'Code d\'authentification invalide',
                //'token' => $jwt,
                // 'refresh_token' => $refreshToken->getRefreshToken(),
            ], Response::HTTP_UNAUTHORIZED);
        }


        dd($response, $session);
    }

}