<?php

namespace FunPro\DriverBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\CarLog;
use FunPro\DriverBundle\Event\CarEvent;
use FunPro\DriverBundle\Event\FilterMoveEvent;
use FunPro\DriverBundle\Event\FilterSleepEvent;
use FunPro\DriverBundle\Event\FilterWakefulEvent;
use FunPro\DriverBundle\Exception\RuntimeCarStatusException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CarLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            CarEvents::CAR_WAKEFUL => array(
                array('onWakeful', 0),
            ),
            CarEvents::CAR_MOVE => array(
                array('onMove', 0),
            ),
            CarEvents::CAR_SLEEP => array(
                array('onSleep', 0),
            ),
            CarEvents::CAR_ACCEPT_SERVICE => array(
                array('onAccept', 0),
            ),
            CarEvents::CAR_READY_SERVICE => array(
                array('onReady', 0),
            ),
            CarEvents::CAR_START_SERVICE => array(
                array('onStart', 0),
            ),
            CarEvents::CAR_END_SERVICE=> array(
                array('onEnd', 0),
            ),
        );
    }

    public function onWakeful(FilterWakefulEvent $event)
    {
        $car = $event->getWakeful()->getCar();

        if ($car->getStatus() != Car::STATUS_SLEEP) {
            throw new RuntimeCarStatusException('status must be sleep', __FILE__, __LINE__);
        }

        $car->setStatus(Car::STATUS_WAKEFUL);
        $carLog = new CarLog($car, $car->getStatus(), $event->getWakeful()->getPoint());
        $this->doctrine->getManager()->persist($carLog);
        $this->doctrine->getManager()->flush();
    }

    public function onMove(FilterMoveEvent $event)
    {
        $car = $event->getCar();

        $status = $car->getStatus();
        if ($status != Car::STATUS_WAKEFUL and $status != Car::STATUS_SERVICE_PREPARE and $status != Car::STATUS_SERVICE_IN) {
            throw new RuntimeCarStatusException('status must be wakeful, prepare or in service', __FILE__, __LINE__);
        }

        $carLog = new CarLog($car, $car->getStatus(), $event->getCurrentLocation());
        $this->doctrine->getManager()->persist($carLog);
        $this->doctrine->getManager()->flush();
    }

    public function onSleep(FilterSleepEvent $event)
    {
        $car = $event->getCar();

        //TODO: master status must be: (sleep, wakeful, accept, ready, start)
        if ($car->getStatus() != Car::STATUS_WAKEFUL and $car->getStatus() != Car::STATUS_SERVICE_END) {
            throw new RuntimeCarStatusException('status must be wakeful or service end', __FILE__, __LINE__);
        }

        $car->setStatus(Car::STATUS_SLEEP);
        $carLog = new CarLog($car, $car->getStatus());
        $this->doctrine->getManager()->persist($carLog);
        $this->doctrine->getManager()->flush();
    }

    public function onAccept(CarEvent $event)
    {
        $car = $event->getCar();

        if ($car->getStatus() != Car::STATUS_WAKEFUL) {
            throw new RuntimeCarStatusException('status must be wakeful', __FILE__, __LINE__);
        }

        $car->setStatus(Car::STATUS_SERVICE_PREPARE);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_ACCEPT);
        $carLog2 = new CarLog($car, $car->getStatus());
        $this->doctrine->getManager()->persist($carLog);
        $this->doctrine->getManager()->persist($carLog2);
    }

    public function onReady(CarEvent $event)
    {
        $car = $event->getCar();

        if ($car->getStatus() != Car::STATUS_SERVICE_PREPARE) {
            throw new RuntimeCarStatusException('status must be prepare', __FILE__, __LINE__);
        }

        $car->setStatus(Car::STATUS_SERVICE_READY);
        $carLog = new CarLog($car, $car->getStatus());
        $this->doctrine->getManager()->persist($carLog);
    }

    public function onStart(CarEvent $event)
    {
        $car = $event->getCar();

        if ($car->getStatus() != Car::STATUS_SERVICE_READY) {
            throw new RuntimeCarStatusException('status must be ready', __FILE__, __LINE__);
        }

        $car->setStatus(Car::STATUS_SERVICE_IN);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_START);
        $carLog2 = new CarLog($car, $car->getStatus());
        $this->doctrine->getManager()->persist($carLog);
        $this->doctrine->getManager()->persist($carLog2);
    }

    public function onEnd(CarEvent $event)
    {
        $car = $event->getCar();

        if ($car->getStatus() != Car::STATUS_SERVICE_IN) {
            throw new RuntimeCarStatusException('status must be in service', __FILE__, __LINE__);
        }

        $car->setStatus(Car::STATUS_WAKEFUL);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_END);
        $this->doctrine->getManager()->persist($carLog);
    }
}