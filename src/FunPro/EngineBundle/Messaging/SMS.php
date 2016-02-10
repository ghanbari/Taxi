<?php

namespace FunPro\EngineBundle\Messaging;

use SmsSender\HttpAdapter\BuzzHttpAdapter;
use SmsSender\HttpAdapter\CurlHttpAdapter;
use SmsSender\SmsSender;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class SMS extends SmsSender implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    private $config;

    /**
     * @var \SmsSender\SmsSender
     */
    private $sender;

    public function __construct()
    {
        $this->config = $this->container->getParameter('sms');
        $this->sender = new \SmsSender\SmsSender();

        $provider = $this->getProvider();
        $this->sender->registerProvider($provider);
    }

    private function getAdapter()
    {
        $adapterName = $this->config['transport'];

        if ($adapterName == 'curl') {
            $adapter = new CurlHttpAdapter();
        } else {
            $buzz    = new \Buzz\Browser(new \Buzz\Client\Curl());
            $adapter = new BuzzHttpAdapter();
        }

        return $adapter;
    }

    public function getProvider()
    {
        $providerName = $this->config['provider'];
        $provider = $this->container->get('fun_pro_engine.messaging.sms.provider.' . $providerName);

        return $provider;
    }
} 