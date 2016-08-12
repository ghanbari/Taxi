<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RegionBasePrice
 *
 * @ORM\Table(
 *      name="region_base_price",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="currency_in_region_UNIQUE", columns={"currency_id", "region_id"})}
 * )
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\RegionBasePriceRepository")
 */
class RegionBasePrice
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Region")
     * @ORM\JoinColumn(name="region_id", referencedColumnName="id")
     */
    private $region;

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * @param Region   $region
     * @param Currency $currency
     * @param null     $price
     */
    public function __construct(Region $region = null, Currency $currency = null, $price = null)
    {
        $this->currency = $currency;
        $this->price = $price;
        $this->region = $region;
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
     * Set price
     *
     * @param integer $price
     * @return RegionBasePrice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set currency
     *
     * @param Currency $currency
     * @return RegionBasePrice
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

    /**
     * Set region
     *
     * @param Region $region
     * @return RegionBasePrice
     */
    public function setRegion(Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }
}
