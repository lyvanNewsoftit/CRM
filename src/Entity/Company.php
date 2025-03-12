<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity(fields:['siret'], message: 'A Company with this Siret is already registered.')]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:collection:company','read:item:company'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:company','read:item:company', 'read:item:user'])]
    #[NotBlank(message: 'Company name must be specified.')]
    #[Length(min: 3, max: 30, minMessage: 'Company name must be at least 3 characters long.', maxMessage: 'Company name cannot exceed 30 characters long.')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['read:collection:company','read:item:company'])]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    #[Groups(['read:collection:company','read:item:company'])]
    #[Assert\NotBlank(message: 'Company postal code must be specified.')]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:company','read:item:company'])]
    #[Assert\NotBlank(message: 'Company city must be specified.')]
    private ?string $city = null;

    #[ORM\Column(length: 10)]
    #[Groups(['read:collection:company','read:item:company'])]
    #[Length(exactly: 10, exactMessage: 'The Phone number must be 10 digits long.')]
    #[Regex('/^\d+$/', message: 'This field can only contain numbers.')]
    #[Assert\NotBlank(message: 'Company phone number must be specified.')]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:company','read:item:company'])]
    #[Assert\Length(max: 255, maxMessage: 'Email cannot exceed 255 characters long.')]
    #[Assert\NotBlank(message: 'Email must be specified.')]
    #[Assert\Email(message: 'Please enter a valid email.')]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['read:item:company'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:item:company', 'read:collection:company'])]
    #[NotBlank(message: 'Siret number must be specified.')]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:item:company'])]
    #[NotBlank(message: 'TVA number must be specified.')]
    private ?string $tvaIntra = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:collection:company', 'read:item:company'])]
    private ?float $salesRevenue = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['read:item:company'])]
    #[Regex('/^\d+$/', message: 'This field can only contain numbers.')]
    private ?int $effectif = null;

    #[ORM\ManyToOne(inversedBy: 'organizations')]
    #[Groups(['read:collection:company', 'read:item:company'])]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(message: 'Company status must be specified.')]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'organizations')]
    #[Groups(['read:collection:company', 'read:item:company'])]
    #[ORM\JoinColumn(nullable: false)]
    #[NotBlank(message: 'Company type must be specified.')]
    private ?CompanyType $type = null;

    /**
     * @var Collection<int, Users>
     */
    #[ORM\OneToMany(targetEntity: Users::class, mappedBy: 'company')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

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

    public function getType(): ?CompanyType
    {
        return $this->type;
    }

    public function setType(?CompanyType $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, Users>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setCompany($this);
        }

        return $this;
    }

    public function removeUser(Users $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getCompany() === $this) {
                $user->setCompany(null);
            }
        }

        return $this;
    }
}
