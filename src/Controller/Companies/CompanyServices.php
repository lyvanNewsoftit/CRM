<?php

namespace App\Controller\Companies;

use App\Entity\Company;
use App\Repository\CompanyTypeRepository;
use App\Repository\StatusRepository;
use ReflectionClass;
use ReflectionProperty;

class CompanyServices
{
    public function __construct(private readonly StatusRepository $statusRepo, private readonly CompanyTypeRepository $organizationTypeRepo)
    {
    }

    public function getPayloadFields($payload): array
    {
        $finalArray = [];

        //  Utilisation de la réflexion pour inspecter la classe 'Company' et obtenir ses propriétés privées.
        // Cela nous permet de parcourir dynamiquement les propriétés de l'entité 'Company' sans avoir besoin de les définir manuellement.
        $reflection = new ReflectionClass(Company::class);
        $entityGeneralPropertiesArray = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        // Création d'un tableau contenant les noms des propriétés privées de l'entité 'Company'. on extrait le name de chaque objet reflectionProperty
        $entityPropertiesArray = [];

        foreach ($entityGeneralPropertiesArray as $entityProperty) {

            $entityPropertiesArray[] = $entityProperty->getName();
        }

        //  Parcours de chaque propriété pour récupérer sa valeur dans le payload.
        foreach ($entityPropertiesArray as $fieldName) {
          //  Si le champ est 'status', on le traite différemment car il s'agit d'une relation avec l'entité 'Status'.
            if ($fieldName === 'status') {
                //Récupération du status(type Entity\Status) à partir de la méthode custom du repo.
                $status = $this->statusRepo->findByInvolvedTable('Company'); // Réxupère tous les status utilisé par la table company
                $idStatus = $payload->get('status');
                $selectedStatus = $this->statusRepo->find($status[$idStatus]['id']);
                $finalArray[$fieldName] = $selectedStatus;
                continue;
            }

            // Si le champ est 'type', on le traite différemment pour récupérer le type de l'entreprise depuis le repository 'CompanyTypeRepository'.
            if($fieldName === 'type'){
                //Récupération du type (type Entity\CompanyType) à partir du repo
                $type = $this->organizationTypeRepo->find($payload->get($fieldName));
                $finalArray[$fieldName] = $type;
                continue;
            }
            // Pour toutes les autres propriétés, on récupère simplement la valeur du champ depuis le payload.
            $finalArray[$fieldName] = $payload->get($fieldName);
        }

        return $finalArray;
    }
}