<?php

namespace App\Form;

use App\Entity\SignAbstract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignalementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeSign', ChoiceType::class, [
                'label' => 'Type de signalement',
                'choices' => [
                    'Incident' => 'Incident',
                    'Dégradation' => 'Degradation',
                    'Danger' => 'Danger',
                    'Autre' => 'Other'
                ],
                'attr' => ['class' => 'form-select mb-3']
            ])
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone concernée',
                'choices' => [
                    'Centre-ville' => 'Downtown',
                    'Nord' => 'North',
                    'Sud' => 'South',
                    'Est' => 'East',
                    'Ouest' => 'West'
                ],
                'attr' => ['class' => 'form-select mb-3']
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse précise',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Numéro, rue, code postal...'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-control mb-3',
                    'placeholder' => 'Décrivez le signalement en détail...'
                ]
            ]);

        if ($options['is_admin']) {
            $builder
                ->add('etatSign', ChoiceType::class, [
                    'label' => 'État',
                    'choices' => [
                        'En attente' => 'Pending',
                        'En cours' => 'In Progress',
                        'Résolu' => 'Resolved',
                        'Rejeté' => 'Rejected',
                    ],
                    'attr' => ['class' => 'form-select mb-3']
                ])
                ->add('feedback', TextareaType::class, [
                    'label' => 'Commentaire',
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                        'class' => 'form-control mb-3'
                    ]
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SignAbstract::class,
            'is_admin' => false
        ]);
    }
}