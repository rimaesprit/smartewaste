<?php

namespace App\Form;

use App\Entity\Conteneur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConteneurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type_dechet', ChoiceType::class, [
                'label' => 'Type de déchet',
                'choices' => [
                    'Plastique' => 'Plastique',
                    'Verre' => 'Verre',
                    'Papier' => 'Papier',
                    'Métal' => 'Métal',
                    'Organique' => 'Organique',
                    'Électronique' => 'Électronique',
                    'Autre' => 'Autre'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('capacite', NumberType::class, [
                'label' => 'Capacité (kg)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 0.1
                ]
            ])
            ->add('poids_actuel', NumberType::class, [
                'label' => 'Poids actuel (kg)',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 0.1
                ]
            ])
            ->add('emplacement', TextType::class, [
                'label' => 'Emplacement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone',
                'choices' => [
                    'Nord' => 'Nord',
                    'Sud' => 'Sud',
                    'Est' => 'Est',
                    'Ouest' => 'Ouest',
                    'Centre' => 'Centre'
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Conteneur::class,
        ]);
    }
}
