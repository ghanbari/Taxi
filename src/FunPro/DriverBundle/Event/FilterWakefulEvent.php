<?php

namespace FunPro\DriverBundle\Event;

use FunPro\ServiceBundle\Entity\Wakeful;
use Symfony\Component\EventDispatcher\Event;

class FilterWakefulEvent extends Event
{
    /**
     * @var Wakeful
     */
    private $wakeful;

    public function __construct(Wakeful $wakeful)
    {
        $this->wakeful = $wakeful;
    }

    /**
     * @return Wakeful
     */
    public function getWakeful()
    {
        return $this->wakeful;
    }
} 