<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerEmail', EmailType::class, [
                'label' => 'Adres e-mail',
                'attr' => ['placeholder' => 'np. jan.kowalski@example.com'],
                'constraints' => [
                    new NotBlank(['message' => 'Proszę podać adres e-mail.']),
                    new Email(['message' => 'Proszę podać poprawny adres e-mail.']),
                ],
            ])
            ->add('customerFirstName', TextType::class, [
                'label' => 'Imię',
                'required' => true,
                'attr' => ['placeholder' => 'np. Jan'],
                'constraints' => [
                    new NotBlank(['message' => 'Proszę podać imię.']),
                ],
            ])
            ->add('customerLastName', TextType::class, [
                'label' => 'Nazwisko',
                'required' => true,
                'attr' => ['placeholder' => 'np. Kowalski'],
                'constraints' => [
                    new NotBlank(['message' => 'Proszę podać nazwisko.']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}