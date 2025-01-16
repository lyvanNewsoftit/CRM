<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\OneToMany(targetEntity: Organization::class, mappedBy: 'status')]
    private Collection $organizations;

    #[ORM\Column(type: 'json')]
    private array $involvedTable = [];



    public function __construct()
    {
        $this->organizations = new ArrayCollection();
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
     * @return Collection<int, Organization>
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): static
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
            $organization->setStatus($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): static
    {
        if ($this->organizations->removeElement($organization)) {
            // set the owning side to null (unless already changed)
            if ($organization->getStatus() === $this) {
                $organization->setStatus(null);
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
