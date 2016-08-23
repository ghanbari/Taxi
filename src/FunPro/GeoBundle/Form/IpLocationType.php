<?php

namespace FunPro\GeoBundle\Form;

use FunPro\GeoBundle\Form\Type\PointType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class IpLocationType
 *
 * @package FunPro\GeoBundle\Form
 */
class IpLocationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('location', PointType::class)
            ->add('ip', Type\TextType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\GeoBundle\Entity\IpLocation',
            'csrf_protection' => false,
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
