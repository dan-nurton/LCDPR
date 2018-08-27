<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 23/07/2018
 * Time: 11:50
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateAuthorFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options){

        $builder
            ->add(
                'surname',
                TextType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Nom de Famille'
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Prénom'
                ]
            )
            ->add(
                'pseudo',
                TextType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Pseudo'
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'E-mail'
                ]
            )
            ->add(
                'shortBio',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Un mot sur Vous'
                ]
            )
            ->add(
                'isAdmin',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Droits administrateur'
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => ['class' => ' btn btn-info btn-lg float-right'],
                    'label' => 'Enregistrer'
                ]
            );
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Author'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'author_form';
    }
}