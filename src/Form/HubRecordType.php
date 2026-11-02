<?php

namespace App\Form;

use App\Entity\HubRecord;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as T;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HubRecordType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('date', T\DateType::class, [
                'label' => 'Registration date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('fullName', T\TextType::class, [
                'label' => 'Full name',
            ])
            ->add('age', T\IntegerType::class, [
                'required' => false,
                'attr' => ['min' => 0, 'max' => 130],
            ]);

        $this->single($builder, 'gender', 'Gender', [
            'Male' => 'male',
            'Female' => 'female',
            'Non Binary' => 'non_binary',
        ]);

        $this->text($builder, 'streetAddress', 'Street address');
        $this->text($builder, 'town', 'Town, e.g. Longsight');
        $this->text($builder, 'postcode', 'Postcode');

        $builder
            ->add('phone', T\TelType::class, [
                'required' => false,
            ])
            ->add('email', T\EmailType::class, [
                'required' => false,
            ]);

        $this->multiple(
            $builder,
            'heardAbout',
            'How did you hear about this NCA service?',
            [
                'Poster/Leaflet' => 'poster_leaflet',
                'Friend/Family' => 'friend_family',
                'Social media' => 'social_media',
                'Job Centre' => 'job_centre',
                'Internet' => 'internet',
                'Library' => 'library',
                'Text or Email' => 'text_email',
                'Other' => 'other',
            ]
        );

        $this->text($builder, 'heardAboutOther', 'Other referral source');

        $this->multiple(
            $builder,
            'supportNeeds',
            'What would you like support with?',
            [
                'Internet access' => 'internet_access',
                'Volunteering' => 'volunteering',
                'Register/Help for Welfare Benefits' => 'welfare_benefits',
                'Register/Help for Universal Credit' => 'universal_credit',
                'Register/Help Manchester Move' => 'manchester_move',
                'Learn English/ESOL' => 'english_esol',
                'Housing Advice/issues' => 'housing_advice',
                'Courses and Learning' => 'courses_learning',
                'Food Bank referral' => 'food_bank_referral',
                'Benefit issues' => 'benefit_issues',
                'Debt or Finances' => 'debt_finances',
            ]
        );

        $this->textarea(
            $builder,
            'supportOther',
            'What else could we help you with?'
        );

        $this->textarea(
            $builder,
            'additionalNeeds',
            'Additional needs during your visit, e.g. because of a disability'
        );

        $this->yesNo(
            $builder,
            'lookingForWork',
            'Are you looking for work/paid employment?'
        );

        $this->multiple(
            $builder,
            'employment',
            'Employment: tick all that apply',
            [
                'Unemployed: 0-6 months' => 'unemployed_0_6_months',
                'Unemployed: 6-12 months' => 'unemployed_6_12_months',
                'Unemployed: 1-2 years' => 'unemployed_1_2_years',
                'Unemployed: 2+ years' => 'unemployed_2_plus_years',
                'Employed: Part Time' => 'employed_part_time',
                'Employed: Full Time (35+ hrs)' => 'employed_full_time',
                'Self Employed: Sole Trader' => 'sole_trader',
                'Self Employed: Micro business (>10 staff)' => 'micro_business',
                'Self Employed: Small business (>50 staff)' => 'small_business',
                'Student: Part Time' => 'student_part_time',
                'Student: Full Time' => 'student_full_time',
                'Retired' => 'retired',
            ]
        );

        $this->multiple(
            $builder,
            'benefits',
            'Do you receive any welfare benefits?',
            [
                'Universal Credit' => 'universal_credit',
                'Child Tax Credits' => 'child_tax_credits',
                'Council Tax Support' => 'council_tax_support',
                'Working Tax Credits' => 'working_tax_credits',
                "Carer's Allowance" => 'carers_allowance',
                'Housing Benefit' => 'housing_benefit',
                'Pension Credit' => 'pension_credit',
                'Child Benefit' => 'child_benefit',
                'PIP / DLA' => 'pip_dla',
                'ESA' => 'esa',
                'Other' => 'other',
                'I DO NOT receive any' => 'none',
            ]
        );

        $this->text($builder, 'benefitsOther', 'Other benefits');

        $this->multiple(
            $builder,
            'immigrationStatuses',
            'Immigration status',
            [
                'British National' => 'british_national',
                'EU National' => 'eu_national',
                'Married to British or EU National' => 'married_british_eu',
                'Indefinite Leave to Remain' => 'ilr',
                'Asylum seeker' => 'asylum_seeker',
                'Refugee' => 'refugee',
            ]
        );

        $this->textarea(
            $builder,
            'statusDescribe',
            'Or describe your immigration status'
        );

        $this->yesNo(
            $builder,
            'englishFirstLanguage',
            'Is English your first language?'
        );

        $this->text(
            $builder,
            'firstLanguage',
            'If No, what is your first language?'
        );

        $this->yesNo(
            $builder,
            'caringResponsibilities',
            'Do you look after an adult or child with additional needs or a disability?'
        );

        $this->multiple($builder, 'housing', 'Housing', [
            'Private Renting' => 'private_renting',
            'Great Places' => 'great_places',
            'One Manchester' => 'one_manchester',
            'Other Landlord' => 'other_landlord',
            'Home Owner' => 'home_owner',
            'Live with parents' => 'live_with_parents',
            'Other' => 'other',
        ]);

        $this->text($builder, 'otherLandlord', 'Other landlord');
        $this->text($builder, 'housingOther', 'Other housing details');

        $this->yesNo(
            $builder,
            'greatPlacesConsent',
            'Great Places tenants only: consent to share name, age, gender, address and contact details with Great Places for monitoring this fund'
        );

        $this->signature(
            $builder,
            'greatPlacesSignature',
            'Great Places signature/reference'
        );

        $this->multiple(
            $builder,
            'permissions',
            'Permission to contact about Northmoor Hub services, activities or events',
            [
                'Email' => 'email',
                'Text Message' => 'text_message',
                'Phone Call' => 'phone_call',
            ]
        );

        $this->yesNo(
            $builder,
            'doNotContact',
            'Do not contact about the Next Step project or other services, activities or events'
        );

        $this->yesNo(
            $builder,
            'sharingConsent',
            'Consent to share information securely with Good Things Foundation for the Department for Education FDI programme and statistical analysis'
        );

        $this->signature(
            $builder,
            'sharingSignature',
            'Sharing consent signature/reference'
        );

        $this->yesNo(
            $builder,
            'imageConsent',
            'Consent to photos/video for promotion, website, social media, internal reports and reports to funders'
        );

        $this->signature(
            $builder,
            'imageSignature',
            'Image consent signature/reference'
        );

        $this->yesNo(
            $builder,
            'eSafetyAgreement',
            'E-Safety agreement recorded: no illegal, dangerous or offensive material, unauthorised software or bypassing internet safety'
        );

        $this->signature(
            $builder,
            'eSafetySignature',
            'E-Safety signature/reference'
        );

        $this->multiple(
            $builder,
            'headerChecks',
            'Staff administration: header boxes on paper form',
            [
                'Data on' => 'data_on',
                'Mailch.' => 'mailch',
                'Text' => 'text',
                'Call' => 'call',
                'Consent' => 'consent',
                'Image' => 'image',
            ]
        );
    }

    private function text(
        FormBuilderInterface $builder,
        string $name,
        string $label
    ): void {
        $builder->add($name, T\TextType::class, [
            'label' => $label,
            'required' => false,
        ]);
    }

    private function textarea(
        FormBuilderInterface $builder,
        string $name,
        string $label
    ): void {
        $builder->add($name, T\TextareaType::class, [
            'label' => $label,
            'required' => false,
            'attr' => ['rows' => 3],
        ]);
    }

    private function single(
        FormBuilderInterface $builder,
        string $name,
        string $label,
        array $choices
    ): void {
        $builder->add($name, T\ChoiceType::class, [
            'label' => $label,
            'choices' => $choices,
            'placeholder' => 'Not recorded',
            'required' => false,
        ]);
    }

    private function multiple(
        FormBuilderInterface $builder,
        string $name,
        string $label,
        array $choices
    ): void {
        $builder->add($name, T\ChoiceType::class, [
            'label' => $label,
            'choices' => $choices,
            'expanded' => true,
            'multiple' => true,
            'required' => false,
        ]);
    }

    private function yesNo(
        FormBuilderInterface $builder,
        string $name,
        string $label
    ): void {
        $this->single($builder, $name, $label, [
            'Yes' => true,
            'No' => false,
        ]);
    }

    private function signature(
        FormBuilderInterface $builder,
        string $name,
        string $label
    ): void {
        $builder->add($name, T\TextType::class, [
            'label' => $label,
            'required' => false,
            'help' => 'Enter the signed paper-form reference or a transcription. This field does not capture a handwritten signature.',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HubRecord::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'hub_record',
        ]);
    }
}