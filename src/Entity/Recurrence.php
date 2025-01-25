<?php

namespace App\Entity;

use App\Repository\RecurrenceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecurrenceRepository::class)]
class Recurrence
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $recurring = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecurring(): ?string
    {
        return $this->recurring;
    }

    public function setRecurring(string $recurring): static
    {
        $this->recurring = $recurring;

        return $this;
    }
}
