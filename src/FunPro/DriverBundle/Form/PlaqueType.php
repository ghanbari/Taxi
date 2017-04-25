<?php

namespace FunPro\DriverBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaqueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstNumber', Type\NumberType::class, array('error_bubbling' => true))
            ->add('secondNumber', Type\NumberType::class, array('error_bubbling' => true))
            ->add('cityNumber', Type\NumberType::class, array('error_bubbling' => true))
            ->add('areaCode', Type\TextType::class, array(
                'attr' => array('maxlength' => 1),
                'error_bubbling' => true,
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\DriverBundle\Entity\Plaque',
            'error_bubbling' => false
        ));
    }
}
