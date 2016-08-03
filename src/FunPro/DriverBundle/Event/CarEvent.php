<?php

namespace FunPro\DriverBundle\Event;

use FunPro\DriverBundle\Entity\Car;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class CarEvent
 *
 * @package FunPro\DriverBundle\Event
 */
class CarEvent extends Event
{
    /**
     * @var Car $car
     */
    private $car;

    public function __construct(Car $car)
    {
        $this->car = $car;
    }

    /**
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }
}
