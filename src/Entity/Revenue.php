<?php

namespace App\Entity;

use App\Repository\RevenueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\Column]
    private ?bool $spending_constraints = null;

    #[ORM\Column(length: 1026, nullable: true)]
    private ?string $actual_constraints = null;

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

    public function isSpendingConstraints(): ?bool
    {
        return $this->spending_constraints;
    }

    public function setSpendingConstraints(bool $spending_constraints): static
    {
        $this->spending_constraints = $spending_constraints;

        return $this;
    }

    public function getActualConstraints(): ?string
    {
        return $this->actual_constraints;
    }

    public function setActualConstraints(?string $actual_constraints): static
    {
        $this->actual_constraints = $actual_constraints;

        return $this;
    }
}
