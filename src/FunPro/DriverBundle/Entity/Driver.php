<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FunPro\AgentBundle\Entity\Agency;
use FunPro\UserBundle\Entity\User;

/**
 * Driver
 *
 * @ORM\Table(name="driver")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\DriverRepository")
 */
class Driver extends User
{
    /**
     * @var Car
     *
     * @ORM\OneToMany(targetEntity="FunPro\DriverBundle\Entity\Car", mappedBy="driver")
     */
    private $cars;

    /**
     * @var array
     *
     * @ORM\OneToOne(targetEntity="FunPro\GeoBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="RESTRICT")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_number", length=20, unique=true)
     */
    private $contractNumber;

    /**
     * @var Agency
     *
     * @ORM\ManyToOne(targetEntity="FunPro\AgentBundle\Entity\Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id", onDelete="restrict", nullable=false)
     */
    private $agency;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="national_code", unique=true)
     */
    private $nationalCode;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=2, scale=1, options={"default"=0})
     */
    private $rate;

    public function __construct()
    {
        parent::__construct();
        $this->contact = array();
        $this->cars = new ArrayCollection();
        $this->rate = 0;
    }

    /**
     * Set contractNumber
     *
     * @param string $contractNumber
     * @return Driver
     */
    public function setContractNumber($contractNumber)
    {
        $this->contractNumber = $contractNumber;

        return $this;
    }

    /**
     * Get contractNumber
     *
     * @return string 
     */
    public function getContractNumber()
    {
        return $this->contractNumber;
    }

    /**
     * Set contact
     *
     * @param array $contact
     * @return Driver
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return array 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set nationalCode
     *
     * @param string $nationalCode
     * @return Driver
     */
    public function setNationalCode($nationalCode)
    {
        $this->nationalCode = $nationalCode;

        return $this;
    }

    /**
     * Get nationalCode
     *
     * @return string 
     */
    public function getNationalCode()
    {
        return $this->nationalCode;
    }

    /**
     * Set rate
     *
     * @param string $rate
     * @return Driver
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string 
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Add cars
     *
     * @param \FunPro\DriverBundle\Entity\Car $cars
     * @return Driver
     */
    public function addCar(\FunPro\DriverBundle\Entity\Car $cars)
    {
        $this->cars[] = $cars;

        return $this;
    }

    /**
     * Remove cars
     *
     * @param \FunPro\DriverBundle\Entity\Car $cars
     */
    public function removeCar(\FunPro\DriverBundle\Entity\Car $cars)
    {
        $this->cars->removeElement($cars);
    }

    /**
     * Get cars
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCars()
    {
        return $this->cars;
    }

    /**
     * Set address
     *
     * @param \FunPro\GeoBundle\Entity\Address $address
     * @return Driver
     */
    public function setAddress(\FunPro\GeoBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \FunPro\GeoBundle\Entity\Address 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set agency
     *
     * @param \FunPro\AgentBundle\Entity\Agency $agency
     * @return Driver
     */
    public function setAgency(\FunPro\AgentBundle\Entity\Agency $agency)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \FunPro\AgentBundle\Entity\Agency 
     */
    public function getAgency()
    {
        return $this->agency;
    }
}
