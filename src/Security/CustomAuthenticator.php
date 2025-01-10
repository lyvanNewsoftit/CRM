<?php

namespace App\Security;

use App\Entity\Users;
use App\Security\Hasher\Sha256PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class CustomAuthenticator extends AbstractAuthenticator
{
    private $jwtManager;
    private $userProvider;
    private UserPasswordHasherInterface $passwordEncoder;


    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly RefreshTokenGeneratorInterface $refreshTokenGenerator, private readonly Sha256PasswordHasher $passwordHasher, private readonly JwtTokenService $jwtTokenService, JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;
    }


    public function supports(Request $request): ?bool
    {
        /* return true if the path is /nsit-api/login_check and the method is POST
        so the script can move on authenticate method
        */
        return $request->getPathInfo() === '/nsit-api/login_check' && $request->getMethod() === 'POST';

    }

    public function authenticate(Request $request): Passport
    {

        // get the payload content
        $content = json_decode($request->getContent(), true);

        //If the payload does not contain email & password
        if (!isset($content['email']) || !isset($content['password'])) {
            throw new CustomUserMessageAuthenticationException('Les champs email et password sont requis.');
        }

        $email = $content['email'];
        $password = $content['password'];

        $passport = new Passport(
            new UserBadge($email, function ($email) {
                return $this->userProvider->loadUserByIdentifier($email);
            }),
            new CustomCredentials(
                function ($password, UserInterface $user) {
                    // Vérifie le mot de passe avec le service Sha256PasswordHasher
                    return $this->passwordHasher->isPasswordValid($user, $password);
                },
                $password
            )
        );

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {

        // Get the authenticated User
        $user = $token->getUser();

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'L\'utilisateur n\'existe pas.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // generate the token with the custom service JwtTokenService
        $jwt = $this->jwtTokenService->generateToken($user);
        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            3600 * 24 * 3 // durée de vie du refresh token 3 jours
        );

        // Sauvegarde du refresh token en base
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        // Création du cookie HTTP-only pour le refresh token
        $refreshTokenCookie = Cookie::create(
            'refresh_token', // Nom du cookie
            $refreshToken->getRefreshToken(), // Valeur
            time() + (3600 * 24 * 7), // Expiration : 7 jours
            '/', // Path
            null, // Domaine (null pour par défaut)
            true, // Secure : HTTPS uniquement
     true,
            false, // Raw
            Cookie::SAMESITE_NONE// SameSite policy
        );

        // création du cookie http only pour le token
        $jwtCookie = Cookie::create(
            'access_token', // Nom du cookie
            $jwt, // Valeur du JWT
            time() + 3600, // Expiration : 1 heure (3600 secondes)
            '/', // Path
            null, // Domaine (null pour par défaut)
            true, // Secure : HTTPS uniquement
            true, // HttpOnly
            false, // Raw
            Cookie::SAMESITE_NONE // SameSite policy
        );


        // JSON Response with the token.
        $response = new JsonResponse([
            'success' => true,
//            'token' => $jwt,
//            'refresh_token' => $refreshToken->getRefreshToken(),
        ], Response::HTTP_OK);

        $response->headers->setCookie($refreshTokenCookie);
        $response->headers->setCookie($jwtCookie);

        return $response;

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?JsonResponse
    {

        // Vérifie si l'exception est une CustomUserMessageAuthenticationException
        if ($exception instanceof CustomUserMessageAuthenticationException) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessageKey(),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Réponse générique pour toute autre erreur d'authentification
        return new JsonResponse([
            'success' => false,
            'message' => 'L\'authentification a échoué.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
