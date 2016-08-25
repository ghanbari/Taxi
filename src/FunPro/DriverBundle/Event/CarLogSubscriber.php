<?php

namespace FunPro\DriverBundle\Event;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\CarLog;
use FunPro\DriverBundle\Entity\CarRoute;
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
                array('checkCarStatusForWakeful', 255),
                array('onWakeful', 20),
            ),
            CarEvents::CAR_MOVE => array(
                array('checkCarStatusForMove', 255),
                array('onMove', 20),
                array('updateRoute', 18),
            ),
            CarEvents::CAR_SLEEP => array(
                array('checkCarStatusForSleep', 255),
                array('onSleep', 20),
                array('finishRoute', 18),
            ),
        );
    }

    /**
     * @param WakefulEvent $event
     */
    public function checkCarStatusForWakeful(WakefulEvent $event)
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
                array(
                    'carId' => $car->getId(),
                    'status' => Car::getStatusName($car->getStatus()),
                )
            );
            throw new CarStatusException('status must be sleep');
        }
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

        $car->setStatus(Car::STATUS_WAKEFUL);
        $carLog = new CarLog($car, $car->getStatus(), $event->getWakeful()->getPoint());
        $this->logger->addInfo('Car\'s status changed to wakeful', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    /**
     * @param GetMoveCarEvent $event
     */
    public function checkCarStatusForMove(GetMoveCarEvent $event)
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

        if ($status === Car::STATUS_SERVICE_ACCEPT) {
            $status = Car::STATUS_SERVICE_PREPARE;
        } elseif ($status === Car::STATUS_SERVICE_IN_AND_ACCEPT) {
            $status = Car::STATUS_SERVICE_IN_AND_PREPARE;
        } elseif ($status === Car::STATUS_SERVICE_START) {
            $status = Car::STATUS_SERVICE_IN;
        } elseif ($status === Car::STATUS_SERVICE_END) {
            $status = Car::STATUS_WAKEFUL;
        }

        $car->setStatus($status);
        $carLog = new CarLog($car, $status, $event->getCurrentLocation());
        $this->logger->addInfo('Car\'s status changed to ' . Car::getStatusName($status), array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    /**
     * @param GetMoveCarEvent $event
     */
    public function updateRoute(GetMoveCarEvent $event)
    {
        $car = $event->getCar();
        $route = $this->doctrine->getRepository('FunProDriverBundle:CarRoute')->findOneBy(array(
            'car' => $car,
            'finished' => false,
        ));

        if ($route) {
            $linestring = $route->getRoute();
            $linestring->addPoint($event->getCurrentLocation());
            $route->setRoute(clone $linestring);
        } else {
            $route = new CarRoute($car);
            $route->setRoute(new LineString(array($event->getPreviousLocation(), $event->getCurrentLocation())));
            $this->doctrine->getManager()->persist($route);
        }
    }

    /**
     * @param CarEvent $event
     */
    public function checkCarStatusForSleep(CarEvent $event)
    {
        $car = $event->getCar();

        if (!$car) {
            $this->logger->addError('driver\'s car is not defined');
            throw new \RuntimeException('driver\'s car is not defined');
        }

        if ($car->getStatus() !== Car::STATUS_WAKEFUL and $car->getStatus() !== Car::STATUS_SERVICE_END) {
            $this->logger->addError(
                'Car\'s status must be wakeful or service end till it can go sleep',
                array(
                    'carId' => $car->getId(),
                    'status' => Car::getStatusName($car->getStatus()),
                )
            );
            throw new CarStatusException('status must be wakeful or service end');
        }
    }

    /**
     * @param CarEvent $event
     *
     * @throws CarStatusException
     */
    public function onSleep(CarEvent $event)
    {
        $car = $event->getCar();

        $car->setStatus(Car::STATUS_SLEEP);
        $carLog = new CarLog($car, $car->getStatus());
        $this->logger->addInfo('Car\'s status changed to sleep', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    /**
     * @param CarEvent $event
     */
    public function finishRoute(CarEvent $event)
    {
        $car = $event->getCar();
        $route = $this->doctrine->getRepository('FunProDriverBundle:CarRoute')->findOneBy(array(
            'car' => $car,
            'finished' => false,
        ));
        $route->setFinished(true);
    }
}
