<?php

namespace FunPro\DriverBundle;

class CarEvents
{
    /**
     * the car.wakeful event is dispatched each time a car status change to wakeful
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\FilterWakefulEvent
     *
     * @var string
     */
    const CAR_WAKEFUL = 'car.wakeful';

    /**
     * the car.move event is dispatched each time a wakeful car location is changed
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\FilterMoveEvent
     *
     * @var string
     */
    const CAR_MOVE = 'car.move';

    /**
     * the car.sleep event is dispatched each time a car status change to sleep
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\FilterSleepEvent
     *
     * @var string
     */
    const CAR_SLEEP = 'car.sleep';

    /**
     * the car.accept_service is dispatched when driver accept a service
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\CarEvent
     *
     * @var string
     */
    const CAR_ACCEPT_SERVICE = 'car.accept_service';

    /**
     * the car.ready_service is dispatched when driver is in passenger place.
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\CarEvent
     *
     * @var string
     */
    const CAR_READY_SERVICE = 'car.ready_service';

    /**
     * the car.start_service is dispatched when driver start move of passenger
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\CarEvent
     *
     * @var string
     */
    const CAR_START_SERVICE = 'car.start_service';

    /**
     * the car.end_service is dispatched when driver end move of passenger
     *
     * the event listener receive an
     * FunPro\DriverBundle\Event\CarEvent
     *
     * @var string
     */
    const CAR_END_SERVICE = 'car.end_service';
} 