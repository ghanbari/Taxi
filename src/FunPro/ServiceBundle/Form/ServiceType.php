<?php

namespace FunPro\ServiceBundle\Form;

use FunPro\GeoBundle\Form\Type\PointType;
use FunPro\ServiceBundle\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startPoint', PointType::class)
            ->add('endPoint', PointType::class)
            ->add('startAddress', Type\TextareaType::class)
            ->add('endAddress', Type\TextareaType::class)
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
                'entry_type' => 'text',
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'mapped' => false,
            ));
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
