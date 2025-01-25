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
    private ?string $roomName = null; // Changed from "room_name" to "roomName"

    #[ORM\Column]
    private ?int $costIndividual = null;

    #[ORM\Column]
    private ?int $costPrivate = null;

    #[ORM\Column]
    private ?int $costCommunity = null;

    #[ORM\Column]
    private ?int $maxOccupancy = null;

    #[ORM\Column(length: 255)]
    private ?string $organisation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomName(): ?string
    {
        return $this->roomName;
    }

    public function setRoomName(string $roomName): static
    {
        $this->roomName = $roomName;

        return $this;
    }

    public function getCostIndividual(): ?int
    {
        return $this->costIndividual;
    }

    public function setCostIndividual(int $costIndividual): static
    {
        $this->costIndividual = $costIndividual;

        return $this;
    }

    public function getCostPrivate(): ?int
    {
        return $this->costPrivate;
    }

    public function setCostPrivate(int $costPrivate): static
    {
        $this->costPrivate = $costPrivate;

        return $this;
    }

    public function getCostCommunity(): ?int
    {
        return $this->costCommunity;
    }

    public function setCostCommunity(int $costCommunity): static
    {
        $this->costCommunity = $costCommunity;

        return $this;
    }

    public function getMaxOccupancy(): ?int
    {
        return $this->maxOccupancy;
    }

    public function setMaxOccupancy(int $maxOccupancy): static
    {
        $this->maxOccupancy = $maxOccupancy;

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
