<?php

namespace App\Entity;

use App\Repository\IndependentAgeActionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IndependentAgeActionRepository::class)]
#[ORM\Table(name: 'independent_age_action')]
class IndependentAgeAction
{
    public function __construct()
    {
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->date = new \DateTimeImmutable('today');
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(targetEntity: IndependentAgeRecord::class, inversedBy: 'actions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?IndependentAgeRecord $record = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $action = null;

    // keep as string because Doctrine maps DECIMAL to string to avoid float issues
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $outcome = null;

    public function getId(): ?int { return $this->id; }

    public function getUuid(): ?string { return $this->uuid; }
    public function setUuid(string $uuid): self { $this->uuid = $uuid; return $this; }

    public function getRecord(): ?IndependentAgeRecord { return $this->record; }
    public function setRecord(?IndependentAgeRecord $record): self { $this->record = $record; return $this; }

    public function getDate(): ?\DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): self { $this->date = $date; return $this; }

    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }

    public function getOutcome(): ?string { return $this->outcome; }
    public function setOutcome(?string $outcome): self { $this->outcome = $outcome; return $this; }
}
