<?php

namespace FunPro\ServiceBundle\Form;

use FunPro\ServiceBundle\Entity\Requested;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startPoint', \FunPro\GeoBundle\Form\Type\PointType::class)
            ->add('endPoint', \FunPro\GeoBundle\Form\Type\PointType::class, array(
                'required' => false,
            ))
            ->add('type', Type\ChoiceType::class, array(
                'choices' => Requested::getTypes(),
                'choices_as_values' => true,
            ))
            ->add('desire', Type\ChoiceType::class, array(
                'choices' => Requested::getDesireOptions(),
                'choices_as_values' => true,
            ))
            ->add('description', Type\TextareaType::class, array(
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
            'data_class' => 'FunPro\ServiceBundle\Entity\Requested'
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
