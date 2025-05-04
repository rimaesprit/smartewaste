<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\File;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeRec', ChoiceType::class, [
                'label' => 'Type de réclamation',
                'choices' => [
                    'Voirie' => 'Voirie',
                    'Éclairage public' => 'Éclairage public',
                    'Propreté' => 'Propreté',
                    'Espaces verts' => 'Espaces verts',
                    'Nuisances sonores' => 'Nuisances sonores',
                    'Eau et assainissement' => 'Eau et assainissement',
                    'Signalisation routière' => 'Signalisation routière',
                    'Mobilier urbain' => 'Mobilier urbain',
                    'Autre' => 'Autre'
                ],
                'placeholder' => 'Sélectionnez un type de réclamation',
                'required' => true
            ])
            ->add('reclamation', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 5],
                'required' => true
            ])
            ->add('address', TextType::class, [
                'label' => 'Adresse concernée',
                'required' => true,
                'attr' => [
                    'class' => 'form-control map-address',
                    'placeholder' => 'Recherchez une adresse ou cliquez sur la carte',
                    'data-map-display' => 'true'
                ]
            ])
            ->add('latitude', HiddenType::class, [
                'mapped' => false,
                'attr' => ['class' => 'map-latitude']
            ])
            ->add('longitude', HiddenType::class, [
                'mapped' => false,
                'attr' => ['class' => 'map-longitude']
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo (optionnelle)',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => '.jpg,.jpeg,.png,.gif',
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => 'Formats acceptés: JPG, PNG, GIF. Taille max: 5MB'
                ]
            ])
        ;
        
        // Ajouter l'option de suppression de la photo si nécessaire
        if ($options['include_delete_photo']) {
            $builder->add('deletePhoto', CheckboxType::class, [
                'label' => 'Supprimer la photo',
                'required' => false,
                'mapped' => false
            ]);
        }
        
        // Ajouter les champs administrateur si nécessaire
        if ($options['is_admin']) {
            $builder
                ->add('etatRec', ChoiceType::class, [
                    'label' => 'État',
                    'choices' => [
                        'En attente' => 'Pending',
                        'En cours' => 'In Progress',
                        'Résolue' => 'Resolved',
                        'Rejetée' => 'Rejected'
                    ],
                    'required' => true
                ])
                ->add('reponse', TextareaType::class, [
                    'label' => 'Réponse',
                    'required' => false,
                    'attr' => [
                        'rows' => 5,
                        'placeholder' => 'Réponse à la réclamation'
                    ]
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
            'include_delete_photo' => false,
            'is_admin' => false,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'allow_extra_fields' => true,
        ]);
    }
} 