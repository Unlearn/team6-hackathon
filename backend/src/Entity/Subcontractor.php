<?php

namespace App\Entity;

use App\Entity\Employee;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'subcontractors')]
class Subcontractor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(type: 'string', length: 11)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^\d{11}$/',
        message: 'ABN must be 11 digits'
    )]
    private string $abn;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    private string $slug;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email(message: 'Please enter a valid email address')]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\ManyToMany(targetEntity: Trade::class, inversedBy: 'subcontractors')]
    #[ORM\JoinTable(name: 'subcontractor_trade')]
    private Collection $trades;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $documents = null;

    #[ORM\OneToMany(targetEntity: Employee::class, mappedBy: 'subcontractor', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $employees;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Employee $mainContactEmployee = null;

    #[ORM\Column(type: 'integer')]
    private int $currentStep = 1;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->trades = new ArrayCollection();
        $this->employees = new ArrayCollection();
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

    public function getAbn(): string
    {
        return $this->abn;
    }

    public function setAbn(string $abn): self
    {
        $this->abn = $abn;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCurrentStep(): int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(int $currentStep): self
    {
        $this->currentStep = $currentStep;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): self
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getTrades(): Collection
    {
        return $this->trades;
    }

    public function addTrade(Trade $trade): self
    {
        if (!$this->trades->contains($trade)) {
            $this->trades[] = $trade;
        }

        return $this;
    }

    public function removeTrade(Trade $trade): self
    {
        $this->trades->removeElement($trade);

        return $this;
    }

    public function getDocuments(): ?array
    {
        return $this->documents;
    }

    public function setDocuments(?array $documents): self
    {
        $this->documents = $documents;
        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): self
    {
        if (!$this->employees->contains($employee)) {
            $this->employees[] = $employee;
            $employee->setSubcontractor($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): self
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getSubcontractor() === $this) {
                $employee->setSubcontractor(null);
            }
        }

        return $this;
    }

    public function getMainContactEmployee(): ?Employee
    {
        return $this->mainContactEmployee;
    }

    public function setMainContactEmployee(?Employee $mainContactEmployee): self
    {
        $this->mainContactEmployee = $mainContactEmployee;
        return $this;
    }
}
