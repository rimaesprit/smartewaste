<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReclamationAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etatRec', ChoiceType::class, [
                'label' => 'Statut de la réclamation',
                'choices' => [
                    'En attente' => 'Pending',
                    'En cours de traitement' => 'In Progress',
                    'Résolue' => 'Resolved',
                    'Rejetée' => 'Rejected'
                ],
                'placeholder' => false,
                'required' => true
            ])
            ->add('reponse', TextareaType::class, [
                'label' => 'Réponse à la réclamation',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Entrez votre réponse à cette réclamation...'
                ]
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