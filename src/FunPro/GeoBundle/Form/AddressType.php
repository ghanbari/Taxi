<?php

namespace FunPro\GeoBundle\Form;

use Doctrine\ORM\EntityRepository;
use FunPro\GeoBundle\Form\Type\PointType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddressType
 *
 * @package FunPro\GeoBundle\Form
 */
class AddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', Type\TextType::class)
            ->add('point', PointType::class)
            ->add('postalCode', Type\TextType::class, array(
                'required' => false,
            ))
            ->add('address', Type\TextareaType::class)
            ->add('city', EntityType::class, array(
                'class' => 'FunPro\GeoBundle\Entity\City',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('c');
                    return $qb->where($qb->expr()->gte('c.lvl', 2))
                        ->orderBy('c.name', 'ASC');
                }
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\GeoBundle\Entity\Address'
        ));
    }
}
