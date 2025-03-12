<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read:collection:company'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection:company','read:item:company'])]
    #[Assert\NotBlank(message:  'Status name must be specified.')]
    #[Assert\Length(min: 5, max: 255, minMessage: 'Status name must be at least 5 characters long.', maxMessage: 'Status name cannot exceed 255 characters long.')]
    private ?string $name = null;

    /**
     * @var Collection<int, Company>
     */
    #[ORM\OneToMany(targetEntity: Company::class, mappedBy: 'status')]
    private Collection $company;

    #[ORM\Column(type: 'json')]
    private array $involvedTable = [];



    public function __construct()
    {
        $this->company = new ArrayCollection();
        $this->involvedTable = [];
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

    /**
     * @return Collection<int, Company>
     */
    public function getCompany(): Collection
    {
        return $this->company;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->company->contains($company)) {
            $this->company->add($company);
            $company->setStatus($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->company->removeElement($company)) {
            // set the owning side to null (unless already changed)
            if ($company->getStatus() === $this) {
                $company->setStatus(null);
            }
        }

        return $this;
    }

    public function getInvolvedTable(): array
    {
        return $this->involvedTable;
    }

    public function setInvolvedTable(array $involvedTable): static
    {
        $this->involvedTable = $involvedTable;

        return $this;
    }

}
