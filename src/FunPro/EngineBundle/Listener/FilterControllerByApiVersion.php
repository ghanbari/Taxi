<?php

namespace FunPro\EngineBundle\Listener;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\VarDumper\VarDumper;

class FilterControllerByApiVersion implements EventSubscriberInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $currentVersion;

    /**
     * @var array
     */
    private $availableVersions;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', -255),
        );
    }

    /**
     * @param Router $router
     */
    public function __construct(Router $router, $currentVersion, array $availableVersions)
    {
        $this->router = $router;
        $this->currentVersion = $currentVersion;
        $this->availableVersions = $availableVersions;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $_route = $request->attributes->get('_route');
        $route = $this->router->getRouteCollection()->get($_route);

        if ($event->isMasterRequest() and $route->hasOption('version') and $route->getOption('version')) {
            $version = $request->attributes->has('version') ? $request->attributes->get('version') : $this->currentVersion;
            if (!in_array($version, $this->availableVersions)) {
                throw new NotFoundHttpException();
            }

            $controller = $request->attributes->get('_controller');
            $version = 'V' . str_replace('.', '_', $version);
            $controller = str_replace('\Controller\\', '\Controller\\'. $version .'\\', $controller);
            $request->attributes->set('_controller', $controller);
        }
    }
} 