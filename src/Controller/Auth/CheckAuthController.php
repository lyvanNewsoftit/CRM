<?php

namespace App\Controller\Auth;

use App\Controller\Auth\Services\TokenManementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class CheckAuthController extends AbstractController
{
    private TokenManementService $tokenManagementService;

    public function __construct(TokenManementService $authService)
    {
        $this->tokenManagementService = $authService;
    }

    #[Route('/nsit-api/auth', name: 'check_auth', methods: ['GET'])]
    public function checkAuth(Request $request): Response
    {
        return $this->tokenManagementService->checkAuth($request);
    }
}
