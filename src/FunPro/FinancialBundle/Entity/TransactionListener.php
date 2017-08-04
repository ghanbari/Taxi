<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use FunPro\FinancialBundle\Exception\LowBalanceException;
use FunPro\PassengerBundle\Entity\Passenger;

class TransactionListener
{
    public function prePersist(Transaction $transaction, LifecycleEventArgs $args)
    {
        $user = $transaction->getUser();

        if (!$transaction->isVirtual()) {
            return;
        }

        if ($user instanceof Passenger
            and $transaction->getDirection() === Transaction::DIRECTION_OUTCOME
            and $transaction->getAmount() > $user->getCredit()
        ) {
            throw new LowBalanceException('Your wallet balance is not enough');
        }

        $user->setCredit($user->getCredit() + ($transaction->getAmount() * $transaction->getDirection()));
    }
}