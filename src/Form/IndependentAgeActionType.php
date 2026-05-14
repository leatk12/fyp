<?php

namespace App\Form;

use App\Entity\IndependentAgeAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndependentAgeActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date:',
            ])
            ->add('actionLocation', ChoiceType::class, [
                'label' => 'Where was the action performed?',
                'choices' => IndependentAgeAction::ACTION_LOCATION_CHOICES,
                'placeholder' => 'Select…',
                'required' => false,
            ])
            ->add('actionLocationOther', TextType::class, [
                'label' => 'If other please describe',
                'required' => false,
            ])
            ->add('action', TextareaType::class, [
                'label' => 'Action:',
                'attr' => ['rows' => 3],
            ])
            ->add('outcome', NumberType::class, [
                'label' => 'Outcome:',
                'required' => false,
                'scale' => 2,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IndependentAgeAction::class,
        ]);
    }
}