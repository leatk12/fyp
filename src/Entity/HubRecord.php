<?php

namespace App\Entity;

use App\Repository\HubRecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: HubRecordRepository::class)]
#[ORM\Table(name: 'hub_record')]
class HubRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private string $uuid;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    public ?\DateTimeImmutable $date = null;

    #[ORM\Column(name: 'full_name', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public ?string $fullName = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 130)]
    public ?int $age = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    public ?string $gender = null;

    #[ORM\Column(name: 'street_address', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $streetAddress = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120)]
    public ?string $town = null;

    #[ORM\Column(length: 12, nullable: true)]
    #[Assert\Length(max: 12)]
    public ?string $postcode = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Assert\Length(max: 30)]
    public ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public ?string $email = null;

    #[ORM\Column(name: 'heard_about', type: Types::JSON)]
    public array $heardAbout = [];

    #[ORM\Column(name: 'heard_about_other', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $heardAboutOther = null;

    #[ORM\Column(name: 'support_needs', type: Types::JSON)]
    public array $supportNeeds = [];

    #[ORM\Column(name: 'support_other', type: Types::TEXT, nullable: true)]
    public ?string $supportOther = null;

    #[ORM\Column(name: 'additional_needs', type: Types::TEXT, nullable: true)]
    public ?string $additionalNeeds = null;

    #[ORM\Column(name: 'looking_for_work', nullable: true)]
    public ?bool $lookingForWork = null;

    #[ORM\Column(type: Types::JSON)]
    public array $employment = [];

    #[ORM\Column(type: Types::JSON)]
    public array $benefits = [];

    #[ORM\Column(name: 'benefits_other', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $benefitsOther = null;

    #[ORM\Column(name: 'immigration_statuses', type: Types::JSON)]
    public array $immigrationStatuses = [];

    #[ORM\Column(name: 'status_describe', type: Types::TEXT, nullable: true)]
    public ?string $statusDescribe = null;

    #[ORM\Column(name: 'english_first_language', nullable: true)]
    public ?bool $englishFirstLanguage = null;

    #[ORM\Column(name: 'first_language', length: 120, nullable: true)]
    #[Assert\Length(max: 120)]
    public ?string $firstLanguage = null;

    #[ORM\Column(name: 'caring_responsibilities', nullable: true)]
    public ?bool $caringResponsibilities = null;

    #[ORM\Column(type: Types::JSON)]
    public array $housing = [];

    #[ORM\Column(name: 'other_landlord', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $otherLandlord = null;

    #[ORM\Column(name: 'housing_other', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $housingOther = null;

    #[ORM\Column(name: 'great_places_consent', nullable: true)]
    public ?bool $greatPlacesConsent = null;

    #[ORM\Column(name: 'great_places_signature', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $greatPlacesSignature = null;

    #[ORM\Column(type: Types::JSON)]
    public array $permissions = [];

    #[ORM\Column(name: 'do_not_contact', nullable: true)]
    public ?bool $doNotContact = null;

    #[ORM\Column(name: 'sharing_consent', nullable: true)]
    public ?bool $sharingConsent = null;

    #[ORM\Column(name: 'sharing_signature', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $sharingSignature = null;

    #[ORM\Column(name: 'image_consent', nullable: true)]
    public ?bool $imageConsent = null;

    #[ORM\Column(name: 'image_signature', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $imageSignature = null;

    #[ORM\Column(name: 'e_safety_agreement', nullable: true)]
    public ?bool $eSafetyAgreement = null;

    #[ORM\Column(name: 'e_safety_signature', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    public ?string $eSafetySignature = null;

    /*
     * Administrative ticks at the top of the paper form.
     * These are separate from the person's consent answers.
     */
    #[ORM\Column(name: 'header_checks', type: Types::JSON)]
    public array $headerChecks = [];

    /** @var Collection<int, HubAction> */
    #[ORM\OneToMany(
        mappedBy: 'record',
        targetEntity: HubAction::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['date' => 'DESC', 'id' => 'DESC'])]
    private Collection $actions;

    public function __construct()
    {
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->date = new \DateTimeImmutable('today');
        $this->actions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /** @return Collection<int, HubAction> */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(HubAction $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->setRecord($this);
        }

        return $this;
    }

    public function removeAction(HubAction $action): self
    {
        if (
            $this->actions->removeElement($action)
            && $action->getRecord() === $this
        ) {
            $action->setRecord(null);
        }

        return $this;
    }

    #[Assert\Callback]
    public function validateRegistration(
        ExecutionContextInterface $context
    ): void {
        if (
            in_array('none', $this->benefits, true)
            && count($this->benefits) > 1
        ) {
            $context->buildViolation(
                'Select either benefits received or "I do not receive any".'
            )->atPath('benefits')->addViolation();
        }

        if ($this->doNotContact === true && $this->permissions !== []) {
            $context->buildViolation(
                'Remove contact permissions when "Do not contact" is Yes.'
            )->atPath('permissions')->addViolation();
        }

        $requiredDetails = [
            [
                in_array('other', $this->heardAbout, true),
                'heardAboutOther',
            ],
            [
                in_array('other', $this->benefits, true),
                'benefitsOther',
            ],
            [
                in_array('other_landlord', $this->housing, true),
                'otherLandlord',
            ],
            [
                in_array('other', $this->housing, true),
                'housingOther',
            ],
            [
                $this->englishFirstLanguage === false,
                'firstLanguage',
            ],
        ];

        foreach ($requiredDetails as [$required, $field]) {
            if ($required && trim((string) $this->{$field}) === '') {
                $context->buildViolation('Please provide the details.')
                    ->atPath($field)
                    ->addViolation();
            }
        }

        $signatureFields = [
            'greatPlacesConsent' => 'greatPlacesSignature',
            'sharingConsent' => 'sharingSignature',
            'imageConsent' => 'imageSignature',
            'eSafetyAgreement' => 'eSafetySignature',
        ];

        foreach ($signatureFields as $consentField => $signatureField) {
            if (
                $this->{$consentField} === true
                && trim((string) $this->{$signatureField}) === ''
            ) {
                $context->buildViolation(
                    'Record the signed-form reference or signature transcription.'
                )->atPath($signatureField)->addViolation();
            }
        }
    }
}