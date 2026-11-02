<?php

namespace App\Form;

use App\Entity\HubAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as T;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HubActionType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('date', T\DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'label' => 'Date',
            ])
            ->add('actionLocation', T\ChoiceType::class, [
                'label' => 'Where was the action performed?',
                'choices' => HubAction::ACTION_LOCATION_CHOICES,
                'placeholder' => 'Select...',
                'required' => false,
            ])
            ->add('actionLocationOther', T\TextType::class, [
                'label' => 'If Other, describe the location',
                'required' => false,
            ])
            ->add('action', T\TextareaType::class, [
                'label' => 'Actions taken, referrals and support',
                'attr' => ['rows' => 5],
            ])
            ->add('achievements', T\TextareaType::class, [
                'label' => 'Achievements',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('outcome', T\NumberType::class, [
                'label' => 'Numeric outcome',
                'help' => 'Optional numeric outcome, matching the existing Independent Age module.',
                'required' => false,
                'input' => 'string',
                'scale' => 2,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HubAction::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'hub_action',
        ]);
    }
}