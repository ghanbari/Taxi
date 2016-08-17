<?php

namespace FunPro\FinancialBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Exception\InvalidTransactionException;
use FunPro\FinancialBundle\FinancialEvents;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PaymentSubsciber
 *
 * @package FunPro\FinancialBundle\Event
 */
class PaymentSubsciber implements EventSubscriberInterface
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Registry              $doctrine
     * @param Logger                $logger
     * @param ParameterBagInterface $parameterBag
     * @param ValidatorInterface    $validator
     * @param Serializer            $serializer
     */
    public function __construct(
        Registry $doctrine,
        Logger $logger,
        ParameterBagInterface $parameterBag,
        ValidatorInterface $validator,
        Serializer $serializer
    ) {
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return array(
            FinancialEvents::PAYMENT_EVENT => array(
                array('insertDriverWage', 140),
                array('insertDriverCommission', 130),
            ),
        );
    }

    public function insertDriverWage(PaymentEvent $event)
    {
        $main = $event->getTransaction();
        $driver = $main->getService()->getCar()->getDriver();

        $transaction = new Transaction(
            $driver,
            $main->getCurrency(),
            $main->getAmount(),
            Transaction::TYPE_WAGE,
            $main->isVirtual()
        );

        if ($main->isVirtual()) {
            $wallet = $this->doctrine->getRepository('FunProFinancialBundle:Wallet')
                ->getUserWallet($driver, $main->getCurrency());
            $transaction->setWallet($wallet);
        }
        $transaction->setService($main->getService());
        $transaction->setStatus(Transaction::STATUS_SUCCESS);

        $errors = $this->validator->validate($transaction, null, array('Create', 'Wage'));
        if (count($errors)) {
            $transactionContext = SerializationContext::create()
                ->setGroups(array('Admin', 'User', 'Service', 'Currency', 'CurrencyLog', 'Wallet', 'Gateway'));
            $this->logger->addError(
                'transaction is not valid',
                array(
                    'errors' => $this->serializer->serialize($errors, 'json'),
                    'transaction' => $this->serializer->serialize($transaction, 'json', $transactionContext)
                )
            );
            throw new InvalidTransactionException('transaction is not valid');
        }

        $this->doctrine->getManager()->persist($transaction);
        $this->logger->addInfo('Wage transaction is persisted');
    }

    public function insertDriverCommission(PaymentEvent $event)
    {
        $main = $event->getTransaction();
        $parameters = $this->parameterBag;
        $driver = $main->getService()->getCar()->getDriver();
        $commissionRate = $main->isVirtual() ?
            $parameters->get('financial.commission.payment.credit') : $parameters->get('financial.commission.payment.cash');

        $transaction = new Transaction(
            $driver,
            $main->getCurrency(),
            $main->getAmount() * $commissionRate / 100,
            Transaction::TYPE_COMMISSION,
            true
        );

        $wallet = $this->doctrine->getRepository('FunProFinancialBundle:Wallet')
            ->getUserWallet($driver, $main->getCurrency());
        $transaction->setWallet($wallet);
        $transaction->setService($main->getService());
        $transaction->setStatus(Transaction::STATUS_SUCCESS);

        $errors = $this->validator->validate($transaction, null, array('Create', 'Commission'));
        if (count($errors)) {
            $transactionContext = SerializationContext::create()
                ->setGroups(array('Admin', 'User', 'Service', 'Currency', 'CurrencyLog', 'Wallet', 'Gateway'));
            $this->logger->addError(
                'transaction is not valid',
                array(
                    'errors' => $this->serializer->serialize($errors, 'json'),
                    'transaction' => $this->serializer->serialize($transaction, 'json', $transactionContext)
                )
            );
            throw new InvalidTransactionException('transaction is not valid');
        }

        $this->doctrine->getManager()->persist($transaction);
        $this->logger->addInfo('Commission transaction is persisted');
    }
}
