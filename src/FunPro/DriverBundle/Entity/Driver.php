<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FunPro\AgentBundle\Entity\Agency;
use FunPro\GeoBundle\Entity\Address;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Interfaces\SMSInterface;
use JMS\Serializer\Annotation as JS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Driver
 *
 * @ORM\Table(name="driver")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\DriverRepository")
 *
 * @UniqueEntity("mobile", groups={"Register"})
 */
class Driver extends User implements SMSInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=11, unique=true)
     *
     * @Assert\NotBlank(groups={"Register", "Update"})
     * @Assert\Regex(pattern="/09\d{9}/", groups={"Register", "Update"})
     *
     * @JS\Groups({"DriverMobile", "Profile", "Admin"})
     * @JS\Since("1.0.0")
     */
    protected $mobile;

    /**
     * @var Car
     *
     * @ORM\OneToMany(targetEntity="FunPro\DriverBundle\Entity\Car", mappedBy="driver")
     *
     * @JS\Groups({"Cars"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $cars;

    /**
     * @var Address
     *
     * @ORM\OneToOne(targetEntity="FunPro\GeoBundle\Entity\Address", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="RESTRICT", nullable=false)
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Type(type="FunPro\GeoBundle\Entity\Address", groups={"Register", "Update"})
     * @Assert\Valid()
     *
     * @JS\Groups({"DriverAddress"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="contract_number", length=20, unique=true)
     *
     * @Assert\NotBlank(groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    private $contractNumber;

    /**
     * @var Agency
     *
     * @ORM\ManyToOne(targetEntity="FunPro\AgentBundle\Entity\Agency")
     * @ORM\JoinColumn(name="agency_id", referencedColumnName="id", onDelete="restrict", nullable=false)
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Type(type="FunPro\AgentBundle\Entity\Agency", groups={"Register", "Update"})
     *
     * @JS\Groups({"Agency"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $agency;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="national_code", unique=true)
     *
     * @Assert\NotBlank(groups={"Register", "Update"})
     * @Assert\Regex(pattern="/\d{10}/", groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    private $nationalCode;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=2, scale=1, options={"default"=0})
     *
     * @JS\Groups({"Public", "Vote", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $rate;

    public function __construct()
    {
        parent::__construct();
        $this->setEnabled(true);
        $this->contact = array();
        $this->cars = new ArrayCollection();
        $this->addRole(self::ROLE_DRIVER);
        $this->rate = 0;
        $this->setMultiDeviceAllowed(false);
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return Driver
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
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
        if (is_null($this->username)) {
            $this->setUsername($nationalCode);
        }

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
     * @param Car $cars
     * @return Driver
     */
    public function addCar(Car $cars)
    {
        $this->cars[] = $cars;

        return $this;
    }

    /**
     * Remove cars
     *
     * @param Car $cars
     */
    public function removeCar(Car $cars)
    {
        $this->cars->removeElement($cars);
    }

    /**
     * Get cars
     *
     * @return ArrayCollection
     */
    public function getCars()
    {
        return $this->cars;
    }

    /**
     * Set address
     *
     * @param Address $address
     * @return Driver
     */
    public function setAddress(Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set agency
     *
     * @param Agency $agency
     * @return Driver
     */
    public function setAgency(Agency $agency)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }
}
