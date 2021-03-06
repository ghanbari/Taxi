<?php

namespace FunPro\ServiceBundle;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ServiceEvents
 *
 * @package FunPro\ServiceBundle
 */
class ServiceEvents extends Event
{
    /**
     * The event occurs when the passenger request one service.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\ServiceEvent instance.
     */
    const SERVICE_REQUESTED = 'service.requested';

    /**
     * The event occurs when the passenger or agent cancel one service.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\ServiceEvent instance.
     */
    const SERVICE_CANCELED = 'service.canceled';

    /**
     * The event occurs when the driver accept service.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\GetCarPointServiceEvent instance.
     */
    const SERVICE_ACCEPTED = 'service.accepted';

    /**
     * The event occurs when the driver reject service.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\GetCarServiceEvent instance.
     */
    const SERVICE_REJECTED = 'service.rejected';

    /**
     * The event occurs when the driver send ready alarm.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\GetCarPointServiceEvent instance.
     */
    const SERVICE_READY = 'service.ready';

    /**
     * The event occurs when the driver start service.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\ServiceEvent instance.
     */
    const SERVICE_START = 'service.start';

    /**
     * The event occurs when the driver finish service.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\ServiceEvent instance.
     */
    const SERVICE_FINISH = 'service.finish';

    /**
     * The event occurs when the service information update.
     *
     * The event listener method receives a FunPro\ServiceBundle\Event\ServiceEvent instance.
     */
    const SERVICE_UPDATE = 'service.update';
}
