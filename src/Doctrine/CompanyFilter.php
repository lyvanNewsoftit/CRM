<?php

namespace App\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class CompanyFilter extends SQLFilter
{

    /**
     * @inheritDoc
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        // Si super admin on applique pas de filtre sur les requetes
        if($this->hasParameter('ROLE_SUPER_ADMIN')){
            return '';
        }

        // vérifier si l'entité cible à une relation avec company
        if ($targetEntity->hasAssociation('company')) {
            // Récupérer l'id de l'entreprise passer en paramètre depuis le controlleur.
            // Vérifie si le paramètre 'user' est défini avant de l'utiliser
            if ($this->hasParameter('companyId')) {
                $companyId = (int)trim($this->getParameter('companyId'), "'");

                // Applique un filtre basé sur l'ID de l'entreprise
                return sprintf('%s.company_id = %d', $targetTableAlias, $companyId);
            } else {
                return '';
            }
        }
        // Si on n'a pas encore de paramètre 'companyId', on ne filtre pas
        return '';
    }
}