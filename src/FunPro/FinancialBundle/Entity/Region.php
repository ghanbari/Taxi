<?php

namespace FunPro\FinancialBundle\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Region
 *
 * @ORM\Table(name="region")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\RegionRepository")
 */
class Region
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
     * @var Polygon
     *
     * @ORM\Column(name="region", type="polygon")
     */
    private $region;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="FunPro\FinancialBundle\Entity\Currency", inversedBy="regions")
     * @ORM\JoinTable(
     *      name="region_currency",
     *      joinColumns={@ORM\JoinColumn(name="region_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="currency_id", referencedColumnName="id")}
     * )
     */
    private $currencies;

    public function __construct()
    {
        $this->currencies = new ArrayCollection();
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
     * @return Polygon
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param Polygon $region
     *
     * @return $this
     */
    public function setRegion(Polygon $region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * Add currencies
     *
     * @param Currency $currencies
     * @return Region
     */
    public function addCurrency(Currency $currencies)
    {
        $this->currencies[] = $currencies;

        return $this;
    }

    /**
     * Remove currencies
     *
     * @param Currency $currencies
     */
    public function removeCurrency(Currency $currencies)
    {
        $this->currencies->removeElement($currencies);
    }

    /**
     * Get currencies
     *
     * @return ArrayCollection
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }
}
