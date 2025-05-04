<?php

namespace App\Form;

use App\Entity\Bienetre;
use App\Entity\Poubelle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

class BienetreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom',
                ]
            ])
            ->add('review', TextareaType::class, [
                'label' => 'Votre avis',
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'avis ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 1000,
                        'minMessage' => 'L\'avis doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'avis ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Partagez votre expérience...',
                    'rows' => 4
                ]
            ])
                ->add('rate', IntegerType::class, [
                    'label' => 'Note (de 1 à 5)',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'La note ne peut pas être vide'
                        ]),
                        new Range([
                            'min' => 1,
                            'max' => 5,
                            'notInRangeMessage' => 'La note doit être entre {{ min }} et {{ max }}'
                        ])
                    ],
                    'attr' => [
                        'class' => 'form-control',
                        'min' => 1,
                        'max' => 5,
                        'placeholder' => 'Donnez une note de 1 à 5'
                    ]
                ])
            ->add('sentiment', ChoiceType::class, [
                'label' => 'Sentiment',
                'choices' => [
                    'Positif' => 'Positif',
                    'Négatif' => 'Négatif',
                    'Neutre' => 'Neutre',
                    'Indéterminé' => 'Indéterminé'
                ],
                'attr' => [
                    'class' => 'form-select',
                    'required' => true
                ]
            ])
            ->add('poubelle', EntityType::class, [
                'class' => Poubelle::class,
                'choice_label' => 'localisation',
                'label' => 'Poubelle concernée',
                'placeholder' => 'Sélectionnez une poubelle',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une poubelle'
                    ])
                ],
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bienetre::class,
        ]);
    }
} 