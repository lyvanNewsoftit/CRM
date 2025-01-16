<?php

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Repository\OrganizationTypeRepository;
use App\Repository\StatusRepository;
use ReflectionClass;
use ReflectionProperty;

class OrganizationServices
{
    public function __construct(private readonly StatusRepository $statusRepo, private readonly OrganizationTypeRepository $organizationTypeRepo)
    {
    }

    public function getPayloadFields($payload): array
    {
        $finalArray = [];

        $reflection = new ReflectionClass(Organization::class);
        $entityGeneralPropertiesArray = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);

        $entityPropertiesArray = [];

        foreach ($entityGeneralPropertiesArray as $entityProperty) {

            $entityPropertiesArray[] = $entityProperty->getName();
        }
        foreach ($entityPropertiesArray as $fieldName) {
            if ($fieldName === 'status') {
                //Récupération du status(type Entity\Status) à partir de la méthode custom du repo.
                $status = $this->statusRepo->findByInvolvedTable('Organization');
                $idStatus = $payload->get('status');
                $selectedStatus = $this->statusRepo->find($status[$idStatus]['id']);
                $finalArray[$fieldName] = $selectedStatus;
                continue;
            }

            if($fieldName === 'type'){
                //Récupération du type (type Entity\OrganizationType) à partir du repo
                $type = $this->organizationTypeRepo->find($payload->get($fieldName));
                $finalArray[$fieldName] = $type;
                continue;
            }

            $finalArray[$fieldName] = $payload->get($fieldName);
        }

        return $finalArray;
    }
}