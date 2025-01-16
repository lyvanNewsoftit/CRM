<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\OrganizationType;
use App\Entity\Status;
use App\Entity\Users;
use App\Repository\OrganizationTypeRepository;
use App\Repository\StatusRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly StatusRepository $statusRepo, private readonly OrganizationTypeRepository $organizationTypeRepo)
    {
    }

    public function load(ObjectManager $manager): void
    {

        // charger fixtures sans effacer la base: php bin/console doctrine:fixtures:load --append

        // Fixtures: OrganizationType
        $organizationTypesArray = [
            [
                "label" => "Revendeur",
                "description" => ""
            ],
            [
                "label" => "Client",
                "description" => ""
            ],
            [
                "label" => "Editeur",
                "description" => ""
            ],
        ];
        foreach ($organizationTypesArray as $data) {
            $organizationType = new OrganizationType();
            $organizationType->setLabel($data["label"]);
            $organizationType->setDescription($data["description"]);
            $this->entityManager->persist($organizationType);
        }

        //Fixtures: Status
        $statusArray = [
            [
                'name' => 'Actif',
                'involvedTable' => ['Organization', 'Users']
            ],
            [

                'name' => 'Inactif',
                'involvedTable' => ['Organization', 'Users']
            ]
        ];
        foreach ($statusArray as $data) {
            $newStatus = new Status();
            $involvedTableArray = [];
            $newStatus->setName($data['name']);
            foreach ($data['involvedTable'] as $involvedTable) {
                $involvedTableArray[] = $involvedTable;
            }
            $newStatus->setInvolvedTable($involvedTableArray);
            $this->entityManager->persist($newStatus);
        }

        //Fixtures: Organization
        $organizationDatasFile = __DIR__ . '/FixturesFiles/organizationFixtures.json';
        $organizationDatas = json_decode((file_get_contents($organizationDatasFile)), true);
        $statuses = $this->statusRepo->findAll();
        $types = $this->organizationTypeRepo->findAll();

        foreach ($organizationDatas as $index => $item) {
            $organization = new Organization();
            $organization->setName($item['name']);
            $organization->setAddress($item['address']);
            $organization->setPostalCode($item['postalCode']);
            $organization->setCity($item['city']);
            $organization->setPhoneNumber($item['phoneNumber']);
            $organization->setEmail($item['email']);
            $organization->setDescription($item['description']);
            $organization->setSiret($item['siret']);
            $organization->setTvaIntra($item['tvaIntra']);
            $organization->setSalesRevenue($item['salesRevenue']);
            $organization->setEffectif($item['effectif']);
            // Alterner entre les statuts
            if ($index % 2) {
                // Modulo pour alterner cycliquement
                $organization->setStatus($statuses[0]);
                $organization->setType($types[0]);
            } else {
                $organization->setStatus($statuses[1]);
                $organization->setType($types[1]);
            }


            $this->entityManager->persist($organization);
        }

        $this->entityManager->flush();

    }
}
