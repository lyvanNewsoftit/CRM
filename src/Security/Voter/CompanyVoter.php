<?php

namespace App\Security\Voter;

use App\Entity\Company;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CompanyVoter extends Voter
{
    public const EDIT = 'EDIT';
    public const VIEW = 'VIEW';
    public const DELETE = 'DELETE';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {

        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE]) && (method_exists($subject, 'getCompany') || $subject instanceof Company);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {

        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // 🏆 Le SUPER ADMIN peut tout faire
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // Si $subject est company: vérifier que c'est le role admin qui fait la requete et aussi que l'admin qui fait la requete veut voir les données de la company concernée
        if($subject instanceof Company) {
            if($this->security->isGranted('ROLE_ADMIN') && $user->getCompany() === $subject) {
                return true;
            }
                return false;
        }



        if($user->getCompany() !== $subject->getCompany()) {
            return false;
        }



        // Role_USER peut juste voir les données
        if ($attribute === self::VIEW) {
            return $this->security->isGranted('ROLE_USER') || $this->security->isGranted('ROLE_ADMIN');
        }

        // ✏️ ROLE_ADMIN peut éditer et supprimer les données de son entreprise
        if (in_array($attribute, [self::EDIT, self::DELETE], true)) {
            return $this->security->isGranted('ROLE_ADMIN');
        }

        return false;
    }
}
