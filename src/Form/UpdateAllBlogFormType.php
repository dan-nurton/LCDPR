<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 22/07/2018
 * Time: 15:29
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UpdateAllBlogFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options){

        $builder
            ->add(
                'title',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Titre'
                ]
            )
            ->add(
                'category',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Categorie'
                ]
            )
            ->add(
                'cover',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Couverture'
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Synopsis'
                ]
            )
            ->add(
                'review',
                TextareaType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                    'label' => 'Avis'
                ]
            )
            ->add(
                'update',
                SubmitType::class,
                [
                    'attr' => ['class' => 'form-control btn btn-info btn-lg float-right'],
                    'label' => 'Ajouter'
                ]
            );
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\BlogPost'
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