<?php

namespace FunPro\PassengerBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plainPassword', Type\RepeatedType::class, array(
                'label' => 'password',
            ))
            ->add('mobile', Type\TextType::class, array(
                'label' => 'mobile',
            ))
            ->add('name', Type\TextType::class, array(
                'label' => 'name',
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
