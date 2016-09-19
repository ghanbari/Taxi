<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;

/**
 * Currency
 *
 * @ORM\Table(name="currency")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\CurrencyRepository")
 */
class Currency
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
     * @var String
     *
     * @ORM\Column(length=100)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(length=3, unique=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $enable;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="FunPro\FinancialBundle\Entity\Region", mappedBy="currencies")
     *
     * @JS\Groups({"Region"})
     * @JS\Since("1.0.0")
     */
    private $regions;

    /**
     * class constructor
     */
    public function __construct()
    {
        $this->setEnable(true);
        $this->regions = new ArrayCollection();
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
     * @return Currency
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Currency
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * Set enable
     *
     * @param boolean $enable
     *
     * @return Currency
     */
    public function setEnable($enable)
    {
        $this->enable = $enable;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRegions()
    {
        return $this->regions;
    }

    /**
     * @param ArrayCollection $regions
     *
     * @return $this
     */
    public function setRegions(ArrayCollection $regions)
    {
        $this->regions = $regions;
        return $this;
    }
}
