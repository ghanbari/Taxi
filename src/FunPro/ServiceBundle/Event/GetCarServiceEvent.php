<?php

namespace FunPro\ServiceBundle\Event;

use FunPro\DriverBundle\Entity\Car;
use FunPro\ServiceBundle\Entity\Service;

/**
 * Class GetCarServiceEvent
 *
 * @package FunPro\ServiceBundle\Event
 */
class GetCarServiceEvent extends ServiceEvent
{
    /**
     * @var Car $car
     */
    private $car;

    /**
     * @param Service $service
     * @param         $car
     */
    public function __construct(Service $service, $car)
    {
        parent::__construct($service);
        $this->car = $car;
    }

    /**
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @param Car $car
     *
     * @return $this
     */
    public function setCar($car)
    {
        $this->car = $car;
        return $this;
    }
}
