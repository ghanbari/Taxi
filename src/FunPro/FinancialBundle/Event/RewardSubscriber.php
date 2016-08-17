<?php

namespace FunPro\FinancialBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FunPro\FinancialBundle\Entity\Transaction;
use FunPro\FinancialBundle\Exception\InvalidTransactionException;
use FunPro\FinancialBundle\FinancialEvents;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\UserBundle\Event\RegisterEvent;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class RewardSubscriber
 *
 * @package FunPro\FinancialBundle\Event
 */
class RewardSubscriber implements  EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return array(
            FinancialEvents::PAYMENT_EVENT => array(
                array('paymentReward', 200),
            ),
            FinancialEvents::REWARD_REFERRER => array(
                array('refererReward', 200),
            ),
        );
    }

    public function paymentReward(PaymentEvent $event)
    {
        $main = $event->getTransaction();
        $parameters = $this->parameterBag;
        $rewardPercent = $main->isVirtual() ?
            $parameters->get('financial.reward.payment.credit') : $parameters->get('financial.reward.payment.cash');

        $transaction = new Transaction(
            $main->getUser(),
            $main->getCurrency(),
            $main->getAmount() * $rewardPercent / 100,
            Transaction::TYPE_REWARD,
            true
        );

        $wallet = $this->doctrine->getRepository('FunProFinancialBundle:Wallet')
            ->getUserWallet($main->getUser(), $main->getCurrency());
        $transaction->setWallet($wallet);
        $transaction->setService($main->getService());
        $transaction->setStatus(Transaction::STATUS_SUCCESS);

        $errors = $this->validator->validate($transaction, null, array('Create', 'Pay', 'Reward'));
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
        $this->logger->addInfo('Reward transaction is persisted');
    }

    public function refererReward(RegisterEvent $event)
    {
        $user = $event->getUser();
        $userReferer = $user->getReferrer();

        $parameters = $this->parameterBag;
        $reward = $parameters->get('financial.reward.referer');
        $currency = $this->doctrine->getRepository('FunProFinancialBundle:Currency')
            ->findOneByCode($parameters->get('financial.reward.referer.default_currency'));

        $wallet = $this->doctrine->getRepository('FunProFinancialBundle:Wallet')
            ->getUserWallet($userReferer, $currency);

        $transaction = new Transaction(
            $userReferer,
            $currency,
            $reward,
            Transaction::TYPE_REWARD,
            true
        );

        $transaction->setWallet($wallet);
        $transaction->setStatus(Transaction::STATUS_SUCCESS);

        $errors = $this->validator->validate($transaction, null, array('Create', 'Reward'));
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
        $this->logger->addInfo('Reward transaction is persisted');
    }

    public function increaseCreditReward()
    {

    }
}
