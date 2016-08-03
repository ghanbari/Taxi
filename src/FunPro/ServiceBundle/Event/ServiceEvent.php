<?php

namespace FunPro\ServiceBundle\Event;

use FunPro\ServiceBundle\Entity\Service;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ServiceEvent
 *
 * @package FunPro\ServiceBundle\Event
 */
class ServiceEvent extends Event
{
    /**
     * @var Service $service
     */
    private $service;

    /**
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param Service $service
     *
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }
}
