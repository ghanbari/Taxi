<?php

namespace FunPro\ServiceBundle\Exception;

use Exception;

/**
 * Exception raise when service status is not expected
 *
 * Class ServiceStatusException
 *
 * @package FunPro\ServiceBundle\Exception
 */
class ServiceStatusException extends \UnexpectedValueException
{
    /**
     * @param string    $message
     * @param int       $code
     * @param Exception $previous
     */
    public function __construct($message = "service status is invalid", $code = 400, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
