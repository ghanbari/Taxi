<?php

namespace FunPro\PassengerBundle\Form\Type;

use FunPro\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProfileType
 *
 * @package FunPro\PassengerBundle\Form\Type
 */
class ProfileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\EmailType::class, array(
                'label' => 'form.email',
                'required' => false,
            ))
            ->add('name', Type\TextType::class, array(
                'label' => 'name',
                'required' => false
            ))
            ->add('age', Type\NumberType::class, array(
                'label' => 'age',
                'required' => false,
                'scale' => 0,
            ))
            ->add('sex', Type\ChoiceType::class, array(
                'label' => 'sex',
                'required' => false,
                'choices' => array(
                    'male' => User::SEX_MALE,
                    'female' => User::SEX_FEMALE,
                ),
                'choices_as_values' => true,
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('description', Type\TextareaType::class, array(
                'label' => 'description',
                'required' => false,
            ))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\PassengerBundle\Entity\Passenger'
        ));
    }

    public function getBlockPrefix()
    {
        return null;
    }
}
