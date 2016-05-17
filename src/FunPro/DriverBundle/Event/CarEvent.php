<?php

namespace FunPro\DriverBundle\Event;

use FunPro\DriverBundle\Entity\Car;
use Symfony\Component\EventDispatcher\Event;

class CarEvent extends Event
{
    /**
     * @var Car
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