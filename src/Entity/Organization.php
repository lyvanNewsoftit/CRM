<?php

namespace App\Entity;

use App\Repository\OrganizationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[UniqueEntity(fields:['siret'], message: 'Un compte avec ce siret est déjà enregistré.')]
class Organization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:collection:organization'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:organization'])]
    #[NotBlank(message: 'Le nom doit être spécifié.')]
    #[Length(min: 3, max: 30, minMessage: 'Le nom doit avoit minimum 3 caractères.', maxMessage: 'Le no ne peut excéder 30 caractères.')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:collection:organization'])]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    #[Groups(['read:collection:organization'])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:organization'])]
    private ?string $city = null;

    #[ORM\Column(length: 10)]
    #[Groups(['read:collection:organization'])]
    #[Length(exactly: 10, exactMessage: 'Le numéro de téléphone doit comporter 10 chiffres.')]
    #[Regex('/^\d+$/', message: 'Ce champ ne peut contenir que des chiffres.')]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:organization'])]
    #[Assert\Length(max: 255, maxMessage: 'L\email ne peut exéder 255 caractères.')]
    #[Assert\NotBlank(message: 'L\'email doit être spécifié.')]
    #[Assert\Email(message: 'Merci de spécifier un email valide.')]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read:collection:organization'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:organization'])]
    #[NotBlank(message: 'Le numéro Siret doit être spécifié.')]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:organization'])]
    #[NotBlank(message: 'Le numéro TVA doit être spécifié.')]
    private ?string $tvaIntra = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:collection:organization'])]
    private ?float $salesRevenue = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:collection:organization'])]
    #[Regex('/^\d+$/', message: 'Ce champ ne peut contenir que des chiffres.')]
    private ?int $effectif = null;

    #[ORM\ManyToOne(inversedBy: 'organizations')]
    #[Groups(['read:collection:organization'])]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(message: 'Le statut du Compte doit être spécifié.')]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'organizations')]
    #[Groups(['read:collection:organization'])]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(message: 'Le type du Compte doit être spécifié.')]
    private ?OrganizationType $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getTvaIntra(): ?string
    {
        return $this->tvaIntra;
    }

    public function setTvaIntra(string $tvaIntra): static
    {
        $this->tvaIntra = $tvaIntra;

        return $this;
    }

    public function getSalesRevenue(): ?float
    {
        return $this->salesRevenue;
    }

    public function setSalesRevenue(?float $salesRevenue): static
    {
        $this->salesRevenue = $salesRevenue;

        return $this;
    }

    public function getEffectif(): ?int
    {
        return $this->effectif;
    }

    public function setEffectif(?int $effectif): static
    {
        $this->effectif = $effectif;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?OrganizationType
    {
        return $this->type;
    }

    public function setType(?OrganizationType $type): static
    {
        $this->type = $type;

        return $this;
    }
}
