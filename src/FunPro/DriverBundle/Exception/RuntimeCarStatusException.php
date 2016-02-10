<?php

namespace FunPro\DriverBundle\Exception;

class RuntimeCarStatusException extends \RuntimeException
{
    protected $code = 1;

    public function __construct($message, $file='', $line='')
    {
        $this->message = $message;
        if (!empty($file)) {
            $this->file = $file;
        }
        if (!empty($line)) {
            $this->line = $line;
        }
    }
} 