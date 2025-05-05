<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReclamationProcessType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etatRec', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'En attente' => 'Pending',
                    'En cours de traitement' => 'In Progress',
                    'Résolu' => 'Resolved',
                    'Rejeté' => 'Rejected',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un état',
                    ]),
                ],
            ])
            ->add('reponseRec', TextareaType::class, [
                'label' => 'Réponse',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Entrez une réponse au client...',
                ],
            ])
            ->add('noteInterne', TextareaType::class, [
                'label' => 'Note interne (visible uniquement par les administrateurs)',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Note interne facultative...',
                ],
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
} 