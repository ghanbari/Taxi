<?php

namespace FunPro\FinancialBundle\Form;

use FunPro\GeoBundle\Form\Type\PointType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DiscountCodeType
 * @package FunPro\FinancialBundle\Form
 */
class DiscountCodeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', Type\TextType::class, array(
                'required' => true,
            ))
            ->add('code', Type\TextType::class, array(
                'pattern' => '[A-Z0-9a-z]{0,10}',
            ))
            ->add('discount', Type\NumberType::class)
            ->add('maxUsage', Type\NumberType::class)
            ->add('maxUsagePerUser', Type\NumberType::class)
            ->add('originLocation', PointType::class)
            ->add('locationRadius', Type\NumberType::class)
            ->add('expiredAt', Type\DateType::class, array(
                'widget' => 'single_text',
                'html5' => false,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\FinancialBundle\Entity\DiscountCode',
        ));
    }
}
