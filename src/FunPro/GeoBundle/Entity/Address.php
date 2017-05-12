<?php

namespace FunPro\GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;

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
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column()
     *
     * @Assert\NotBlank(groups={"AddressCreate", "AddressUpdate"})
     * @Assert\Length(max="255", groups={"AddressCreate", "AddressUpdate"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $title;

    /**
     * @var Point
     *
     * @ORM\Column(type="point")
     *
     * @Assert\NotBlank(groups={"AddressCreate", "AddressUpdate"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Point"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $point;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", length=10, nullable=true)
     *
     * @Assert\Length(min="10", max="10", groups={"AddressCreate", "AddressUpdate"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\ManyToOne(targetEntity="FunPro\GeoBundle\Entity\City")
     * @ORM\JoinColumn(name="city", referencedColumnName="id", onDelete="RESTRICT", nullable=false)
     *
     * @Assert\Type(type="FunPro\GeoBundle\Entity\City", groups={"AddressCreate", "AddressUpdate"})
     * @Assert\NotNull(groups={"AddressCreate", "AddressUpdate"})
     *
     * @JS\Groups({"City"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank(groups={"AddressCreate", "AddressUpdate"})
     * @Assert\Length(max="2000", groups={"AddressCreate", "AddressUpdate"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="area", type="smallint", nullable=true)
     *
     * @Assert\Type("numeric", groups={"AddressCreate", "AddressUpdate"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $area;

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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Address
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     * Set point
     *
     * @param Point $point
     *
     * @return Address
     */
    public function setPoint(Point $point)
    {
        $this->point = $point;

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
     * Set postalCode
     *
     * @param string $postalCode
     *
     * @return Address
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

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
     * Set address
     *
     * @param string $address
     *
     * @return Address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get city
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set city
     *
     * @param City $city
     *
     * @return Address
     */
    public function setCity(City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param string $area
     *
     * @return $this
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }
}
