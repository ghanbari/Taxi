<?php

namespace FunPro\EngineBundle\Messaging\Provider;

use SmsSender\HttpAdapter\HttpAdapterInterface;
use SmsSender\Provider\AbstractProvider;
use SmsSender\Exception as Exception;
use SmsSender\Result\ResultInterface;

class RayganSmsProvider extends AbstractProvider
{
    /**
     * @var string
     */
    const SEND_SMS_URL = 'http://RayganSMS.com/SendMessageWithPost.ashx';

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $international_prefix;

    /**
     * @var string
     */
    protected $from;

    /**
     * {@inheritDoc}
     */
    public function __construct(HttpAdapterInterface $adapter, array $config)
    {
        parent::__construct($adapter);

        $this->username = $config['providers']['raygan_sms']['username'];
        $this->password = $config['providers']['raygan_sms']['password'];
        $this->from     = $config['providers']['raygan_sms']['from'];
        $this->international_prefix = $config['prefix'];
    }

    /**
     * {@inheritDoc}
     */
    public function send($recipient, $body, $originator = '')
    {
        if (null === $this->username || null === $this->password) {
            throw new Exception\InvalidCredentialsException('No API credentials provided');
        }

        if (empty($originator) and empty($this->from)) {
            throw new Exception\InvalidArgumentException('The originator parameter is required for this provider.');
        }

        $originator = !empty($originator) ? $originator : $this->from;
        $originator = $this->cleanOriginator($originator);

        $params = $this->getParameters(array(
            'RecNumber'    => $this->localNumberToInternational($recipient, $this->international_prefix),
            'MessageBody'  => $body,
            'PhoneNumber'  => $originator,
        ));

        return $this->executeQuery(self::SEND_SMS_URL, $params, array(
            'recipient'  => $recipient,
            'body'       => $body,
            'originator' => $originator,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'raygan_sms';
    }

    /**
     * @param  string $query
     * @return array
     */
    protected function executeQuery($url, array $data = array(), array $extra_result_data = array())
    {
        $content = $this->getAdapter()->getContent($url, 'POST', $headers = array(), $data);

        if (null === $content) {
            return array_merge($this->getDefaults(), $extra_result_data);
        }

        return $this->parseResults($content, $extra_result_data);
    }

    /**
     * Builds the parameters list to send to the API.
     *
     * @return array
     * @author Kevin Gomez <contact@kevingomez.fr>
     */
    public function getParameters(array $additionnal_parameters = array())
    {
        return array_merge(array(
            'UserName'  => $this->username,
            'Password'  => $this->password,
            'Smsclass'  => 1,
        ), $additionnal_parameters);
    }

    /**
     * Parse the data returned by the API.
     *
     * @param  string $result The raw result string.
     * @return array
     */
    protected function parseResults($result, array $extra_result_data = array())
    {
        $sms_data = array();

        // get the status
        $sms_data['status'] = $result === '0'
            ? ResultInterface::STATUS_SENT
            : ResultInterface::STATUS_FAILED;

        return array_merge($this->getDefaults(), $extra_result_data, $sms_data);
    }
}