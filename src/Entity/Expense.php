<?php

namespace App\Entity;

use App\Repository\ExpenseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ExpenseRepository::class)]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Supplier $supplier_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateIncurred = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?float $amount = null;

    #[ORM\ManyToOne]
    private ?Account $account_id = null;

    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: 'expenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transaction $transaction = null;

    #[ORM\Column(type: 'uuid', nullable: true)]
    private ?Uuid $draftUuid = null;

    public function getDraftUuid(): ?Uuid
    {
        return $this->draftUuid;
    }

    public function setDraftUuid(?Uuid $uuid): static
    {
        $this->draftUuid = $uuid;
        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): static
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSupplierId(): ?Supplier
    {
        return $this->supplier_id;
    }

    public function setSupplierId(?Supplier $supplier_id): static
    {
        $this->supplier_id = $supplier_id;
        return $this;
    }

    public function getDateIncurred(): ?\DateTimeInterface
    {
        return $this->dateIncurred;
    }

    public function setDateIncurred(\DateTimeInterface $dateIncurred): static
    {
        $this->dateIncurred = $dateIncurred;
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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): static
    {
        $this->amount = $amount;
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
}
