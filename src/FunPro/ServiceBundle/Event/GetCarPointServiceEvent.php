<?php

namespace FunPro\ServiceBundle\Event;

use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\ServiceBundle\Entity\Service;

/**
 * Class GetCarPointServiceEvent
 *
 * @package FunPro\ServiceBundle\Event
 */
class GetCarPointServiceEvent extends ServiceEvent
{
    /**
     * @var Point $point
     */
    private $point;

    /**
     * @param Service $service
     * @param         $point
     */
    public function __construct(Service $service, $point)
    {
        parent::__construct($service);
        $this->point = $point;
    }

    /**
     * @return Point
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param Point $point
     *
     * @return $this
     */
    public function setPoint(Point $point)
    {
        $this->point = $point;
        return $this;
    }
}
