<?php

namespace FunPro\DriverBundle\Exception;

/**
 * Exception raise when car have not a correct status.
 *
 * Class CarStatusException
 *
 * @package FunPro\DriverBundle\Exception
 */
class CarStatusException extends \UnexpectedValueException
{
    protected $code = -1;

    /**
     * @inheritdoc
     *
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message, 400);
    }
}
