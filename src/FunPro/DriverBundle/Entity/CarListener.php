<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CarListener
 *
 * @package FunPro\DriverBundle\Entity
 */
class CarListener
{
    /**
     * @ORM\PrePersist()
     *
     * @param Car                $car
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Car $car, LifecycleEventArgs $event)
    {
        /** @var EntityManager $manager */
        $manager = $event->getObjectManager();

        $queryBuilder = $manager->getRepository('FunProDriverBundle:Car')
            ->createQueryBuilder('c');

        $query = $queryBuilder->update()
            ->set('c.current', false)
            ->where('c.driver', ':driver')
            ->setParameter('driver', $car->getDriver())
            ->getQuery();

        $query->execute();
    }
}
