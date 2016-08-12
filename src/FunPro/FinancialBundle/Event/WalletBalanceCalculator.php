<?php

namespace FunPro\FinancialBundle\Event;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Exception\LowBalanceException;
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
        $entity = $event->getObject();

        if (!$entity instanceof Transaction) {
            return;
        }

        if (!$entity->isVirtual()) {
            return;
        }

        $wallet = $entity->getWallet();
        if ($entity->getType() === Transaction::DIRECTION_OUTCOME and $entity->getAmount() > $wallet->getBalance()) {
            $this->logger->addError(
                'Your wallet balance is not enough',
                array(
                    'cost' => $entity->getAmount(),
                    'balance' => $wallet->getBalance(),
                )
            );
            throw new LowBalanceException('Your wallet balance is not enough');
        }

        $wallet->setBalance($wallet->getBalance() + ($entity->getAmount() * $entity->getDirection()));
    }

    #TODO: implement this method
    public function preUpdate(PreUpdateEventArgs $event)
    {
    }
}
