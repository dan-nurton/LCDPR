<?php
/**
 * Created by PhpStorm.
 * User: Dan-n
 * Date: 30/07/2018
 * Time: 12:57
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentFormType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Below is all of the inputs that the author will see when creating their new author access. Each one contains its own attributes where applicable, such as the field `name` being required.
        // Please note the naming of these form elements is the same naming as the entity. We will be passing in the Author entity so the form knows what the data is for.
        $builder
            ->add(
                'content',
                TextType::class,
                [
                    'constraints' => [new NotBlank()],
                    'attr' => ['class' => 'form-control'],
                ]
            )

            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => ['class' => 'form-control btn-primary pull-right'],
                    'label' => 'Poster'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Comment'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'comment_form';
    }

}