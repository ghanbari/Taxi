<?php

namespace FunPro\DriverBundle\Form;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\GeoBundle\Form\AddressType;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Form\UserType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Class DriverType
 *
 * @package FunPro\DriverBundle\Form
 */
class DriverType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parentName', Type\TextType::class)
            ->add('born', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('education', Type\ChoiceType::class, array(
                'choices' => array(
                    'zire.diplome' => Driver::EDUCATION_UNDER_DIPLOMA,
                    'diplome' => Driver::EDUCATION_ASSOCIATE_DEGREE,
                    'lisans' => Driver::EDUCATION_BACHELOR,
                    'foghe.lisans' => Driver::EDUCATION_MASTER_DEGREE,
                ),
                'choices_as_values' => true,
            ))
            ->add('codStatus', Type\ChoiceType::class, array(
                'choices' => array(
                    'mohaf' => Driver::COD_END,
                    'payan.khedmat' => Driver::COD_EXEMPTION,
                    'mohafiyate.tahsili' => Driver::COD_EDUCATION_EXEMPTION,
                ),
                'choices_as_values' => true,
            ))
            ->add('marriage', Type\ChoiceType::class, array(
                'multiple' => false,
                'expanded' => true,
                'choices' => array(
                    0 => 'no',
                    1 => 'yes',
                )
            ))
            ->add('shebaNumber', Type\TextType::class)
            ->add('startActivity', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('endActivity', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('learningCourse', Type\ChoiceType::class, array(
                'multiple' => false,
                'expanded' => true,
                'choices' => array(
                    0 => 'no',
                    1 => 'yes',
                )
            ))
            ->add('mobile', Type\TextType::class)
            ->add('contractNumber', Type\TextType::class)
            ->add('nationalCode', Type\TextType::class, array(
                'by_reference' => false,
            ))
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
            ->add('email', Type\EmailType::class, array('required' => false));
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
        return UserType::class;
    }
}
