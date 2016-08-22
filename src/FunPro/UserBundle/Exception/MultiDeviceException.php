<?php

namespace FunPro\UserBundle\Exception;

use Buzz\Exception\LogicException;
use Exception;

/**
 * Class MultiDeviceException
 *
 * @package FunPro\UserBundle\Exception
 */
class MultiDeviceException extends LogicException
{
    public function __construct($message = "User can not have multi device", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
