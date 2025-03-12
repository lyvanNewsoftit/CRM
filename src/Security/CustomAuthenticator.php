<?php

namespace App\Security;

use App\Controller\Auth\Services\TwoFactorService;
use App\Entity\Users;
use App\Security\Hasher\Sha256PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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


    public function __construct(private readonly TwoFactorService $twoFactorService, private readonly Sha256PasswordHasher $passwordHasher, JWTTokenManagerInterface $jwtManager, UserProviderInterface $userProvider, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->jwtManager = $jwtManager;
        $this->userProvider = $userProvider;
        $this->passwordEncoder = $passwordEncoder;
    }


    public function supports(Request $request): ?bool
    {
        /* return true if the path is /crm-api/login_check and the method is POST
        so the script can move on authenticate method
        */
        return $request->getPathInfo() === '/crm-api/login_check' && $request->getMethod() === 'POST';

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


    public function onAuthenticationSuccess(Request $request,  TokenInterface $token, string $firewallName): ?JsonResponse
    {

        // Récupérer rememberMe dans le payload afin de le mettre en session pour savoir si oui ou non on crée un cookie frefreshtoken quin permettra de rester connecter beaucoup plus longtemps
        $payload = $request->getPayload();
        $rememberMe =$payload->get('rememberMe');
        //Récupérer l'utilisateur
        $user = $token->getUser();
        $role = $user->getRoles();

        //Récupérer la company
        $company = $user->getCompany();

        // Créer une session pour y stocker le User ainsi que le status de rememberMe.
        $session = $request->getSession();
        $session->set('user', $user);
        $session->set('company', $company);
        $session->set('roles', $role);
        $session->set('rememberMe', $rememberMe);

        //lancement de la fonction qui permet soit de créer un secret a stocker dans user et  générer un qrCode a scanner soit envoyer comme response qu'l faut directement le code 2FA avec authenticator
       return $this->twoFactorService->generateQrCode($request, $token->getUser());

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
