<?php

namespace FunPro\DriverBundle\Form;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use FunPro\GeoBundle\Form\AddressType;
use FunPro\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

class DriverType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class)
            ->add('age', Type\NumberType::class)
            ->add('sex', Type\ChoiceType::class, array(
                'choices' => array(
                    'male' => User::SEX_MALE,
                    'female' => User::SEX_FEMALE,
                ),
                'multiple' => false,
                'expanded' => true,
                'choices_as_values' => true,
            ))
            ->add('description', Type\TextareaType::class, array(
                'required' => false,
            ))
            ->add('mobile', Type\TextType::class)
            ->add('contractNumber', Type\TextType::class)
            ->add('nationalCode', Type\TextType::class)
//            ->add('avatar')
            ->add('contact', Type\CollectionType::class, array(
                'entry_type' => Type\TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('agency', EntityType::class, array(
                'class' => 'FunPro\AgentBundle\Entity\Agency',
                'choice_label' => 'name'
            ))
            ->add('address', AddressType::class)
            ->add('email', Type\EmailType::class, array('required'=>false))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\DriverBundle\Entity\Driver'
        ));
    }

    public function getParent()
    {
        return RegistrationFormType::class;
    }
}
