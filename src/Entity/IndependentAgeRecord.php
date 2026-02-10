<?php

namespace App\Entity;

use App\Repository\IndependentAgeRecordRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IndependentAgeRecordRepository::class)]
#[ORM\Table(name: 'independent_age_record')]
class IndependentAgeRecord
{
    public const HEAR_ABOUT_CHOICES = [
        'Poster/Leaflet' => 'poster_leaflet',
        'Social media' => 'social_media',
        'CAB' => 'cab',
        'Friend/Family' => 'friend_family',
        'GP' => 'gp',
        'Library' => 'library',
    ];

    public const GENDER_CHOICES = [
        'Male' => 'male',
        'Female' => 'female',
        'Non-binary' => 'non_binary',
        'Prefer not to say' => 'prefer_not',
        'Other' => 'other',
    ];

    public const BENEFIT_CHOICES = [
        'State pension' => 'state_pension',
        "Carer's Allowance" => 'carers_allowance',
        'PIP' => 'pip',
        'Pension Credit' => 'pension_credit',
        'Housing Benefit' => 'housing_benefit',
        'DLA' => 'dla',
        'Attendance Allowance' => 'attendance_allowance',
        'Council Tax Support' => 'council_tax_support',
    ];

    public const IMMIGRATION_CHOICES = [
        'British National' => 'british_national',
        'EU National' => 'eu_national',
        'Married to British or EU National' => 'married_to_british_or_eu',
        'Asylum Seeker' => 'asylum_seeker',
        'Refugee' => 'refugee',
        'Indefinate Leave to Remain' => 'ilr',
    ];

    public const HOUSING_CHOICES = [
        'Private renting' => 'private_renting',
        'Great places' => 'great_places',
        'One Manchester' => 'one_manchester',
        'Home owner' => 'home_owner',
        'Live with family' => 'live_with_family',
        'Temporary Accommodation' => 'temporary_accommodation',
        'Supported/Retirement Living' => 'supported_retirement',
        'Other' => 'other',
    ];

    public const PERMISSION_CHOICES = [
        'Email' => 'email',
        'Text message' => 'text_message',
        'Phone call' => 'phone_call',
    ];

    public function __construct()
    {
        // store UUID as string (CHAR(36) in MySQL)
        $this->uuid = Uuid::v4()->toRfc4122();
        $this->actions = new ArrayCollection();
        $this->benefits = [];
        $this->permissions = [];
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 36, unique: true)]
    private ?string $uuid = null;

    // Section 1
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $fullName = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?int $age = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $gender = null;

    // Section 2
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $heardAbout = null;

    // Section 3
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $streetAddress = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $ward = null;

    #[ORM\Column(length: 12, nullable: true)]
    private ?string $postcode = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Email(allowNull: true)]
    private ?string $email = null;

    // Section 4
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $additionalNeeds = null;

    // Section 5
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $benefits = [];

    // Section 6
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $immigrationStatus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $statusDescribe = null;

    // Section 6b
    #[ORM\Column(nullable: true)]
    private ?bool $isEnglishFirstLanguage = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $firstLanguage = null;

    // Section 7
    #[ORM\Column(length: 40, nullable: true)]
    private ?string $housing = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $housingOther = null;

    // Section 8
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $permissions = [];

    /**
     * @var Collection<int, IndependentAgeAction>
     */
    #[ORM\OneToMany(mappedBy: 'record', targetEntity: IndependentAgeAction::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['date' => 'DESC', 'id' => 'DESC'])]
    private Collection $actions;

    public function getId(): ?int { return $this->id; }

    public function getUuid(): ?string { return $this->uuid; }
    public function setUuid(string $uuid): self { $this->uuid = $uuid; return $this; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $fullName): self { $this->fullName = $fullName; return $this; }

    public function getAge(): ?int { return $this->age; }
    public function setAge(int $age): self { $this->age = $age; return $this; }

    public function getGender(): ?string { return $this->gender; }
    public function setGender(?string $gender): self { $this->gender = $gender; return $this; }

    public function getHeardAbout(): ?string { return $this->heardAbout; }
    public function setHeardAbout(?string $heardAbout): self { $this->heardAbout = $heardAbout; return $this; }

    public function getStreetAddress(): ?string { return $this->streetAddress; }
    public function setStreetAddress(?string $streetAddress): self { $this->streetAddress = $streetAddress; return $this; }

    public function getWard(): ?string { return $this->ward; }
    public function setWard(?string $ward): self { $this->ward = $ward; return $this; }

    public function getPostcode(): ?string { return $this->postcode; }
    public function setPostcode(?string $postcode): self { $this->postcode = $postcode; return $this; }

    public function getPhone(): ?string { return $this->phone; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }

    public function getAdditionalNeeds(): ?string { return $this->additionalNeeds; }
    public function setAdditionalNeeds(?string $additionalNeeds): self { $this->additionalNeeds = $additionalNeeds; return $this; }

    public function getBenefits(): ?array { return $this->benefits; }
    public function setBenefits(?array $benefits): self { $this->benefits = $benefits ?? []; return $this; }

    public function getImmigrationStatus(): ?string { return $this->immigrationStatus; }
    public function setImmigrationStatus(?string $immigrationStatus): self { $this->immigrationStatus = $immigrationStatus; return $this; }

    public function getStatusDescribe(): ?string { return $this->statusDescribe; }
    public function setStatusDescribe(?string $statusDescribe): self { $this->statusDescribe = $statusDescribe; return $this; }

    public function isEnglishFirstLanguage(): ?bool { return $this->isEnglishFirstLanguage; }
    public function setIsEnglishFirstLanguage(?bool $isEnglishFirstLanguage): self { $this->isEnglishFirstLanguage = $isEnglishFirstLanguage; return $this; }

    public function getFirstLanguage(): ?string { return $this->firstLanguage; }
    public function setFirstLanguage(?string $firstLanguage): self { $this->firstLanguage = $firstLanguage; return $this; }

    public function getHousing(): ?string { return $this->housing; }
    public function setHousing(?string $housing): self { $this->housing = $housing; return $this; }

    public function getHousingOther(): ?string { return $this->housingOther; }
    public function setHousingOther(?string $housingOther): self { $this->housingOther = $housingOther; return $this; }

    public function getPermissions(): ?array { return $this->permissions; }
    public function setPermissions(?array $permissions): self { $this->permissions = $permissions ?? []; return $this; }

    /**
     * @return Collection<int, IndependentAgeAction>
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(IndependentAgeAction $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions->add($action);
            $action->setRecord($this);
        }
        return $this;
    }

    public function removeAction(IndependentAgeAction $action): self
    {
        if ($this->actions->removeElement($action)) {
            if ($action->getRecord() === $this) {
                $action->setRecord(null);
            }
        }
        return $this;
    }
}
