<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Expense;
use App\Entity\Revenue;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "Date cannot be null")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Account $account_id = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Amount cannot be blank.")]
    #[Assert\PositiveOrZero(message: "Amount must be zero or positive.")]
    private ?float $amount = null;

    #[ORM\OneToMany(targetEntity: Expense::class, mappedBy: 'transaction', cascade: ['persist', 'remove'])]
    private Collection $expenses;

    // NEW: Add OneToMany for revenues
    #[ORM\OneToMany(targetEntity: Revenue::class, mappedBy: 'transaction', cascade: ['persist', 'remove'])]
    private Collection $revenues;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Type cannot be blank")]
    private ?string $Type = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $uuid = null;

    #[ORM\Column(length: 10, nullable: false)]
    private ?string $isExpenseOrRevenue = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $category = null;

    public function __construct()
    {
        $this->expenses = new ArrayCollection();
        $this->revenues = new ArrayCollection(); // initialize revenues
    }

    public function getIsExpenseOrRevenue(): ?string
    {
        return $this->isExpenseOrRevenue;
    }

    public function setIsExpenseOrRevenue(?string $value): self
    {
        $this->isExpenseOrRevenue = $value;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $cat): self
    {
        $this->category = $cat;
        return $this;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(?Uuid $uuid): static
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return Collection<int, Expense>
     */
    public function getExpenses(): Collection
    {
        return $this->expenses;
    }

    public function addExpense(Expense $expense): static
    {
        if (!$this->expenses->contains($expense)) {
            $this->expenses[] = $expense;
            $expense->setTransaction($this);
        }
        return $this;
    }

    public function removeExpense(Expense $expense): static
    {
        if ($this->expenses->removeElement($expense)) {
            if ($expense->getTransaction() === $this) {
                $expense->setTransaction(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Revenue>
     */
    public function getRevenues(): Collection
    {
        return $this->revenues;
    }

    public function addRevenue(Revenue $revenue): static
    {
        if (!$this->revenues->contains($revenue)) {
            $this->revenues[] = $revenue;
            $revenue->setTransaction($this);
        }
        return $this;
    }

    public function removeRevenue(Revenue $revenue): static
    {
        if ($this->revenues->removeElement($revenue)) {
            if ($revenue->getTransaction() === $this) {
                $revenue->setTransaction(null);
            }
        }
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
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

    public function getAccountId(): ?Account
    {
        return $this->account_id;
    }

    public function setAccountId(?Account $account_id): static
    {
        $this->account_id = $account_id;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): static
    {
        $this->Type = $Type;
        return $this;
    }
}
