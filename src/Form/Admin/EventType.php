<?php

namespace App\Form\Admin;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa wydarzenia'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'attr' => ['rows' => 5]
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Data rozpoczęcia',
                'widget' => 'single_text',

            ])
            ->add('endDate', DateTimeType::class, [
                'label' => 'Data zakończenia (opcjonalnie)',
                'widget' => 'single_text',
                'required' => false
            ])
            ->add('locationName', TextType::class, [
                'label' => 'Nazwa miejsca'
            ])
            ->add('locationAddress', TextType::class, [
                'label' => 'Adres miejsca (opcjonalnie)',
                'required' => false
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Obrazek wydarzenia (JPG, PNG, GIF)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'Plik obrazka jest zbyt duży ({{ size }} {{ suffix }}). Maksymalny dozwolony rozmiar to {{ limit }} {{ suffix }}.',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Proszę przesłać prawidłowy plik obrazka (JPG, PNG lub GIF).',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/jpeg, image/png, image/gif'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}