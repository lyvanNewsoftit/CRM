<?php


namespace App\EventSubscriber;

use App\Entity\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshTokenRepository;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;

class RemoveExpiredTokenSubscriber implements EventSubscriberInterface
{
    private JWTTokenManagerInterface $jwtManager;
    private EntityManagerInterface $entityManager;
    private $refreshTokenRepo;

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
        $this->refreshTokenRepo = $entityManager->getRepository(RefreshToken::class);
    }

    public function onKernelResponse(ResponseEvent $event)
    {

        $request = $event->getRequest();
        // Vérifie que la route correspond à `/token/refresh`
        if ($request->getPathInfo() !== '/nsit-api/token/refresh') {
            return;
        }

        $response = $event->getResponse();
        $apiResponse = json_decode($response->getContent());

        // Vérifie que le token est expiré (401) et la réponse a pour message 'Expired JWT Token'
        if ($response->getStatusCode() === 401 && isset($apiResponse->message) && $apiResponse->message === 'Expired JWT Token') {
            // Récupérer le jeton depuis les cookies
            $accessToken = $request->cookies->get('access_token');

            // Vérifier si le jeton est valide (pas vide)
            /*    if ($accessToken) {
                    try {
                        // Créer un parser avec un encoder Jose
                        $parser = new Parser(new JoseEncoder());

                        // Décoder le jeton pour récupérer le payload
                        $token = $parser->parse($accessToken);

                        // Accéder aux claims du token
                        $claims = $token->claims();

                        // Vérifier si le claim 'username' est non nul
                        if (null !== $claims->get('username')) {
                            $username = $claims->get('username');

                            // Récupérer l'utilisateur à partir du nom d'utilisateur
                            $user = $this->entityManager->getRepository(Users::class)->findOneBy(['email' => $username]);

                            if ($user) {

                                // Effectuer des actions supplémentaires avec l'utilisateur si nécessaire
                            }
                        }
                    } catch (\Exception $e) {
                        // Si une erreur se produit lors du décodage du jeton, ignorer l'erreur
                        // Tu peux ajouter des logs ici pour plus de détails sur l'erreur si nécessaire
                    }
                }*/

            //suppression ancien access_token
            $jwtCookie = Cookie::create(
                'access_token', // Nom du cookie
                '', // Valeur du JWT
                time() - 3600,
                '/', // Path
                null, // Domaine (null pour par défaut)
                true, // Secure : HTTPS uniquement
                true, // HttpOnly
                false, // Raw
                Cookie::SAMESITE_NONE // SameSite policy
            );
            // Supprimer le cookie access_token
            $response->headers->setCookie($jwtCookie);

        } else if ($response->getStatusCode() === 401 && isset($apiResponse->message) && ($apiResponse->message === 'Missing JWT Refresh Token' || $apiResponse->message === 'Invalid JWT Refresh Token')) {

            $session = $request->getSession();
            $expiredRefreshToken = $session->get('refresh_token');
            $tokenToDeleteFromDatabase = $this->refreshTokenRepo->findOneBy(['refreshToken' => $expiredRefreshToken]);
            if ($tokenToDeleteFromDatabase) {
                $this->entityManager->remove($tokenToDeleteFromDatabase);
                $this->entityManager->flush();

                // Suppression des informations dans la session
                $session->remove('refresh_token'); // Supprime le refresh token
                $session->remove('user'); // Supprime les informations utilisateur

                // Expiration du cookie PHPSESSID pour le supprimer
                $response->headers->setCookie(new Cookie('PHPSESSID', '', time() - 3600, '/', null, true, true));

            }

        } else {

            //création du cookie http only pour le token
            $jwtCookie = Cookie::create(
                'access_token', // Nom du cookie
                $apiResponse->token, // Valeur du JWT
                time() + 3600, // Expiration : 1 heure (3600 secondes)
                '/', // Path
                null, // Domaine (null pour par défaut)
                true, // Secure : HTTPS uniquement
                true, // HttpOnly
                false, // Raw
                Cookie::SAMESITE_NONE // SameSite policy
            );

            $response->headers->setCookie($jwtCookie);
        }


        // return new JsonResponse(['api response' => $apiResponse, 'cookie' => $request->cookies]);
    }

    public
    static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }
}
