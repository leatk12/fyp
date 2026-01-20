<?php

namespace App\Form;

use App\Entity\IndependentAgeRecord;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependentAgeRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Section 1
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label'  => 'Date',
            ])
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Age',
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => IndependentAgeRecord::GENDER_CHOICES,
                'required' => false,
                'placeholder' => 'Select…',
            ])

            // Section 2
            ->add('heardAbout', ChoiceType::class, [
                'label' => 'How did you hear about us?',
                'choices' => IndependentAgeRecord::HEAR_ABOUT_CHOICES,
                'placeholder' => 'Select…',
                'required' => false,
            ])

            // Section 3
            ->add('streetAddress', TextType::class, [
                'label' => 'Street Address',
                'required' => false,
            ])
            ->add('ward', TextType::class, [
                'label' => 'Ward',
                'required' => false,
            ])
            ->add('postcode', TextType::class, [
                'label' => 'Postcode',
                'required' => false,
            ])
            ->add('phone', TelType::class, [
                'label' => 'Phone',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
            ])

            // Section 4
            ->add('additionalNeeds', TextareaType::class, [
                'label' => 'Additional needs',
                'required' => false,
                'attr' => ['rows' => 4],
            ])

            // Section 5
            ->add('benefits', ChoiceType::class, [
                'label' => 'Do you receive any of the following?',
                'choices' => IndependentAgeRecord::BENEFIT_CHOICES,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])

            // Section 6
            ->add('immigrationStatus', ChoiceType::class, [
                'label' => 'Immigration status',
                'choices' => IndependentAgeRecord::IMMIGRATION_CHOICES,
                'placeholder' => 'Select…',
                'required' => false,
            ])
            ->add('statusDescribe', TextareaType::class, [
                'label' => 'Describe your status',
                'required' => false,
                'attr' => ['rows' => 3],
            ])

            // Section 6b - Language
            ->add('isEnglishFirstLanguage', ChoiceType::class, [
                'label' => 'Is English your first language?',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('firstLanguage', TextType::class, [
                'label' => 'No, my first language is',
                'required' => false,
            ])

            // Section 7 - Housing
            ->add('housing', ChoiceType::class, [
                'label' => 'Housing',
                'choices' => IndependentAgeRecord::HOUSING_CHOICES,
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('housingOther', TextType::class, [
                'label' => 'If Other, please specify',
                'required' => false,
            ])

            // Section 8 - Permissions
            ->add('permissions', ChoiceType::class, [
                'label' => 'Permissions to contact',
                'choices' => IndependentAgeRecord::PERMISSION_CHOICES,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IndependentAgeRecord::class,
        ]);
    }
}
