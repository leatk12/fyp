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
    public const ACTION_LOCATION_CHOICES = [
        'Home visit' => 'home_visit',
        'Telephone/Online' => 'telephone_online',
        'In a community or outreach setting' => 'community_outreach',
        'Hub' => 'hub',
        'Other' => 'other',
        'Not recorded' => 'not_recorded',
    ];

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

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $actionLocation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $actionLocationOther = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $action = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $outcome = null;

    public function getId(): ?int { return $this->id; }

    public function getUuid(): ?string { return $this->uuid; }
    public function setUuid(string $uuid): self { $this->uuid = $uuid; return $this; }

    public function getRecord(): ?IndependentAgeRecord { return $this->record; }
    public function setRecord(?IndependentAgeRecord $record): self { $this->record = $record; return $this; }

    public function getDate(): ?\DateTimeImmutable { return $this->date; }
    public function setDate(\DateTimeImmutable $date): self { $this->date = $date; return $this; }

    public function getActionLocation(): ?string { return $this->actionLocation; }
    public function setActionLocation(?string $actionLocation): self { $this->actionLocation = $actionLocation; return $this; }

    public function getActionLocationOther(): ?string { return $this->actionLocationOther; }
    public function setActionLocationOther(?string $actionLocationOther): self { $this->actionLocationOther = $actionLocationOther; return $this; }

    public function getAction(): ?string { return $this->action; }
    public function setAction(string $action): self { $this->action = $action; return $this; }

    public function getOutcome(): ?string { return $this->outcome; }
    public function setOutcome(?string $outcome): self { $this->outcome = $outcome; return $this; }
}