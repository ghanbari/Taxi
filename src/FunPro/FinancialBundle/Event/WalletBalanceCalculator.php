<?php

namespace FunPro\FinancialBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Exception\LowBalanceException;
use FunPro\PassengerBundle\Entity\Passenger;
use Symfony\Bridge\Monolog\Logger;

/**
 * {@inheritDoc}
 */
class WalletBalanceCalculator implements EventSubscriber
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist'
        );
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $transaction = $event->getObject();

        if (!$transaction instanceof Transaction) {
            return;
        }

        $user = $transaction->getUser();
        if (!$transaction->isVirtual()) {
            return;
        }

        if ($user instanceof Passenger
            and $transaction->getType() === Transaction::DIRECTION_OUTCOME
            and $transaction->getAmount() > $user->getCredit()
        ) {
            $this->logger->addError(
                'Your wallet balance is not enough',
                array(
                    'cost' => $transaction->getAmount(),
                    'balance' => $user->getCredit(),
                )
            );
            throw new LowBalanceException('Your wallet balance is not enough');
        }

        $user->setCredit($user->getCredit() + ($transaction->getAmount() * $transaction->getDirection()));
    }

    #TODO: implement this method
    public function preUpdate(PreUpdateEventArgs $event)
    {
    }
}
