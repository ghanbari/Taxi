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
 * @UniqueEntity("mobile", groups={"Register", "Update"})
 * @UniqueEntity("contractNumber", groups={"Register", "Update"})
 * @UniqueEntity("nationalCode", groups={"Register", "Update"})
 */
class Driver extends User implements SMSInterface
{
    const EDUCATION_UNDER_DIPLOMA = 0;
    const EDUCATION_ASSOCIATE_DEGREE = 1;
    const EDUCATION_BACHELOR = 2;
    const EDUCATION_MASTER_DEGREE = 3;

    const COD_END = 0;
    const COD_EXEMPTION = 1;
    const COD_EDUCATION_EXEMPTION = 2;

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
     * @var string
     *
     * @ORM\Column(name="parent_name", length=50)
     *
     * @Assert\NotBlank(groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $parentName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="born", type="date", options={"default"="2017/01/01"})
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Date(groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $born;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\Choice(callback="getAvailableEducations", groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $education;

    /**
     * @var integer call of duty status
     *
     * @ORM\Column(name="cod_status", type="smallint")
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Choice(callback="getAvailableCodStatus", groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $codStatus;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_marriage")
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Type(type="numeric", groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $marriage;

    /**
     * @var string
     *
     * @ORM\Column(name="sheba_number", length=20)
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Length(max="20", groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $shebaNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_activity", type="date")
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Date(groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $startActivity;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_activity", type="date")
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Date(groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $endActivity;

    /**
     * @var boolean
     *
     * @ORM\Column(name="learning_course", type="boolean")
     *
     * @Assert\NotNull(groups={"Register", "Update"})
     * @Assert\Type(type="numeric", groups={"Register", "Update"})
     *
     * @JS\Groups({"Owner", "Admin", "Register"})
     * @JS\Since("1.0.0")
     */
    protected $learningCourse;

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
        $this->setLearningCourse(false);
        $this->setMarriage(true);
        #TODO: set end activity time as expire date
    }

    /**
     * @return array
     */
    public static function getAvailableEducations()
    {
        return array(
            self::EDUCATION_UNDER_DIPLOMA,
            self::EDUCATION_ASSOCIATE_DEGREE,
            self::EDUCATION_BACHELOR,
            self::EDUCATION_MASTER_DEGREE,
        );
    }

    /**
     * return array
     */
    public static function getAvailableCodStatus()
    {
        return array(
            self::COD_END,
            self::COD_EXEMPTION,
            self::COD_EDUCATION_EXEMPTION,
        );
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
     * Set mobile
     *
     * @param string $mobile
     *
     * @return Driver
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

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
     * Set contractNumber
     *
     * @param string $contractNumber
     *
     * @return Driver
     */
    public function setContractNumber($contractNumber)
    {
        $this->contractNumber = $contractNumber;

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
     * Set contact
     *
     * @param array $contact
     *
     * @return Driver
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

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
     * Set nationalCode
     *
     * @param string $nationalCode
     *
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
     * Get rate
     *
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set rate
     *
     * @param string $rate
     *
     * @return Driver
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Add cars
     *
     * @param Car $cars
     *
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
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param Address $address
     *
     * @return Driver
     */
    public function setAddress(Address $address = null)
    {
        $this->address = $address;

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

    /**
     * Set agency
     *
     * @param Agency $agency
     *
     * @return Driver
     */
    public function setAgency(Agency $agency)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getBorn()
    {
        return $this->born;
    }

    /**
     * @param \DateTime $born
     *
     * @return $this
     */
    public function setBorn(\DateTime $born)
    {
        $this->born = $born;
        return $this;
    }

    /**
     * @return int
     */
    public function getCodStatus()
    {
        return $this->codStatus;
    }

    /**
     * @param int $codStatus
     *
     * @return $this
     */
    public function setCodStatus($codStatus)
    {
        $this->codStatus = $codStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getEducation()
    {
        return $this->education;
    }

    /**
     * @param int $education
     *
     * @return $this
     */
    public function setEducation($education)
    {
        $this->education = $education;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndActivity()
    {
        return $this->endActivity;
    }

    /**
     * @param \DateTime $endActivity
     *
     * @return $this
     */
    public function setEndActivity(\DateTime $endActivity)
    {
        $this->endActivity = $endActivity;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isLearningCourse()
    {
        return $this->learningCourse;
    }

    /**
     * @param boolean $learningCourse
     *
     * @return $this
     */
    public function setLearningCourse($learningCourse)
    {
        $this->learningCourse = $learningCourse;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isMarriage()
    {
        return $this->marriage;
    }

    /**
     * @param boolean $marriage
     *
     * @return $this
     */
    public function setMarriage($marriage)
    {
        $this->marriage = $marriage;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentName()
    {
        return $this->parentName;
    }

    /**
     * @param string $parentName
     *
     * @return $this
     */
    public function setParentName($parentName)
    {
        $this->parentName = $parentName;
        return $this;
    }

    /**
     * @return string
     */
    public function getShebaNumber()
    {
        return $this->shebaNumber;
    }

    /**
     * @param string $shebaNumber
     *
     * @return $this
     */
    public function setShebaNumber($shebaNumber)
    {
        $this->shebaNumber = $shebaNumber;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartActivity()
    {
        return $this->startActivity;
    }

    /**
     * @param \DateTime $startActivity
     *
     * @return $this
     */
    public function setStartActivity(\DateTime $startActivity)
    {
        $this->startActivity = $startActivity;
        return $this;
    }
}
