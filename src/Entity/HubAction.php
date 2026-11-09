<?php

namespace App\Entity;

use App\Repository\HubActionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: HubActionRepository::class)]
#[ORM\Table(name: 'hub_action')]
class HubAction
{
    public const ACTION_LOCATION_CHOICES = [
        'Home visit' => 'home_visit',
        'Telephone/Online' => 'telephone_online',
        'Community or outreach setting' => 'community_outreach',
        'Hub' => 'hub',
        'Other' => 'other',
        'Not recorded' => 'not_recorded',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private string $uuid;

    #[ORM\ManyToOne(targetEntity: HubRecord::class, inversedBy: 'actions')]
    #[ORM\JoinColumn(
        name: 'record_id',
        referencedColumnName: 'id',
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[Assert\NotNull]
    private ?HubRecord $record = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    public ?\DateTimeImmutable $date = null;
    #[ORM\Column(name: 'performed_by', length: 255, nullable: true)]
    #[Assert\Length(max:255)]
    public ?string $performedBy = null;

    #[ORM\Column(name: 'action_location', length: 60, nullable: true)]
    #[Assert\Length(max: 60)]
    public ?string $actionLocation = null;

    #[ORM\Column(name: 'action_location_other', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $actionLocationOther = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    public ?string $action = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $achievements = null;

    #[ORM\Column(
        type: Types::DECIMAL,
        precision: 10,
        scale: 2,
        nullable: true
    )]
    #[Assert\Range(min: -99999999.99, max: 99999999.99)]
    public ?string $outcome = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->date = new \DateTimeImmutable('today');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getRecord(): ?HubRecord
    {
        return $this->record;
    }

    public function setRecord(?HubRecord $record): self
    {
        $this->record = $record;

        return $this;
    }

    #[Assert\Callback]
    public function validateLocation(
        ExecutionContextInterface $context
    ): void {
        if (
            $this->actionLocation === 'other'
            && trim((string) $this->actionLocationOther) === ''
        ) {
            $context->buildViolation('Please describe the location.')
                ->atPath('actionLocationOther')
                ->addViolation();
        }
    }
}