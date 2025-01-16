<?php

namespace App\DataFixtures;

use App\Entity\OrganizationType;
use App\Entity\Status;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
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

        foreach ($statusArray as $data){
            $newStatus = new Status();
            $involvedTableArray = [];
            $newStatus->setName($data['name']);
            foreach ($data['involvedTable'] as $involvedTable){
                $involvedTableArray[] = $involvedTable;
            }
            $newStatus->setInvolvedTable($involvedTableArray);
            $this->entityManager->persist($newStatus);
        }

        $this->entityManager->flush();


    }
}
