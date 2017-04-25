<?php

namespace FunPro\DriverBundle\Form;

use FunPro\DriverBundle\Entity\Car;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CarType
 *
 * @package FunPro\DriverBundle\Form
 */
class CarType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', Type\ChoiceType::class, array(
                'choices' => Car::getTypes(),
                'choices_as_values' => true,
            ))
            ->add('color', Type\TextType::class)
            ->add('thirdPartyInsurance', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('pullInsurance', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('technicalDiagnosis', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('trafficPlan', Type\DateType::class, array(
                'format' => 'yyyy-MM-dd',
                'widget' => 'single_text',
                'html5' => false
            ))
            ->add('bodyQuality', Type\ChoiceType::class, array(
                'choices' => Car::getAvailableQuality()
            ))
            ->add('insideQuality', Type\ChoiceType::class, array(
                'choices' => Car::getAvailableQuality()
            ))
            ->add('ownership', Type\ChoiceType::class, array(
                'choices' => Car::getAvailableOwnerships()
            ))
            ->add('plaque', PlaqueType::class)
            ->add('born', Type\TextType::class)
            ->add('description', Type\TextareaType::class, array('required' => false))
            ->add('imageFile', Type\FileType::class, array(
                'required' => false,
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\DriverBundle\Entity\Car'
        ));
    }
}
