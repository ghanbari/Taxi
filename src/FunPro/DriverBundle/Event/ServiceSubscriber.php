<?php

namespace FunPro\DriverBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\CarLog;
use FunPro\DriverBundle\Exception\CarStatusException;
use FunPro\ServiceBundle\Event\GetCarPointServiceEvent;
use FunPro\ServiceBundle\Event\ServiceEvent;
use FunPro\ServiceBundle\ServiceEvents;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ServiceSubscriber implements EventSubscriberInterface
{
    /**
     * @var Registry $doctrine
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
            ServiceEvents::SERVICE_ACCEPTED => array('onServiceAccept', 20),
            ServiceEvents::SERVICE_CANCELED => array('onServiceCanceled', 20),
            ServiceEvents::SERVICE_READY => array('onServiceReady', 20),
            ServiceEvents::SERVICE_START => array('onServiceStart', 20),
            ServiceEvents::SERVICE_FINISH => array('onServiceFinish', 20),
        );
    }

    public function onServiceCanceled(ServiceEvent $event)
    {
        $service = $event->getService();
        $car = $service->getCar();

        if (!$car) {
            $this->logger->addInfo(
                'Service haven\'t car, driver status willn\'t changed',
                array('service' => $service->getId())
            );
            return;
        }

        $car->setStatus(Car::STATUS_WAKEFUL);
        $carLog = new CarLog($car, Car::STATUS_WAKEFUL);
        $this->logger->addInfo('Car\'s status changed to wakeful', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    public function onServiceAccept(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();
        $car = $service->getCar();

        if (!$car) {
            $this->logger->addError('Service haven\'t car', array('service' => $service->getId()));
            throw new \LogicException('Service haven\'t car');
        }

        if ($car->getStatus() !== Car::STATUS_WAKEFUL) {
            $this->logger->addError(
                'Car\'s status must be wakeful till it can accept one service',
                array('car' => $car->getId())
            );
            throw new CarStatusException('status must be wakeful');
        }

        $car->setStatus(Car::STATUS_SERVICE_ACCEPT);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_ACCEPT, $event->getPoint());
        $this->logger->addInfo('Car\'s status changed to accepted', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    public function onServiceReady(GetCarPointServiceEvent $event)
    {
        $service = $event->getService();
        $car = $service->getCar();

        if (!$car) {
            $this->logger->addError('Service haven\'t car', array('service' => $service->getId()));
            throw new \LogicException('Service haven\'t car');
        }

        if ($car->getStatus() !== Car::STATUS_SERVICE_PREPARE) {
            $this->logger->addError(
                'Car\'s status must be prepare till it can send ready alarm',
                array('carId' => $car->getId())
            );
            throw new CarStatusException('status must be prepare');
        }

        $car->setStatus(Car::STATUS_SERVICE_READY);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_READY, $event->getPoint());
        $this->logger->addInfo('Car\'s status changed to ready', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    public function onServiceStart(ServiceEvent $event)
    {
        $service = $event->getService();
        $car = $service->getCar();

        if (!$car) {
            $this->logger->addError('Service haven\'t car', array('service' => $service->getId()));
            throw new \LogicException('Service haven\'t car');
        }

        if ($car->getStatus() !== Car::STATUS_SERVICE_READY) {
            $this->logger->addError(
                'Car\'s status must be ready till it can start service',
                array('carId' => $car->getId())
            );
            throw new CarStatusException('status must be ready');
        }

        $car->setStatus(Car::STATUS_SERVICE_START);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_START);
        $this->logger->addInfo('Car\'s status changed to start', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }

    public function onServiceFinish(ServiceEvent $event)
    {
        $service = $event->getService();
        $car = $service->getCar();

        if (!$car) {
            $this->logger->addError('Service haven\'t car', array('service' => $service->getId()));
            throw new \LogicException('Service haven\'t car');
        }

        if ($car->getStatus() !== Car::STATUS_SERVICE_IN) {
            $this->logger->addError(
                'Car\'s status must be in service till it can stop service',
                array('carId' => $car->getId())
            );
            throw new CarStatusException('status must be in service');
        }

        $car->setStatus(Car::STATUS_SERVICE_END);
        $carLog = new CarLog($car, Car::STATUS_SERVICE_END);
        $this->logger->addInfo('Car\'s status changed to end', array('carId' => $car->getId()));
        $this->doctrine->getManager()->persist($carLog);
    }
}
