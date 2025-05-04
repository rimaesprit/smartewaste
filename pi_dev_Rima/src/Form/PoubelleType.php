<?php

namespace App\Form;

use App\Entity\Poubelle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Choice;

class PoubelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La localisation ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'La localisation doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'La localisation ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la localisation',
                    'required' => true,
                    'minlength' => 3,
                    'maxlength' => 255
                ]
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'adresse ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez l\'adresse complète',
                    'required' => true,
                    'minlength' => 5,
                    'maxlength' => 255
                ]
            ])
            ->add('niveauRemplissage', IntegerType::class, [
                'label' => 'Niveau de remplissage (%)',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le niveau de remplissage ne peut pas être vide'
                    ]),
                    new Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Le niveau de remplissage doit être entre {{ min }}% et {{ max }}%'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100,
                    'required' => true,
                    'step' => 1
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de poubelle',
                'choices' => [
                    'Plastique' => 'Plastique',
                    'Verre' => 'Verre',
                    'Papier' => 'Papier',
                    'Métal' => 'Métal',
                    'Organique' => 'Organique',
                    'Mixte' => 'Mixte'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type de poubelle doit être sélectionné'
                    ]),
                    new Choice([
                        'choices' => ['Plastique', 'Verre', 'Papier', 'Métal', 'Organique', 'Mixte'],
                        'message' => 'Veuillez sélectionner un type de poubelle valide'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select',
                    'required' => true
                ]
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Fonctionnelle' => 'Fonctionnelle',
                    'En maintenance' => 'En maintenance',
                    'Hors service' => 'Hors service'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le statut doit être sélectionné'
                    ]),
                    new Choice([
                        'choices' => ['Fonctionnelle', 'En maintenance', 'Hors service'],
                        'message' => 'Veuillez sélectionner un statut valide'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select',
                    'required' => true
                ]
            ])
            ->add('latitude', NumberType::class, [
                'label' => 'Latitude',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => -90,
                        'max' => 90,
                        'notInRangeMessage' => 'La latitude doit être comprise entre {{ min }}° et {{ max }}°'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'display: none;',
                    'min' => -90,
                    'max' => 90,
                    'step' => 'any'
                ]
            ])
            ->add('longitude', NumberType::class, [
                'label' => 'Longitude',
                'required' => false,
                'constraints' => [
                    new Range([
                        'min' => -180,
                        'max' => 180,
                        'notInRangeMessage' => 'La longitude doit être comprise entre {{ min }}° et {{ max }}°'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'display: none;',
                    'min' => -180,
                    'max' => 180,
                    'step' => 'any'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poubelle::class,
        ]);
    }
} 