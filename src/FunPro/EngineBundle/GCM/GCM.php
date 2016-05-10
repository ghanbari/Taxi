<?php

namespace FunPro\EngineBundle\GCM;

use Buzz\Browser;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\UserBundle\Entity\Device;
use FunPro\UserBundle\Entity\Message;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Component\VarDumper\VarDumper;

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
     * @param Registry $doctrine
     * @param Serializer $serializer
     * @param $apiKey
     */
    public function __construct(Registry $doctrine, Serializer $serializer, $apiKey)
    {
        $this->doctrine = $doctrine;
        $this->serializer = $serializer;
        $this->apiKey = $apiKey;
    }

    public function send(array $devices, Message $message)
    {
        $manager = $this->doctrine->getManager();
        $devices = array_chunk($devices, 1000);

        foreach ($devices as $chunkedDevice) {
            $message->setRegistrationIds();
            $messages = array();

            /** @var Device $device */
            foreach ($chunkedDevice as $device) {
                $persistableMessage = clone $message;
                $persistableMessage->setDevice($device);
                $manager->persist($persistableMessage);
                $messages[] = $persistableMessage;
            }

            $registerationIds = array_map(array($this, 'getDeviceToken'), $messages);
            $message->setRegistrationIds($registerationIds);
            $requestBody = $this->serialize($message);

            $retry = 0;
            do {
                $browser = new Browser();
                $headers = array(
                    'Authorization' => 'key=' . $this->apiKey,
                    'Content-Type' => 'application/json',
                );

                $response = $browser->post('https://gcm-http.googleapis.com/gcm/send', $headers, $requestBody);
                $statusCode = $response->getStatusCode();

                $retry++;
                if ($statusCode >= 500) {
                    usleep(pow(2, $retry));
                }
            } while ($statusCode >= 500 & $retry <= 9);

            $setStatus = function (Message $message) use ($statusCode) {
                $message->setStatus($statusCode);
            };
            call_user_func_array($setStatus, $messages);


            if ($statusCode == 200) {
                $resObj = $this->serializer->deserialize($response->getContent(), 'FunPro\EngineBundle\GCM\Success', 'json');
                $this->onSuccess($resObj, $messages);
            }

            $manager->flush();
            $manager->clear();
        }
    }

    /**
     * @param Success $response
     * @param Message[] $messages
     */
    public function onSuccess(Success $response, array $messages)
    {
        $setMultiCast = function (Message $message) use ($response) {
            $message->setMulticastId($response->getMulticastId());
        };

        array_map($setMultiCast, $messages);

        if ($response->getFailure() == 0 and $response->getCanonicalIds() == 0) {
            return;
        }

        $result = $response->getResults();
        $length = count($result);

        for ($i = 0; $i < $length; $i++) {
            if (isset($result[$i]['message_id'])) {
                $messages[$i]->setGcmId($result[$i]['message_id']);

                if (isset($result[$i]['registration_id'])) {
                    $messages[$i]->getDevice()->setDeviceToken($result[$i]['registration_id']);
                }
            } else {
                $messages[$i]->setError($result[$i]['error']);
                if ($result[$i]['error'] == 'NotRegistered') {
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