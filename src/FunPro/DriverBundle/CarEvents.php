<?php

namespace FunPro\DriverBundle;

class CarEvents
{
    /**
     * the car.wakeful event is dispatched each time a car status change to wakeful
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\WakefulEvent
     *
     * @var string
     */
    const CAR_WAKEFUL = 'car.wakeful';

    /**
     * the car.move event is dispatched each time a wakeful car location is changed
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\GetMoveCarEvent
     *
     * @var string
     */
    const CAR_MOVE = 'car.move';

    /**
     * the car.sleep event is dispatched each time a car status change to sleep
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\CarEvent
     *
     * @var string
     */
    const CAR_SLEEP = 'car.sleep';
}
