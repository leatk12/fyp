<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{
    DateType, TextType, MoneyType, ChoiceType, SubmitType
};
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // 1) The top-level choice: are we dealing with an EXPENSE or REVENUE?
            ->add('isExpenseOrRevenue', ChoiceType::class, [
                'choices' => [
                    'Expense' => 'EXPENSE',
                    'Revenue' => 'REVENUE',
                ],
                'label' => 'Is this Expense or Revenue?',
                'required' => true,
            ])
            // 2) The “category” choice with a big list
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Rent' => 'Rent',
                    'Wages' => 'Wages',
                    'Utilities' => 'Utilities',
                    'Project expense - Diner' => 'Project expense - Diner',
                    'Project expense - Hmwk club' => 'Project expense - Hmwk club',
                    'Project expense - Next step' => 'Project expense - Next step',
                    'Room booking' => 'Room booking',
                    'Incoming funding' => 'Incoming funding',
                    'Stationary' => 'Stationary',
                    'IT Maintenance' => 'IT Maintenance',
                    'Miscellaneous' => 'Miscellaneous',
                ],
                'label' => 'Category',
                'required' => true,
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'required' => false,
                'label' => 'Description',
            ])
            ->add('amount', MoneyType::class, [
                'currency' => 'GBP',
                'required' => false,
                'label' => 'Transaction Amount (auto-filled from lines)',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save Transaction'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
}
