<?php

namespace FunPro\UserBundle\Form;

use FunPro\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class UserType
 *
 * @package FunPro\UserBundle\Form
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class)
            ->add('age', Type\NumberType::class, ['required' => false])
            ->add('sex', Type\ChoiceType::class, array(
                'choices' => array(
                    'male' => User::SEX_MALE,
                    'female' => User::SEX_FEMALE,
                ),
                'multiple' => false,
                'expanded' => true,
                'choices_as_values' => true,
            ))
            ->add('description', Type\TextareaType::class, ['required' => false])
            ->add('avatarFile', Type\FileType::class, ['required' => false])
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\UserBundle\Entity\User'
        ));
    }

    public function getParent()
    {
        return RegistrationFormType::class;
    }
}
