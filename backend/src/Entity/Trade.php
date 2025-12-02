<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'trades')]
class Trade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\ManyToMany(targetEntity: Subcontractor::class, mappedBy: 'trades')]
    private Collection $subcontractors;

    public function __construct()
    {
        $this->subcontractors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSubcontractors(): Collection
    {
        return $this->subcontractors;
    }

    public function addSubcontractor(Subcontractor $subcontractor): self
    {
        if (!$this->subcontractors->contains($subcontractor)) {
            $this->subcontractors[] = $subcontractor;
            $subcontractor->addTrade($this);
        }

        return $this;
    }

    public function removeSubcontractor(Subcontractor $subcontractor): self
    {
        if ($this->subcontractors->removeElement($subcontractor)) {
            $subcontractor->removeTrade($this);
        }

        return $this;
    }
}
