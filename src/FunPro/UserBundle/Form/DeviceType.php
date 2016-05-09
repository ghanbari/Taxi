<?php

namespace FunPro\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeviceType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('deviceToken', Type\TextareaType::class)
            ->add('deviceIdentifier', Type\TextType::class)
            ->add('deviceName', Type\TextType::class)
            ->add('os', Type\TextType::class)
            ->add('deviceModel', Type\TextType::class)
            ->add('deviceVersion', Type\TextType::class)
            ->add('appVersion', Type\TextType::class)
            ->add('appName', Type\TextType::class)
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\UserBundle\Entity\Device'
        ));
    }
}
