<?php

namespace FunPro\GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity(repositoryClass="FunPro\GeoBundle\Repository\AddressRepository")
 */
class Address
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
     * @var string
     *
     * @ORM\Column()
     */
    private $title;

    /**
     * @var Point
     *
     * @ORM\Column(type="point")
     */
    private $point;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", length=10, nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="FunPro\GeoBundle\Entity\City")
     * @ORM\JoinColumn(name="city", referencedColumnName="id", onDelete="RESTRICT", nullable=false)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $address;

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
     * Set title
     *
     * @param string $title
     * @return Address
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set point
     *
     * @param Point $point
     * @return Address
     */
    public function setPoint(Point $point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return Point
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     * @return Address
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string 
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set city
     *
     * @param \FunPro\GeoBundle\Entity\City $city
     * @return Address
     */
    public function setCity(\FunPro\GeoBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \FunPro\GeoBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }
}
