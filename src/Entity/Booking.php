<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $time_start = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $time_end = null;

    #[ORM\Column(length: 255)]
    private ?string $room = null; // Changed to store room name

    #[ORM\Column(length: 255)]
    private ?string $booked_by = null;

    #[ORM\Column]
    private ?int $cust_id = null;

    #[ORM\ManyToOne(targetEntity: Recurrence::class)]
    #[ORM\JoinColumn(nullable: true)] // Allow this field to be null
    private ?Recurrence $recurring = null;

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

    public function getTimeStart(): ?\DateTimeInterface
    {
        return $this->time_start;
    }

    public function setTimeStart(\DateTimeInterface $time_start): static
    {
        $this->time_start = $time_start;

        return $this;
    }

    public function getTimeEnd(): ?\DateTimeInterface
    {
        return $this->time_end;
    }

    public function setTimeEnd(\DateTimeInterface $time_end): static
    {
        $this->time_end = $time_end;

        return $this;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(string $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getBookedBy(): ?string
    {
        return $this->booked_by;
    }

    public function setBookedBy(string $booked_by): static
    {
        $this->booked_by = $booked_by;

        return $this;
    }

    public function getCustId(): ?int
    {
        return $this->cust_id;
    }

    public function setCustId(int $cust_id): static
    {
        $this->cust_id = $cust_id;

        return $this;
    }

    public function getRecurring(): ?Recurrence
    {
        return $this->recurring;
    }

    public function setRecurring(?Recurrence $recurring): static
    {
        $this->recurring = $recurring;

        return $this;
    }
}
