<?php

namespace FunPro\DriverBundle\Event;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use FunPro\DriverBundle\Entity\Car;

/**
 * Class GetMoveCarEvent
 *
 * @package FunPro\DriverBundle\Event
 */
class GetMoveCarEvent extends CarEvent
{
    /**
     * @var Point $currentLocation
     */
    private $currentLocation;

    /**
     * @var Point $previousLocation
     */
    private $previousLocation;

    /**
     * @param Car   $car
     * @param Point $previous
     * @param Point $current
     */
    public function __construct(Car $car, Point $previous, Point $current)
    {
        parent::__construct($car);
        $this->currentLocation = $current;
        $this->previousLocation = $previous;
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
