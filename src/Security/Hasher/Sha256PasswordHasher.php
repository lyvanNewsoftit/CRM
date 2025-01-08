<?php
namespace App\Security\Hasher;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


class Sha256PasswordHasher implements UserPasswordHasherInterface{
    public function hashPassword(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): string
    {
        // Ajoutez un sel ici si nécessaire
        $salt = bin2hex(random_bytes(32)); // Générer un sel aléatoire
        $passwordWithSalt = $plainPassword . $salt;  // Ajouter le sel au mot de passe
        return hash('sha256', $passwordWithSalt) . ':' . $salt; // Retourner le hachage avec le sel
    }

    public function isPasswordValid(PasswordAuthenticatedUserInterface $user, #[\SensitiveParameter] string $plainPassword): bool
    {
        // Récupérer le hachage et le sel stockés
        list($storedHash, $salt) = explode(':', $user->getPassword()); // Supposons que le mot de passe stocké soit sous forme de "hash:salt"
        // Ajouter le sel au mot de passe fourni
        $passwordWithSalt = $plainPassword . $salt;
        // Hacher la combinaison
        $hashedPassword = hash('sha256', $passwordWithSalt);
        // Comparer les hachages
        return $hashedPassword === $storedHash;
    }

    public function needsRehash(PasswordAuthenticatedUserInterface $user): bool
    {
        // TODO: Implement needsRehash() method.
        return false;
    }
}