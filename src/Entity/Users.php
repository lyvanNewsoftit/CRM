<?php

namespace App\Entity;

use App\Enum\UserRole;
use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: ('This Email already exist. Please enter an other one.'))]
class Users implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:item:user', 'read:collection:user'])]
    private ?int $idUser = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['read:item:user', 'read:collection:user'])]
    #[Assert\Choice(callback: [UserRole::class, 'getAvailableRoles'], multiple: true, message: 'Invalid role selected.')]
    #[Assert\NotBlank(message: 'Role must be specified.')]
    private array $roles = [UserRole::USER->value];

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255, maxMessage: 'Email cannot exceed 255 characters long.')]
    #[Assert\NotBlank(message: 'Email must be specified.')]
    #[Assert\Email(message: 'Please enter a valid email.')]
    #[Groups(['read:item:user', 'read:collection:user'])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\length(min: 5, max: 20, minMessage: 'Firstname must be at least 5 characters long.', maxMessage: 'Firstname cannot exceed 20 characters long.')]
    #[Assert\NotBlank(message: 'Firstname must be specified.')]
    #[Groups(['read:item:user', 'read:collection:user'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\length(min: 5, max: 20, minMessage: 'Lastname must be at least 5 characters long', maxMessage: 'Lastname cannot exceed 20 characters long.')]
    #[Assert\NotBlank(message: 'Lastname must be specified.')]
    #[Groups(['read:item:user', 'read:collection:user'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'password must be specified.')]
    private ?string $password = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetPasswordTokenExpiration = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $totpSecret = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'users')]
    #[Groups(['read:item:user', 'read:collection:user'])]
    #[Assert\NotBlank(message: 'Company must be specified.')]
    private ?Company $company = null;

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

public function getCompany(): ?Company
{
    return $this->company;
}

public function setCompany(?Company $company): static
{
    $this->company = $company;

    return $this;
}
//public function __toString(): string
//{
//   return $this->getEmail();
//}
}
