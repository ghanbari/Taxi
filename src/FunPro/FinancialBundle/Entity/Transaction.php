<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\TransactionRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Transaction
{
    const DIRECTION_INCOME  = 1;
    const DIRECTION_OUTCOME = -1;

    const TYPE_PAY          = 1;
    const TYPE_WAGE         = 2;
    const TYPE_COMMISSION   = 3;
    const TYPE_CREDIT       = 4;
    const TYPE_WITHDRAW     = 5;
    const TYPE_MOVE         = 6;
    const TYPE_REWARD       = 7;

    const STATUS_SUCCESS = 1;
    const STATUS_FAILED  = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type(type="FunPro\UserBundle\Entity\User", groups={"Create", "Update"})
     *
     * @JS\Groups({"User"})
     * @JS\Since("1.0.0")
     */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Range(min="1", max="99999999", groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $amount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_virtual", type="boolean", options={"default"=false})
     *
     * @Assert\Type("boolean", groups={"Create", "Update"})
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\True(groups={"Reward", "Commission", "Credit", "Withdraw", "Move"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $virtual;

    /**
     * @var integer
     *
     * @ORM\Column(name="direction", type="smallint")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Choice(callback="getValidDirection", groups={"Create", "Update"})
     * @Assert\Expression(expression="this.isValidDirection()", groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $direction;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Choice(callback="getValidType", groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $type;

    /**
     * @var Service
     *
     * @ORM\ManyToOne(targetEntity="FunPro\ServiceBundle\Entity\Service")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @Assert\NotNull(groups={"Pay", "Wage", "Commission"})
     * @Assert\Type(type="FunPro\ServiceBundle\Entity\Service", groups={"Create", "Update"})
     *
     * @JS\Groups({"Service"})
     * @JS\Type(name="FunPro\ServiceBundle\Entity\Service")
     * @JS\Since("1.0.0")
     */
    private $service;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @Assert\NotNull(groups={"Credit", "Withdraw"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $status;

    /**
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type(type="FunPro\FinancialBundle\Entity\Currency", groups={"Create", "Update"})
     *
     * @JS\Groups({"Currency"})
     * @JS\Since("1.0.0")
     */
    private $currency;

    /**
     * Only when user will move credit from one wallet to another wallet
     *
     * @var CurrencyExchangeLog
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\CurrencyExchangeLog")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\NotNull(groups={"Move"})
     * @Assert\Type("FunPro\FinancialBundle\Entity\CurrencyExchangeLog", groups={"Create", "Update"})
     *
     * @JS\Groups({"CurrencyLog"})
     * @JS\Since("1.0.0")
     */
    private $currencyLog;

    /**
     * Required if transaction is virtual
     *
     * @var Wallet
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Wallet")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id")
     *
     * @Assert\Expression(expression="!this.isVirtual() or value !== null", groups={"Create", "Update"})
     *
     * @JS\Groups({"Wallet"})
     * @JS\Since("1.0.0")
     */
    private $wallet;

    /**
     * @var Gateway
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Gateway")
     * @ORM\JoinColumn(name="gateway_id", referencedColumnName="id")
     *
     * @Assert\NotNull(groups={"Withdraw", "Credit"})
     * @Assert\Type(type="FunPro\FinancialBundle\Entity\Gateway", groups={"Create", "Update"})
     *
     * @JS\Groups({"Gateway"})
     * @JS\Since("1.0.0")
     */
    private $gateWay;

    /**
     * @var Wallet
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Wallet")
     * @ORM\JoinColumn(name="moved_wallet_id", referencedColumnName="id")
     *
     * @Assert\NotNull(groups={"Move"})
     * @Assert\Type(type="FunPro\FinancialBundle\Entity\Wallet", groups={"Create", "Update"})
     *
     * @JS\Groups({"Wallet"})
     * @JS\Since("1.0.0")
     */
    private $moveToWallet;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @param User     $user
     * @param Currency $currency
     * @param integer  $amount
     * @param integer  $type
     * @param bool     $virtual
     * @param Wallet   $wallet
     */
    public function __construct(User $user, Currency $currency, $amount, $type, $virtual, Wallet $wallet = null)
    {
        $this->user = $user;
        $this->amount = $amount;
        $this->virtual = $virtual;
        $this->type = $type;
        $this->wallet = $wallet;
        $this->currency = $currency;

        if (in_array($type, array(self::TYPE_WAGE, self::TYPE_REWARD, self::TYPE_CREDIT))) {
            $this->direction = self::DIRECTION_INCOME;
        } else {
            $this->direction = self::DIRECTION_OUTCOME;
        }
    }

    /**
     * @return array
     */
    public static function getValidDirection()
    {
        return array(
            self::DIRECTION_OUTCOME,
            self::DIRECTION_INCOME,
        );
    }

    /**
     * @return bool
     */
    public function isValidDirection()
    {
        if (in_array($this->type, array(self::TYPE_WAGE, self::TYPE_REWARD, self::TYPE_CREDIT))) {
            $direction = self::DIRECTION_INCOME;
        } else {
            $direction = self::DIRECTION_OUTCOME;
        }

        return $this->getDirection() === $direction;
    }

    public static function getValidType()
    {
        return array(
            self::TYPE_PAY,
            self::TYPE_WAGE,
            self::TYPE_REWARD,
            self::TYPE_COMMISSION,
            self::TYPE_CREDIT,
            self::TYPE_WITHDRAW,
            self::TYPE_MOVE,
        );
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set virtual
     *
     * @param boolean $virtual
     * @return Transaction
     */
    public function setVirtual($virtual)
    {
        $this->virtual = $virtual;

        return $this;
    }

    /**
     * Get virtual
     *
     * @return boolean
     */
    public function getVirtual()
    {
        return $this->virtual;
    }

    /**
     * Get virtual
     *
     * @return boolean
     */
    public function isVirtual()
    {
        return $this->virtual;
    }

    /**
     * Set direction
     *
     * @param integer $direction
     * @return Transaction
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get direction
     *
     * @return integer
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Transaction
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param User $user
     * @return Transaction
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set service
     *
     * @param Service $service
     * @return Transaction
     */
    public function setService(Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     *
     * @return $this
     */
    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set currencyLog
     *
     * @param CurrencyExchangeLog $currencyLog
     * @return Transaction
     */
    public function setCurrencyLog(CurrencyExchangeLog $currencyLog = null)
    {
        $this->currencyLog = $currencyLog;

        return $this;
    }

    /**
     * Get currencyLog
     *
     * @return CurrencyExchangeLog
     */
    public function getCurrencyLog()
    {
        return $this->currencyLog;
    }

    /**
     * Set wallet
     *
     * @param Wallet $wallet
     * @return Transaction
     */
    public function setWallet(Wallet $wallet = null)
    {
        $this->wallet = $wallet;

        return $this;
    }

    /**
     * Get wallet
     *
     * @return Wallet
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * Set gateWay
     *
     * @param Gateway $gateWay
     * @return Transaction
     */
    public function setGateWay(Gateway $gateWay = null)
    {
        $this->gateWay = $gateWay;

        return $this;
    }

    /**
     * Get gateWay
     *
     * @return Gateway
     */
    public function getGateWay()
    {
        return $this->gateWay;
    }

    /**
     * Set moveToWallet
     *
     * @param Wallet $moveToWallet
     * @return Transaction
     */
    public function setMoveToWallet(Wallet $moveToWallet = null)
    {
        $this->moveToWallet = $moveToWallet;

        return $this;
    }

    /**
     * Get moveToWallet
     *
     * @return Wallet
     */
    public function getMoveToWallet()
    {
        return $this->moveToWallet;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param mixed $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(mixed $deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }
}
