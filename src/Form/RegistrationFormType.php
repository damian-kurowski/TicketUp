<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adres e-mail'
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Imię (opcjonalnie)',
                'required' => false, // Pole nie jest wymagane
                'attr' => ['placeholder' => 'Podaj swoje imię']
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nazwisko (opcjonalnie)',
                'required' => false, // Pole nie jest wymagane
                'attr' => ['placeholder' => 'Podaj swoje nazwisko']
            ])

            ->add('plainPassword', PasswordType::class, [

                'label' => 'Hasło',
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać hasło',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Twoje hasło powinno mieć przynajmniej {{ limit }} znaków',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}