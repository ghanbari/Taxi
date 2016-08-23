<?php

namespace FunPro\EngineBundle\Profiler;

use FunPro\EngineBundle\GCM\Success;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class GCMDataCollector extends DataCollector
{
    /**
     * Collects data for the given Request and Response.
     *
     * @param Request $request A Request instance
     * @param Response $response A Response instance
     * @param \Exception $exception An Exception instance
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['total'] = count($this->data['messages']);
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return 'GCM';
    }

    public function __construct()
    {
        $this->data = array(
            'success' => 0,
            'failure' => 0,
            'canonical' => 0,
            'messages' => array(),
        );
    }

    public function add(array $messages, Success $response=null)
    {
        if ($response) {
            $this->data['success'] += $response->getSuccess();
            $this->data['failure'] += $response->getFailure();
            $this->data['canonical'] += $response->getCanonicalIds();
        }

        $this->data['messages'] = array_merge($messages, $this->data['messages']);
    }

    /**
     * @return int
     */
    public function getCanonicalCount()
    {
        return $this->data['canonical'];
    }

    /**
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->data['success'];
    }

    /**
     * @return int
     */
    public function getFailureCount()
    {
        return $this->data['failure'];
    }

    /**
     * @return \FunPro\UserBundle\Entity\Message[]
     */
    public function getMessages()
    {
        return $this->data['messages'];
    }

    public function getMessageCount()
    {
        return count($this->data['messages']);
    }
} 