<?php

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Repository\OrganizationTypeRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationController extends AbstractController
{
    public function __construct(private readonly ValidatorInterface $validator, private readonly EntityManagerInterface $entityManager, private readonly OrganizationRepository $organizationRepo)
    {
    }

    #[Route('/nsit-api/organization', name: 'app_organization', methods: ['POST'])]
    public function createOrganization(Request $request, OrganizationServices $organizationServices): JsonResponse
    {

        $organization = new Organization();

        // Appel fonction du service associé à l'entité.
        $payload = $organizationServices->getPayloadFields($request->getPayload());

        foreach ($payload as $field => $value) {
            // Pas de setter pour  la propriété id
            if ($field === 'id') {
                continue;
            }

            $setter = 'set' . ucfirst($field);
            $organization->$setter($value ? $value : '');
        }

        //Gestion des erreurs.
        $errors = [];
        $violations = $this->validator->validate($organization);

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();

            if (!isset($errors[$propertyPath])) {
                $errors[$propertyPath] = [];
            }
            $errors[$propertyPath][] = $violation->getMessage();
        }

        if (count($errors) > 0) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Une erreur est survenue.', 'errors' => $errors],
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [],
                false
            );
        }

        $this->entityManager->persist($organization);
        $this->entityManager->flush();

        return new JsonResponse(
            ['success' => true, 'message' => 'Un nouveau Compte à été créer.'],
            Response::HTTP_OK,
            [],
            false
        );
    }

    #[Route('/nsit-api/organization', name: 'get_organization_list', methods: ['GET'])]
    public function getOrganizations(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $organizationsList = $this->organizationRepo->findAll();
        $organizationsListJson = $serializer->serialize($organizationsList, 'json', ['groups' => 'read:collection:organization']);
        return new JsonResponse($organizationsListJson, Response::HTTP_OK, [], true);
    }
}
