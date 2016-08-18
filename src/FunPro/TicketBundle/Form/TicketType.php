<?php

namespace FunPro\TicketBundle\Form;

use FunPro\TicketBundle\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type;

/**
 * Class TicketType
 *
 * @package FunPro\TicketBundle\Form
 */
class TicketType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', Type\TextType::class)
            ->add('message', Type\TextareaType::class)
            ->add('priority', Type\ChoiceType::class, array(
                'choices' => Ticket::getValidPriority(),
                'choices_as_values' => true,
            ))
            ->add('type', Type\ChoiceType::class, array(
                'choices' => Ticket::getValidType(),
                'choices_as_values' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FunPro\TicketBundle\Entity\Ticket'
        ));
    }
}
