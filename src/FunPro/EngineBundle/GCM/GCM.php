<?php

namespace FunPro\EngineBundle\GCM;

use Buzz\Browser;
use Buzz\Client\Curl;
use Buzz\Exception\RequestException;
use Buzz\Exception\RuntimeException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\EngineBundle\Profiler\GCMDataCollector;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\Message;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;

/**
 * Class GCM
 *
 * @package FunPro\EngineBundle\GCM
 *
 * @TODO: GCM send request in http_kernel.terminated
 */
class GCM
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var GCMDataCollector
     */
    private $collector;

    /**
     * @var String
     */
    private $env;

    /**
     * @var array
     *
     * array (
     *      array ($message, $devices),
     *      array ($message, $devices),
     * )
     */
    private $entries = array();
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Registry         $doctrine
     * @param Serializer       $serializer
     * @param Logger           $logger
     * @param GCMDataCollector $collector
     * @param String           $apiKey
     * @param String           $env
     */
    public function __construct(
        Registry $doctrine,
        Serializer $serializer,
        Logger $logger,
        GCMDataCollector $collector,
        $apiKey,
        $env
    ) {
        $this->doctrine = $doctrine;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
        $this->collector = $collector;
        $this->env = $env;
        $this->logger = $logger;
    }

    /**
     * Add message to queue for sending
     *
     * @param array   $devices
     * @param Message $message
     */
    public function queue(array $devices, Message $message)
    {
        $this->entries[] = array(
            'message' => $message,
            'devices' => $devices
        );

        $messageContext = SerializationContext::create()->setGroups(array('Public', 'GCM'));
        $deviceContext = SerializationContext::create()->setGroups(array('Public', 'Admin'));
        $this->logger->addInfo('add notification to queue', array(
            'message' => $this->serializer->serialize($message, 'json', $messageContext),
            'devices' => $this->serializer->serialize($devices, 'json', $deviceContext)
        ));
    }

    /**
     * send and persist messages in queue
     */
    public function processQueue()
    {
        foreach ($this->entries as $entry) {
            $rawMessage = $entry['message'];
            $devices = $entry['devices'];

            $devices = array_chunk($devices, 1000);
            foreach ($devices as $partialDevices) {
                /** @var Message[] $messages */
                $messages = array();

                /** @var Device $device */
                foreach ($partialDevices as $device) {
                    $persistableMessage = clone $rawMessage;
                    $persistableMessage->setDevice($device);
                    $this->doctrine->getManager()->persist($persistableMessage);
                    if ($device->getStatus() === Device::STATUS_ACTIVE) {
                        $messages[] = $persistableMessage;
                    }
                }

                $this->sendRequest($messages, $rawMessage);

                $this->doctrine->getManager()->flush();
                $this->doctrine->getManager()->clear();
            }
        }
    }

    /**
     * @param array   $messages
     * @param Message $rawMessage
     */
    private function sendRequest(array $messages, Message $rawMessage)
    {
        $registrationIds = array_map(array($this, 'getDeviceToken'), $messages);
        $rawMessage->setRegistrationIds($registrationIds);
        $requestBody = $this->serialize($rawMessage);

        $serializer = $this->serializer;
        $messageContext = SerializationContext::create()->setGroups(array('Public', 'GCM'));
        $this->logger->addInfo(
            'Sending notification to devices',
            array($this->serializer->serialize($rawMessage, 'json', $messageContext))
        );

        $retry = 0;
        do {
            $client = new Curl();
            $browser = new Browser($client);
            $headers = array(
                'Authorization' => 'key=' . $this->apiKey,
                'Content-Type' => 'application/json',
            );

            try {
                $response = $browser->post('https://gcm-http.googleapis.com/gcm/send', $headers, $requestBody);
                $this->logger->addInfo('gcm raw response', array(
                    'content', $response->getContent(),
                    'headers', $response->getHeaders(),
                ));
                $statusCode = $response->getStatusCode();
            } catch (RequestException $e) {
                $this->logger->addError($e->getMessage(), array($serializer->serialize($e->getRequest(), 'json')));
                return;
            } catch (RuntimeException $e) {
                $this->logger->alert($e->getMessage());
                return;
            }

            $retry++;
            if ($statusCode >= 500) {
                usleep(pow(2, $retry));
            }
        } while ($statusCode >= 500 & $retry <= 9);

        if ($statusCode >= 400) {
            $messageContext = SerializationContext::create()->setGroups(array('Public', 'GCM'));
            $this->logger->addError('failed in sending notification', array(
                'message' => $serializer->serialize($rawMessage, 'json', $messageContext),
                'statusCode' => $statusCode,
                'retry' => $retry,
            ));
        } else {
            $messageContext = SerializationContext::create()->setGroups(array('Public', 'GCM'));
            $this->logger->addInfo('gcm response', array(
                'message' => $serializer->serialize($rawMessage, 'json', $messageContext),
                'statusCode' => $statusCode,
                'retry' => $retry,
            ));
        }

        $setStatus = function (Message $message) use ($statusCode) {
            $message->setStatus($statusCode);
        };
        array_map($setStatus, $messages);

        if ($statusCode == 200 or $this->env = 'dev') {
            try {
                $success = $serializer->deserialize($response->getContent(), 'FunPro\EngineBundle\GCM\Success', 'json');
                $this->onSuccess($success, $messages);
            } catch (\JMS\Serializer\Exception\RuntimeException $e) {
                $this->logger->addError('deserialization json error', array('message' => $e->getTraceAsString()));
                return;
            }
        } else {
            $success = null;
        }

        if ($this->env === 'dev') {
            $this->logger->addDebug('add message to data collector');
            $this->collector->add($messages, $success);
        }
    }

    /**
     * @param Success $response
     * @param Message[] $messages
     */
    private function onSuccess(Success $response, array $messages)
    {
        $this->logger->addInfo('Process success response', array('multicast' => $response->getMulticastId()));
        $setMultiCast = function (Message $message) use ($response) {
            $message->setMulticastId($response->getMulticastId());
        };

        array_map($setMultiCast, $messages);

        if ($response->getFailure() == 0 and $response->getCanonicalIds() == 0 and $this->env = 'prod') {
            return;
        }

        $result = $response->getResults();
        $length = count($result);

        for ($i = 0; $i < $length; $i++) {
            if (isset($result[$i]['message_id'])) {
                $messages[$i]->setGcmId($result[$i]['message_id']);

                if (isset($result[$i]['registration_id'])) {
                    $this->logger->addNotice('Device\'s token is updated');
                    $messages[$i]->getDevice()->setDeviceToken($result[$i]['registration_id']);
                }
            } else {
                $this->logger->addWarning('error in gcm', array($result[$i]['error']));
                $messages[$i]->setError($result[$i]['error']);
                if ($result[$i]['error'] === 'NotRegistered' or $result[$i]['error'] === 'InvalidRegistration') {
                    $this->logger->addWarning('deactive device', array('id' => $messages[$i]->getDevice()->getId()));
                    $messages[$i]->getDevice()->setStatus(Device::STATUS_DEACTIVE);
                }
            }
        }
    }

    private function getDeviceToken(Message $message)
    {
        return $message->getDevice()->getDeviceToken();
    }

    private function serialize(Message $message)
    {
        $context = SerializationContext::create()
            ->setGroups('GCM');
        return $this->serializer->serialize($message, 'json', $context);
    }
}
