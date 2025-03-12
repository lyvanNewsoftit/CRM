<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CheckAuthController extends AbstractController
{

    public function __construct()
    {
    }

    #[Route('/crm-api/auth', name: 'check_auth', methods: ['GET'])]
    public function checkAuth(Request $request, SessionInterface $session): JsonResponse
    {
        $session= $request->getSession();
        $user = $session->get('user');
        $PHPSESSID = $request->cookies->get('PHPSESSID');

        if(!$user || !$PHPSESSID){
            return new JsonResponse([
                'success' => false,
                'message' => 'No authenticated users.'
            ], Response::HTTP_UNAUTHORIZED, [], false);
        } else {
            return new JsonResponse([
                'success' => true,
                'message' => 'Authenticated user.',
                'user' => $user->getUserIdentifier()
            ], Response::HTTP_OK, [], false);
        }
    }
}
