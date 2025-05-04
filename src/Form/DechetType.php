<?php

namespace App\Form;

use App\Entity\Camion;
use App\Entity\Dechet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DechetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_dechet', ChoiceType::class, [
                'label' => 'Type de déchet',
                'choices' => [
                    'Plastique' => 'plastique',
                    'Papier' => 'papier',
                    'Verre' => 'verre',
                    'Métal' => 'metal',
                    'Organique' => 'organique',
                    'Électronique' => 'electronique',
                    'Dangereux' => 'dangereux',
                    'Autre' => 'autre'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids (kg)',
                'attr' => [
                    'placeholder' => 'Entrez le poids en kg',
                    'class' => 'form-control'
                ]
            ])
            ->add('date_depot', DateType::class, [
                'label' => 'Date de dépôt',
                'widget' => 'single_text',
                'data' => new \DateTime(),
                'attr' => [
                    'class' => 'form-control',
                    'max' => (new \DateTime())->format('Y-m-d')
                ]
            ])
            ->add('camion', EntityType::class, [
                'class' => Camion::class,
                'choice_label' => 'matricule',
                'placeholder' => 'Sélectionner un camion',
                'label' => 'Camion',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dechet::class,
        ]);
    }
} 