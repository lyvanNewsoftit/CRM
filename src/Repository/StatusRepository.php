<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Status>
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Status::class);
    }

    public function findByInvolvedTable(string $name): array
    {
        $involvedStatusArray = []; // Ce tableau contiendra les statuts associés à une table donnée (par exemple, 'Company').

        //  Requête SQL qui cherche des statuts dans la table 'status' qui sont associés à une table spécifique
        // Elle utilise une sous-requête pour analyser les éléments JSON dans la colonne 'involved_table'.
        $sql = "SELECT id, name, involved_table FROM status WHERE EXISTS(SELECT 1 FROM json_array_elements_text(status.involved_table) AS elem WHERE elem.value= :value)";
        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('value', $name);
        $results =$query->executeQuery()->fetchAllAssociative();


        foreach ($results as $result) {
            $involvedStatusArray[$result['id']]['id'] = $result['id'];

            if($involvedStatusArray[$result['id']]){
                $involvedStatusArray[$result['id']]['name'] = $result['name'];
            }
        }
       return $involvedStatusArray;
    }

    //    /**
    //     * @return Status[] Returns an array of Status objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Status
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
