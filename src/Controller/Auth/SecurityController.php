<?php

namespace App\Controller\Auth;

use App\Security\JwtTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OTPHP\TOTP;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/crm-api/login_check', name: 'login_check', methods: ['POST'])]
    public function login(JWTTokenManagerInterface $jwtManager, UserInterface $user)
    {
        /*
            classic Authentication is now handle by the CustomAuthenticator in Security\CustomAuthenticator.php
         */
    }

    #[Route('/crm-api/logout', name: 'logout', methods: ['POST'])]
    public function logout(JWTTokenManagerInterface $jwtManager, UserInterface $user, Request $request, SessionInterface $session){
        $session = $request->getSession();
        $session->clear();

        $response = new JsonResponse([
            'success' => true,
            'message' => 'You have been logged out.',
        ], Response::HTTP_OK);

        // Supprimer les cookies en les définissant avec une expiration dans le passé
        $response->headers->setCookie(
            Cookie::create('access_token', '', time() - 3600, '/', null, true, true, false, Cookie::SAMESITE_NONE)
        );
        $response->headers->setCookie(
            Cookie::create('refresh_token', '', time() - 3600, '/crm-api/token/refresh', null, true, true, false, Cookie::SAMESITE_NONE)
        );

        return $response;
    }

    #[Route('/crm-api/check_2FA', name: 'check_2FA', methods: ['POST'])]
    public function check2Fa(ParameterBagInterface $params, Request $request, GoogleAuthenticatorInterface $googleAuthenticator, JWTTokenManagerInterface $jwtTokenService, RefreshTokenGeneratorInterface $refreshTokenGenerator)
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
        // Appel du listerner JWTreatedListener afin de rajouter des données custom dans le token

        $jwt = $jwtTokenService->create($user);

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
                    '/crm-api/token/refresh', // Path
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

            // Préparer le hash des role

            $salt = $params->get('app.role_salt');
            $hashedRoles = array_map(fn($role) => hash_hmac('sha256', $role, $salt), $user->getRoles());

            // JSON Response
            $response = new JsonResponse([
                'success' => true,
                'message' => 'Successful authentication.',
                //'token' => $jwt,
                //'refresh_token' => $refreshToken->getRefreshToken(),
                'roles' => $hashedRoles,
                'user' => $user->getUserIdentifier()
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
                'message' => 'Invalid Auth Code.',
                //'token' => $jwt,
                // 'refresh_token' => $refreshToken->getRefreshToken(),
            ], Response::HTTP_UNAUTHORIZED);
        }



    }

}