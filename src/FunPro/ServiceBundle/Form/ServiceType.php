<?php

namespace FunPro\ServiceBundle\Form;

use FunPro\ServiceBundle\Entity\Service;
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
            ->add('startAddress', Type\TextareaType::class, array(
                'required' => true,
            ))
            ->add('endAddress', Type\TextareaType::class, array(
                'required' => true,
            ))
            ->add('type', Type\ChoiceType::class, array(
                'choices' => Service::getTypes(),
                'choices_as_values' => true,
                'required' => false,
            ))
            ->add('desire', Type\ChoiceType::class, array(
                'choices' => Service::getDesireOptions(),
                'choices_as_values' => true,
                'required' => false,
            ))
            ->add('description', Type\TextareaType::class, array(
                'required' => false,
            ))
            ->add('propagationList', Type\CollectionType::class, array(
                'description' => 'array of driver ids',
                'entry_type' => 'text',
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'mapped' => false,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\ServiceBundle\Entity\Service'
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
