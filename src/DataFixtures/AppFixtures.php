<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\CompanyType;
use App\Entity\Status;
use App\Entity\Users;
use App\Repository\CompanyTypeRepository;
use App\Repository\StatusRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;


class AppFixtures extends Fixture
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly StatusRepository $statusRepo, private readonly CompanyTypeRepository $companyTypeRepo)
    {
    }

    public function load(ObjectManager $manager): void
    {

        // charger fixtures sans effacer la base: php bin/console doctrine:fixtures:load --append

        // Fixtures: CompanyType
        $companyTypesArray = [
            [
                'label' => 'Advertisers',
                'description' => ''
            ],
            [
                'label' => 'Publishers',
                'description' => ''
            ]
        ];
        foreach ($companyTypesArray as $data) {
            $companyType = new CompanyType();
            $companyType->setLabel($data['label']);
            $companyType->setDescription($data['description']);
            $this->entityManager->persist($companyType);
        }

        //Fixtures: Status
        $statusArray = [
            [
                'name' => 'Activ',
                'involvedTable' => ['Company', 'Users']
            ],
            [
                'name' => 'Inactiv',
                'involvedTable' => ['Company', 'Users']
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

        //Fixtures: Company
        $companyDatasFile = __DIR__ . '/FixturesFiles/CompanyFixtures.json';
        $companyDatas = json_decode((file_get_contents($companyDatasFile)), true);
        $statuses = $this->statusRepo->findAll();
        $types = $this->companyTypeRepo->findAll();

        foreach ($companyDatas as $index => $item) {
            $company = new Company();
            $company->setName($item['name']);
            $company->setAddress($item['address']);
            $company->setPostalCode($item['postalCode']);
            $company->setCity($item['city']);
            $company->setPhoneNumber($item['phoneNumber']);
            $company->setEmail($item['email']);
            $company->setDescription($item['description']);
            $company->setSiret($item['siret']);
            $company->setTvaIntra($item['tvaIntra']);
            $company->setSalesRevenue($item['salesRevenue']);
            $company->setEffectif($item['effectif']);
            // Alterner entre les statuts
            if ($index % 2) {
                // Modulo pour alterner cycliquement
                $company->setStatus($statuses[0]);
                $company->setType($types[0]);

            } else {
                $company->setStatus($statuses[1]);
                $company->setType($types[1]);
            }


            $this->entityManager->persist($company);
        }

        $this->entityManager->flush();

    }
}
