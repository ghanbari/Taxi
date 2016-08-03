<?php

namespace FunPro\DriverBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\CarLog;
use FunPro\DriverBundle\Event\CarEvent;
use FunPro\DriverBundle\Event\GetMoveCarEvent;
use FunPro\DriverBundle\Event\WakefulEvent;
use FunPro\DriverBundle\Exception\CarStatusException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CarLogSubscriber
 *
 * @package FunPro\DriverBundle\EventListener
 */
class CarLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Logger $logger
     */
    private $logger;

    /**
     * @param Registry $doctrine
     * @param Logger   $logger
     */
    public function __construct(Registry $doctrine, Logger $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
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
                array('onWakeful', 20),
            ),
            CarEvents::CAR_MOVE => array(
                array('onMove', 20),
            ),
            CarEvents::CAR_SLEEP => array(
                array('onSleep', 20),
            ),
        );
    }

    /**
     * change car status into wakeful
     *
     * @param WakefulEvent $event
     */
    public function onWakeful(WakefulEvent $event)
    {
        $wakeful = $event->getWakeful();
        $car = $wakeful->getCar();

        if (!$car) {
            $this->logger->addError('driver\'s car is not defined', array('wakeful' => $wakeful->getId()));
            throw new \RuntimeException('driver\'s car is not defined');
        }

        if ($car->getStatus() !== Car::STATUS_SLEEP) {
            $this->logger->addError(
                'Car\'s status must be sleep till it change into wakeful',
                array('car' => $car->getId())
            );
            throw new CarStatusException('status must be sleep');
        }

        $car->setStatus(Car::STATUS_WAKEFUL);
        $carLog = new CarLog($car, $car->getStatus(), $event->getWakeful()->getPoint());
        $this->logger->addInfo('Car\'s status changed to wakeful', array($car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    /**
     * @param GetMoveCarEvent $event
     *
     * @throws CarStatusException
     */
    public function onMove(GetMoveCarEvent $event)
    {
        $car = $event->getCar();
        $status = $car->getStatus();

        if ($status === Car::STATUS_SLEEP) {
            $this->logger->addError(
                'Car\'s status must not be sleep',
                array('car' => $car->getId())
            );
            throw new CarStatusException('status must not be sleep');
        }

        if ($status === Car::STATUS_SERVICE_ACCEPT) {
            $status = Car::STATUS_SERVICE_PREPARE;
        } elseif ($status === Car::STATUS_SERVICE_START) {
            $status = Car::STATUS_SERVICE_IN;
        } elseif ($status === Car::STATUS_SERVICE_END) {
            $status = Car::STATUS_WAKEFUL;
        }

        $car->setStatus($status);
        $carLog = new CarLog($car, $status, $event->getCurrentLocation());
        $this->logger->addInfo('Car\'s status changed to'.$status, array($car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    /**
     * @param CarEvent $event
     *
     * @throws CarStatusException
     */
    public function onSleep(CarEvent $event)
    {
        $wakeful = $event->getWakeful();
        $car = $wakeful->getCar();

        if (!$car) {
            $this->logger->addError('driver\'s car is not defined', array('wakeful' => $wakeful->getId()));
            throw new \RuntimeException('driver\'s car is not defined');
        }

        if ($car->getStatus() !== Car::STATUS_WAKEFUL or $car->getStatus() !== Car::STATUS_SERVICE_END) {
            $this->logger->addError(
                'Car\'s status must be wakeful or service end till it can go sleep',
                array('car' => $car->getId())
            );
            throw new CarStatusException('status must be wakeful or service end');
        }

        $car->setStatus(Car::STATUS_SLEEP);
        $carLog = new CarLog($car, $car->getStatus());
        $this->logger->addInfo('Car\'s status changed to sleep', array($car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }
}
