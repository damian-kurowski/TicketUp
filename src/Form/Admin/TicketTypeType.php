<?php

namespace App\Form\Admin;

use App\Entity\TicketType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa typu biletu (np. Normalny, VIP)',
            ])
            ->add('price', NumberType::class, [
                'label' => 'Cena (zł)',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01'
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Całkowita ilość dostępnych biletów tego typu',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TicketType::class,
        ]);
    }
}