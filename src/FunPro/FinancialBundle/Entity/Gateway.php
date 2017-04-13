<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;

/**
 * Gateway
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\GatewayRepository")
 */
class Gateway
{
    /**
     * @var integer
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
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     *
     * @JS\Groups({"Private"})
     * @JS\Since("1.0.0")
     */
    private $config;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     *
     * @JS\Groups({"Private"})
     */
    private $enable;

    /**
     * @var Currency
     *
     * @deprecated
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Currency")
     *
     * @JS\Groups({"Currency"})
     * @JS\Since("1.0.0")
     */
    private $currency;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default", type="boolean", options={"default"=false})
     *
     * @JS\Groups({"Private"})
     * @JS\Since("1.0.0")
     */
    private $default;

    /**
     * @param       $name
     * @param array $config
     * @param bool  $default
     */
    public function __construct($name, array $config, $default = false)
    {
        $this->setName($name);
        $this->setConfig($config);
        $this->setEnable(true);
        $this->setDefault($default);
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Gateway
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get config
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set config
     *
     * @param array $config
     *
     * @return Gateway
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get enable
     *
     * @return boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * Get enable
     *
     * @return boolean
     */
    public function isEnable()
    {
        return $this->enable;
    }

    /**
     * Set enable
     *
     * @param boolean $enable
     *
     * @return Gateway
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get default
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Set default
     *
     * @param boolean $default
     *
     * @return Gateway
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Get currency
     *
     * @deprecated
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @deprecated
     * @param Currency $currency
     *
     * @return Gateway
     */
    public function setCurrency(Currency $currency = null)
    {
        $this->currency = $currency;

        return $this;
    }
}
