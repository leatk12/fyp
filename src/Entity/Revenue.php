<?php

namespace App\Entity;

use App\Repository\RevenueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use App\Entity\Transaction;

#[ORM\Entity(repositoryClass: RevenueRepository::class)]
class Revenue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $source = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(nullable: true)]
    private ?bool $spendingConstraints = null;

    #[ORM\Column(length: 1026, nullable: true)]
    private ?string $actualConstraints = null;

    #[ORM\Column(type: 'uuid', unique: false, nullable: true)]
    private ?Uuid $draftUuid = null;

    // NEW: Add the ManyToOne relationship to Transaction
    #[ORM\ManyToOne(targetEntity: Transaction::class, inversedBy: 'revenues')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Transaction $transaction = null;

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): static
    {
        $this->transaction = $transaction;
        return $this;
    }

    public function getDraftUuid(): ?Uuid
    {
        return $this->draftUuid;
    }

    public function setdraftUuid(?Uuid $uuid): static
    {
        $this->draftUuid = $uuid;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }



    public function getSpendingConstraints(): ?bool
    {
        return $this->spendingConstraints;
    }


    public function setSpendingConstraints(bool $spendingConstraints): static
    {
        $this->spendingConstraints = $spendingConstraints;
        return $this;
    }

    public function getActualConstraints(): ?string
    {
        return $this->actualConstraints;
    }

    public function setActualConstraints(?string $actualConstraints): static
    {
        $this->actualConstraints = $actualConstraints;
        return $this;
    }
}
