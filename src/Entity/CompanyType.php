<?php

namespace App\Entity;

use App\Repository\CompanyTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CompanyTypeRepository::class)]
class CompanyType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:collection:company'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:company', 'read:item:company'])]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'type')]
    private Collection $company;

    /**
     * @var Collection<int, Company>
     */


    public function __construct()
    {
        $this->company = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

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

    /**
     * @return Collection<int, Company>
     */
    public function getOrganizations(): Collection
    {
        return $this->company;
    }

    public function addOrganization(Company $company): static
    {
        if (!$this->company->contains($company)) {
            $this->company->add($company);
            $company->setType($this);
        }

        return $this;
    }

    public function removeOrganization(Company $company): static
    {
        if ($this->company->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getType() === $this) {
                $company->setType(null);
            }
        }

        return $this;
    }

}
