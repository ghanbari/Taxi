<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CurrencyExchangeLog
 *
 * @ORM\Table(name="currency_exchange_log")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\CurrencyExchangeLogRepository")
 */
class CurrencyExchangeLog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=14, scale=5)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $exchange;

    /**
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     *
     * @JS\Groups({"Currency"})
     * @JS\Since("1.0.0")
     */
    private $currency;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

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
     * Set exchange
     *
     * @param string $exchange
     * @return CurrencyExchangeLog
     */
    public function setExchange($exchange)
    {
        $this->exchange = $exchange;

        return $this;
    }

    /**
     * Get exchange
     *
     * @return string
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CurrencyExchangeLog
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set currency
     *
     * @param Currency $currency
     * @return CurrencyExchangeLog
     */
    public function setCurrency(Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
