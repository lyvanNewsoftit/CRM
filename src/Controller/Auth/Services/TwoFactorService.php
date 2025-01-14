<?php

namespace App\Controller\Auth\Services;

use Doctrine\ORM\EntityManagerInterface;
use OTPHP\TOTP;
use Scheb\TwoFactorBundle\Security\Http\Authentication\AuthenticationRequiredHandlerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class TwoFactorService
{

    public function __construct(private GoogleAuthenticatorInterface $googleAuthenticator, private EntityManagerInterface $entityManager)
    {
    }

    public function generateQrCode(Request $request, $user,): JsonResponse
    {

        //Vérifier si l'utilisateur a un secret pour 2FA, sinon en créer un.
        if (!$user->getGoogleAuthenticatorSecret()) {

            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();


            // Générer le contenu QR
            $qrContent = $this->googleAuthenticator->getQrContent(
                $user,
                $user->getGoogleAuthenticatorUsername(), // Utilisez l'email de l'utilisateur
                $user->getGoogleAuthenticatorSecret()
            );


            return new JsonResponse([
                'success' => true,
                'needQrCodeScan' => true,
                'message' => 'Veuillez scanner ce QR code dans votre application d\'authentification.',
                'secretCode' => $secret,
                'qrCodeContent' => $qrContent
            ], Response::HTTP_OK);
        }



        $jsonResponse = json_encode(['success' => true, 'needQrCodeScan' => false, 'message' => 'Le code 2FA est requis.','qrCodeContent' => null]);
        // Retourner l'URL qui permettra de générer le qr code
        return new JsonResponse($jsonResponse, Response::HTTP_OK, ['Content-Type' => 'application/json'], true);
    }

}