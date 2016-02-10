<?php

namespace FunPro\DriverBundle\Event;

use FunPro\DriverBundle\Entity\Car;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use Symfony\Component\EventDispatcher\Event;

class FilterMoveEvent extends Event
{
    /**
     * @var Car
     */
    private $car;

    /**
     * @var Point
     */
    private $currentLocation;

    /**
     * @var Point
     */
    private $previousLocation;

    public function __construct(Car $car, Point $previous, Point $current)
    {
        $this->car = $car;
        $this->currentLocation = $current;
        $this->previousLocation = $previous;
    }

    /**
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @return Point
     */
    public function getCurrentLocation()
    {
        return $this->currentLocation;
    }

    /**
     * @return Point
     */
    public function getPreviousLocation()
    {
        return $this->previousLocation;
    }
} 