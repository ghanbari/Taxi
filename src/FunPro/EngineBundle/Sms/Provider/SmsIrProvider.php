<?php

namespace FunPro\EngineBundle\Sms\Provider;

use SmsSender\Exception as Exception;
use SmsSender\HttpAdapter\HttpAdapterInterface;
use SmsSender\Provider\AbstractProvider;
use SmsSender\Result\ResultInterface;

/**
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class SmsIrProvider extends AbstractProvider
{
    /**
     * @var string
     */
    const SEND_SMS_URL = 'http://ip.sms.ir/SendMessage.ashx';

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
    protected $originator;

    /**
     * {@inheritDoc}
     */
    public function __construct(HttpAdapterInterface $adapter, $username, $password, $from, $international_prefix = '+98')
    {
        parent::__construct($adapter);

        $this->username = $username;
        $this->password = $password;
        $this->originator = $from;
        $this->international_prefix = $international_prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function send($recipient, $body, $originator = '')
    {
        if (null === $this->username || null === $this->password) {
            throw new Exception\InvalidCredentialsException('No API credentials provided');
        }

        $originator = $originator ?: $this->originator;

        if (empty($originator)) {
            throw new Exception\InvalidArgumentException('The originator parameter is required for this provider.');
        }

        // clean the originator string to ensure that the sms won't be
        // rejected because of this
        $originator = $this->cleanOriginator($originator);

        $params = $this->getParameters(array(
            'to'    => $this->localNumberToInternational($recipient, $this->international_prefix),
            'text'  => $body,
            'lineNo'  => $originator,
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
        return 'sms_ir';
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
            'user'  => $this->username,
            'pass'  => $this->password,
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
//        $data = json_decode($result, true);
        $sms_data = array();

        // get the status
        $sms_data['status'] = $result == 'Ok'
            ? ResultInterface::STATUS_SENT
            : ResultInterface::STATUS_FAILED;

        $sms_data['message'] = $result;

        return array_merge($sms_data, $this->getDefaults(), $extra_result_data);
    }
}

// vim: set softtabstop=4 tabstop=4 shiftwidth=4 autoindent:
