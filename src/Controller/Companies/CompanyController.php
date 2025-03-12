<?php

namespace App\Controller\Companies;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\CompanyTypeRepository;
use App\Repository\StatusRepository;
use App\Security\Voter\CompanyVoter;
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

class CompanyController extends AbstractController
{
    public function __construct(private readonly ValidatorInterface $validator, private readonly EntityManagerInterface $entityManager, private readonly CompanyRepository $companyRepo)
    {
    }

    #[Route('/crm-api/company', name: 'app_company', methods: ['POST'])]
    public function createCompany(Request $request, CompanyServices $companyServices): JsonResponse
    {

        $company = new Company();

        // Appel fonction du service associé à l'entité.
        $payload = $companyServices->getPayloadFields($request->getPayload());

        foreach ($payload as $field => $value) {
            // Pas de setter pour  la propriété id ni users
            if ($field === 'id' || $field === 'users') {
                continue;
            }

            $setter = 'set' . ucfirst($field);
            $company->$setter($value ? $value : '');
        }

        //Gestion des erreurs.
        $errors = [];
        $violations = $this->validator->validate($company);

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();

            if (!isset($errors[$propertyPath])) {
                $errors[$propertyPath] = [];
            }
            $errors[$propertyPath][] = $violation->getMessage();
        }

        if (count($errors) > 0) {
            return new JsonResponse(
                ['success' => false, 'message' => 'An error occured.', 'errors' => $errors],
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [],
                false
            );
        }

        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return new JsonResponse(
            ['success' => true, 'message' => 'A new Company has been registered.'],
            Response::HTTP_OK,
            [],
            false
        );
    }

    #[Route('/crm-api/company', name: 'get_company_list', methods: ['GET'])]
    public function getCompany(Request $request, SerializerInterface $serializer): JsonResponse
    {
        // Vérifier que l'utilisateur a le rôle ROLE_SUPER_ADMIN
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');


        $companyList = $this->companyRepo->findAll();
        $companyListJson = $serializer->serialize($companyList, 'json', ['groups' => 'read:collection:company']);
        return new JsonResponse($companyListJson, Response::HTTP_OK, [], true);
    }

    #[Route('crm-api/company/{id}', name: 'get_one_company', methods: ['GET'])]
    public function getOneUser(int $id, Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $company = $this->companyRepo->find($id);

        if (!$company) {
            return new JsonResponse(['success' => false, 'message' => 'Company not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->denyAccessUnlessGranted(CompanyVoter::VIEW, $company);

        $companyJson = $serializer->serialize($company, 'json', ['groups' => 'read:item:company']);
        return new JsonResponse($companyJson, Response::HTTP_OK, [], true);

    }

}
