<?php
namespace App\Controller;

// src/Controller/UserController.php

namespace App\Controller;

use App\Entity\Users;
use App\Security\Hasher\Sha256PasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UsersController extends AbstractController
{
    private $passwordHasher;

    public function __construct(Sha256PasswordHasher $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/user/create", name="user_create")
     */
    #[Route('/nsit-api/users', name: 'users', methods: ['POST'])]
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
}
