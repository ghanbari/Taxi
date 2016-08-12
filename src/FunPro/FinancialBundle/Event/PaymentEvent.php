<?php

namespace FunPro\FinancialBundle\Event;

use FunPro\FinancialBundle\Entity\Transaction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class PaymentEvent
 *
 * @package FunPro\FinancialBundle\Event
 */
class PaymentEvent extends Event
{
    /**
     * @var Transaction
     */
    private $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
