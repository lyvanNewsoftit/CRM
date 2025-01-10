<?php

namespace App\Controller;

// src/Controller/UserController.php

namespace App\Controller;

use App\Controller\Auth\Services\TokenManementService;
use App\Entity\Users;
use App\Repository\UsersRepository;
use App\Security\Hasher\Sha256PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class UsersController extends AbstractController
{
    private $passwordHasher;

    public function __construct(Sha256PasswordHasher $passwordHasher, private TokenManementService $tokenManagementService, private EntityManagerInterface $entityManager, private UsersRepository $usersRepo,)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/user/create", name="user_create")
     */
    #[Route('/nsit-api/users', name: 'create_users', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, Sha256PasswordHasher $passwordHasher): JsonResponse
    {

        $payload = $request->getPayload();


        $user = new Users();

        $user->setEmail($payload->get('email') ?? '');
        $user->setFirstname($payload->get('firstname') ?? '');
        $user->setLastname($payload->get('lastname') ?? '');
        $user->setPassword($payload->get('password') ? $passwordHasher->hashPassword($user, $payload->get('password')) : '');

        $violations = $validator->validate($user);
        $errorsValidation = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $propertyPath = $violation->getPropertyPath();
                $errorsValidation[$propertyPath][] = $violation->getMessage();
            }

            return new JsonResponse($errorsValidation, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('Utilisateur créé avec succès', Response::HTTP_CREATED);
    }


    #[Route('/nsit-api/users', name: 'get_users', methods: ['GET'])]
    public function getUsers(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        // Récupérer le JWT à partir du cookie HttpOnly
        $jwtToken = $request->cookies->get('access_token');

        if (!$jwtToken) {
            return new JsonResponse(['error' => 'JWT Token not found'], Response::HTTP_UNAUTHORIZED);
        }


        $state = $this->tokenManagementService->checkAuth($request);
        $users = $this->usersRepo->findAll();
        $usersJson = $serializer->serialize($users, 'json');
        return new JsonResponse($usersJson, Response::HTTP_OK, [], true);
    }

    #[Route('/nsit-api/users/reset-password', name: 'users_reset_password', methods: ['POST'])]
    public function resetPassword(Request $request, MailerInterface $mailer, Environment $twig): JsonResponse
    {
        $payload = $request->getPayload();
        $userEmail = $payload->get('email') ?? null;

        if (!$userEmail) {
            return new JsonResponse(
                ['success' => false, 'message' => 'L\'Email doit être spécifié.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = $this->usersRepo->findOneBy(['email' => $userEmail]);

        if (!$user) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Aucun utilisateur trouvé.'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Génération du token de réinitialisation
        try {
            $resetToken = bin2hex(random_bytes(32));
            $expirationToken = new \DateTimeImmutable('+1 hour');

            $user->setResetPasswordToken($resetToken);
            $user->setResetPasswordTokenExpiration($expirationToken);

            $this->entityManager->persist($user);
            $this->entityManager->flush();


        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la génération du token.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $resetUrl = 'http://localhost:5173/reset-password?token=' . $resetToken;

        // Sauvegarde du token dans la base de données (implémentez selon votre modèle)
        // $user->setResetToken($resetToken);
        // $usersRepo->save($user, true);

        // Génération du contenu de l'email
        try {
            $emailContent = $twig->render('emails/reset_password.html.twig', [
                'user' => $user,
                'resetUrl' => $resetUrl,
            ]);
        } catch (\Twig\Error\Error $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de la génération de l\'email : ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Envoi de l'email
        try {
            $email = (new Email())
                ->from('no-reply@your-domain.com')
                ->to($userEmail)
                ->subject('Réinitialisation de votre mot de passe')
                ->html($emailContent);

            $mailer->send($email);
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Un email va vous être envoyé pour réinitialiser le mot de passe.',
        ]);
    }

    #[Route('/nsit-api/users/new-password', name: 'users_new_password', methods: ['POST'])]
    public function setNewPassword(Request $request, Sha256PasswordHasher $passwordHasher)
    {
        $payload = $request->getPayload();
        $newPassword = $payload->get('password');
        $token = $payload->get('token');

        $user = $this->usersRepo->findOneBy(['resetPasswordToken' => $token]);

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $user->setResetPasswordToken(null);
        $user->setResetPasswordTokenExpiration(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Mot de passe réinitialiser.']);


    }

}
