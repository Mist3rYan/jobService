<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class,[
            'required'=> false,
            'attr' => [
                'class' => 'form-control',
                'minlength' => 0,
                'maxlength' => 50,
            ],
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Length([
                    'min' => 0,
                    'max' => 50,
                ]),
            ],
        ])
        ->add('firstName', TextType::class,[
            'required'=> false,
            'attr' => [
                'class' => 'form-control',
                'minlength' => 0,
                'maxlength' => 50,
            ],
            'label' => 'Prénom',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Length([
                    'min' => 0,
                    'max' => 50,
                ]),
            ],
        ])
        ->add('address', TextType::class,[
            'required'=> false,
            'attr' => [
                'class' => 'form-control',
                'minlength' => 0,
                'maxlength' => 255,
            ],
            'label' => 'Adresse',
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
        ->add('cv', FileType::class,[
            'data_class' => null,
            'required'=> false,
            'attr' => [
                'class' => 'form-control',
            ],
            'label' => 'Déposer votre CV',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
        ])
        ->add('plainPassword', PasswordType::class,[
            'attr' => [
                'class' => 'form-control',
            ],
            'label' => 'Mot de passe',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
        ])
        ->add('submit', SubmitType::class, [
            'attr' => [
                'class' => 'btn btn-primary mt-4'
            ],
            'label' => 'Modifier'
        ])
    ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
