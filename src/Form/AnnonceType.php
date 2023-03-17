<?php

namespace App\Form;

use App\Entity\Annonce;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poste', TextType::class,[
                'required'=> false,
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 0,
                    'maxlength' => 255,
                ],
                'label' => 'Poste :',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 0,
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('lieu_de_travail', TextType::class,[
                'required'=> false,
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 0,
                    'maxlength' => 255,
                ],
                'label' => 'Adresse :',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 0,
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('description', TextType::class,[
                'required'=> false,
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 0,
                    'maxlength' => 255,
                ],
                'label' => 'Description :',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 0,
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => "Créer l'annonce"
            ])
            // ->add('createAt')
            // ->add('id_candidat_valid')
            // ->add('id_candidat_invalid')
            // ->add('id_validaton_consultant')
            // ->add('isValid')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
