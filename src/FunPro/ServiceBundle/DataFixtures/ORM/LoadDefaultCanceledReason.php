<?php

namespace FunPro\ServiceBundle\Datafixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\ServiceBundle\Entity\CanceledReason;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadDefaultCanceledReason
 *
 * @package FunPro\ServiceBundle\Datafixtures\ORM
 */
class LoadDefaultCanceledReason implements
    FixtureInterface,
    ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $translator = $this->container->get('translator');
        $delay = new CanceledReason($translator->trans('delay'), $translator->trans('delay.in.service'));
        $carCrash = new CanceledReason(
            $translator->trans('my.car.is.crashing'),
            $translator->trans('my.car.is.crashing'),
            CanceledReason::GROUP_DRIVER
        );

        $manager->persist($carCrash);
        $manager->persist($delay);
        $manager->flush();
    }
}
