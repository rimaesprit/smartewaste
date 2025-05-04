<?php

namespace App\Form;

use App\Entity\Camion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class CamionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('matricule', TextType::class, [
                'attr' => [
                    'pattern' => '^[A-Z]{2,3}-[0-9]{3,4}$',
                    'maxlength' => 8,
                    'placeholder' => 'Ex: TN-123 ou TNS-1234'
                ]
            ])
            ->add('capacite', NumberType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 50,
                    'step' => 0.1
                ]
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    'En service' => 'en_service',
                    'En maintenance' => 'en_maintenance',
                    'Hors service' => 'hors_service'
                ]
            ])
            ->add('type_moteur', ChoiceType::class, [
                'choices' => [
                    'Diesel' => 'diesel',
                    'Électrique' => 'electrique',
                    'Hybride' => 'hybride',
                    'Gaz' => 'gaz',
                    'Biodiesel' => 'biodiesel'
                ]
            ])
            ->add('emission_co2', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 1000,
                    'step' => 0.1
                ]
            ])
            ->add('consommation', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 50,
                    'step' => 0.1
                ]
            ])
            ->add('annee_fabrication', NumberType::class, [
                'attr' => [
                    'min' => 2000,
                    'max' => 2024
                ]
            ])
            ->add('kilometrage', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'step' => 0.1
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Camion::class,
        ]);
    }
} 