<?php

namespace FunPro\GeoBundle\Form\Type;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class PointType
 *
 * @package FunPro\GeoBundle\Form\Type
 */
class PointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('longitude', Type\NumberType::class, array(
                'property_path' => 'x',
            ))
            ->add('latitude', Type\NumberType::class, array(
                'property_path' => 'y',
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CrEOF\Spatial\PHP\Types\Geometry\Point'
        ));
    }
}
