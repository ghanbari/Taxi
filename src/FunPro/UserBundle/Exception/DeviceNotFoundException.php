<?php

namespace FunPro\UserBundle\Exception;
use Exception;

/**
 * Class DeviceNotFoundException
 *
 * @package FunPro\UserBundle\Exception
 */
class DeviceNotFoundException extends \RuntimeException
{
    public function __construct($message = "Device is not exists", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
