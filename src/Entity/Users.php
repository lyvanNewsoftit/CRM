<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: ('Cet email est déjà utilisé, merci d\'en utiliser un autre.'))]
class Users implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idUser = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255, maxMessage: 'L\email ne peut exéder 255 caractères.')]
    #[Assert\NotBlank(message: 'L\'email doit être spécifié.')]
    #[Assert\Email(message: 'Merci de spécifier un email valide.')]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\length(min: 5, max: 20, minMessage: 'Le prénom doit avoir 5 caractères minimum.', maxMessage: 'Le prénom ne peut exéder 20 caractères.')]
    #[Assert\NotBlank(message: 'Le prénom doit être spécifié.')]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\length(min: 5, max: 20, minMessage: 'Le nom doit avoir 5 caractères minimum.', maxMessage: 'Le nom ne peut exéder 20 caractères.')]
    #[Assert\NotBlank(message: 'Le nom doit être spcifié.')]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe doit être spécifié.')]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetPasswordTokenExpiration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $totpSecret = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles ?? [];

        // 'ROLE_USER' par défaut
        $roles[] = 'ROLE_USER';

        // Supprime les doublons au cas où
        return array_unique($roles);
    }


    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $resetPasswordToken): static
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getResetPasswordTokenExpiration(): ?\DateTimeImmutable
    {
        return $this->resetPasswordTokenExpiration;
    }

    public function setResetPasswordTokenExpiration(?\DateTimeImmutable $resetPasswordTokenExpiration): static
    {
        $this->resetPasswordTokenExpiration = $resetPasswordTokenExpiration;

        return $this;
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $totpSecret): static
    {
        $this->totpSecret = $totpSecret;

        return $this;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        /*
         * Cette méthode doit retourner un booléen. Si la 2FA (Google Authenticator) est toujours
         * activée pour tous les utilisateurs,  retourner true dans cette méthode.*/
        return true;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        // Utiliser l'email de l'utilisateur comme identifiant pour TOTP
        return $this->getEmail();
    }

//    public function getTotpAuthenticationConfiguration(): TotpConfigurationInterface|null
//    {
//        // Retourne une configuration nécessaire pour TOTP, typiquement le secret de l'utilisateur.
//
//        return new TotpConfiguration(
//            $this->getGoogleAuthenticatorSecret(),
//            $this->getGoogleAuthenticatorUsername(),
//            3600,
//            6
//        );
//    }
}
