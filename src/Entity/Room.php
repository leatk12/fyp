<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $room_name = null;

    #[ORM\Column]
    private ?int $cost_individual = null;

    #[ORM\Column]
    private ?int $cost_private = null;

    #[ORM\Column]
    private ?int $cost_community = null;

    #[ORM\Column]
    private ?int $max_occupancy = null;

    #[ORM\Column(length: 255)]
    private ?string $organisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomName(): ?string
    {
        return $this->room_name;
    }

    public function setRoomName(string $room_name): static
    {
        $this->room_name = $room_name;

        return $this;
    }

    public function getCostIndividual(): ?int
    {
        return $this->cost_individual;
    }

    public function setCostIndividual(int $cost_individual): static
    {
        $this->cost_individual = $cost_individual;

        return $this;
    }

    public function getCostPrivate(): ?int
    {
        return $this->cost_private;
    }

    public function setCostPrivate(int $cost_private): static
    {
        $this->cost_private = $cost_private;

        return $this;
    }

    public function getCostCommunity(): ?int
    {
        return $this->cost_community;
    }

    public function setCostCommunity(int $cost_community): static
    {
        $this->cost_community = $cost_community;

        return $this;
    }

    public function getMaxOccupancy(): ?int
    {
        return $this->max_occupancy;
    }

    public function setMaxOccupancy(int $max_occupancy): static
    {
        $this->max_occupancy = $max_occupancy;

        return $this;
    }

    public function getOrganisation(): ?string
    {
        return $this->organisation;
    }

    public function setOrganisation(string $organisation): static
    {
        $this->organisation = $organisation;

        return $this;
    }
}
