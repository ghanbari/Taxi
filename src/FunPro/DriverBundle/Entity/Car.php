<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\ServiceBundle\Entity\Wakeful;
use FunPro\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Car
 *
 * @ORM\Table(name="car")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\CarRepository")
 * @ORM\EntityListeners({"CarListener"})
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @Vich\Uploadable()
 */
class Car
{
    const STATUS_SLEEP = 0;
    const STATUS_WAKEFUL = 1;
    const STATUS_SERVICE_ACCEPT = 2;
    const STATUS_SERVICE_PREPARE = 3;
    const STATUS_SERVICE_READY = 4;
    const STATUS_SERVICE_START = 5;
    const STATUS_SERVICE_IN = 6;
    const STATUS_SERVICE_END = 7;
    const STATUS_SERVICE_IN_AND_ACCEPT = 8;
    const STATUS_SERVICE_IN_AND_PREPARE = 9;

    const TYPE_PERIDE = 1;
    const TYPE_P405 = 2;
    const TYPE_P206 = 3;
    const TYPE_P207 = 4;
    const TYPE_SAMAND = 5;
    const TYPE_PERSIA = 6;
    const TYPE_ZANTIYA = 7;
    const TYPE_MEGAN = 8;
    const TYPE_JACK = 9;
    const TYPE_PERIDE_HB = 10;
    const TYPE_TAXI = 11;
    const TYPE_TIBA_HB = 12;
    const TYPE_TIBA = 13;
    const TYPE_P206_SD = 14;
    const TYPE_L90 = 15;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Public", "Driver"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Driver", inversedBy="cars")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type(type="FunPro\DriverBundle\Entity\Driver", groups={"Create", "Update"})
     *
     * TODO: Why driver must see his information
     * TODO: Driver must change to DriverO, means 'Driver Object'
     * @JS\Groups({"Driver", "Admin", "DriverInfo"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $driver;

    /**
     * @deprecated
     * @var string
     *
     * @#Assert\NotBlank(groups={"Create", "Update"})
     * @#Assert\Length(min="2",max="50", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Choice(callback="getTypes", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $type;

    /**
     * @var Plaque
     *
     * @ORM\OneToOne(targetEntity="FunPro\DriverBundle\Entity\Plaque", inversedBy="car", cascade={"persist"})
     * @ORM\JoinColumn(name="plaque_id", referencedColumnName="id", nullable=false)
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Plaque"})
     * @JS\Since("1.0.0")
     */
    private $plaque;

    /**
     * @var string
     *
     * @ORM\Column(length=25)
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(max="25")
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $color;

    /**
     * @var \DateTime Expire date of third party insurance
     *
     * @ORM\Column(name="third_party_insurance", type="date", options={"default": "2000-01-01"})
     *
     * @Assert\Date(groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Type("DateTime<'U'>")
     * @JS\Since("1.0.0")
     */
    private $thirdPartyInsurance;

    /**
     * @var \DateTime Expire date of pull insurance
     *
     * @ORM\Column(name="pull_insurance", type="date", options={"default": "2000-01-01"})
     *
     * @Assert\Date(groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Type("DateTime<'U'>")
     * @JS\Since("1.0.0")
     */
    private $pullInsurance;

    /**
     * @var \DateTime Expire date of technical diagnosis
     *
     * @ORM\Column(name="technical_diagnosis", type="date", options={"default": "2000-01-01"})
     *
     * @Assert\Date(groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Type("DateTime<'U'>")
     * @JS\Since("1.0.0")
     */
    private $technicalDiagnosis;

    /**
     * @var \DateTime Expire date of traffic plan
     *
     * @ORM\Column(name="traffic_plan", type="date", options={"default": "2000-01-01"})
     *
     * @Assert\Date(groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Type("DateTime<'U'>")
     * @JS\Since("1.0.0")
     */
    private $trafficPlan;

    /**
     * @var string
     *
     * @ORM\Column(name="body_quality", length=10, options={"default": "good"})
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Choice(callback="getAvailableQuality", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $bodyQuality;

    /**
     * @var string
     *
     * @ORM\Column(name="inside_quality", length=10, options={"default": "good"})
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Choice(callback="getAvailableQuality", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $insideQuality;

    /**
     * @var string
     *
     * @ORM\Column(name="ownership", length=20, options={"default": "own"})
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Choice(callback="getAvailableOwnerships", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $ownership;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Length(max="4", min="4", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $born;

    /**
     * @deprecated
     * @var float
     *
     * @Assert\Range(min="0", max="9", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max="10000", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $deletedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="deleted_by", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Blameable(on="change", field="deletedAt")
     *
     * @JS\Groups({"Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $deletedBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JS\Groups({"Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $createdBy;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_current", type="boolean", options={"default"=false})
     *
     * @JS\Groups({"Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $current;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     *
     * @JS\Groups({"CarStatus", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $status;

    /**
     * @var Wakeful
     *
     * @ORM\OneToOne(targetEntity="FunPro\ServiceBundle\Entity\Wakeful", mappedBy="car")
     */
    private $wakeful;

    /**
     * @var string
     *
     * @ORM\Column(name="image", nullable=true)
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $image;

    /**
     * @var File
     *
     * @Vich\UploadableField(fileNameProperty="image", mapping="car_image")
     */
    private $imageFile;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->setCurrent(true);
        $this->setStatus(self::STATUS_SLEEP);
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
     * @return array
     */
    public static function getTypes()
    {
        return array(
            'peride' => self::TYPE_PERIDE,
            '405' => self::TYPE_P405,
            '206' => self::TYPE_P206,
            '207' => self::TYPE_P207,
            'samand' => self::TYPE_SAMAND,
            'persia' => self::TYPE_PERSIA,
            'zantiya' => self::TYPE_ZANTIYA,
            'megan' => self::TYPE_MEGAN,
            'jack' => self::TYPE_JACK,
            'peride-hb' => self::TYPE_PERIDE_HB,
            'tiba' => self::TYPE_TIBA,
            'tiba-hb' => self::TYPE_TIBA_HB,
            '206-sd' => self::TYPE_P206_SD,
            'l90' => self::TYPE_L90,
            'taxi' => self::TYPE_TAXI,
        );
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Car
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getBodyQuality()
    {
        return $this->bodyQuality;
    }

    /**
     * @param string $bodyQuality
     *
     * @return $this
     */
    public function setBodyQuality($bodyQuality)
    {
        $this->bodyQuality = $bodyQuality;
        return $this;
    }

    /**
     * @return string
     */
    public function getInsideQuality()
    {
        return $this->insideQuality;
    }

    /**
     * @param string $insideQuality
     *
     * @return $this
     */
    public function setInsideQuality($insideQuality)
    {
        $this->insideQuality = $insideQuality;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnership()
    {
        return $this->ownership;
    }

    /**
     * @param string $ownership
     *
     * @return $this
     */
    public function setOwnership($ownership)
    {
        $this->ownership = $ownership;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPullInsurance()
    {
        return $this->pullInsurance;
    }

    /**
     * @param \DateTime $pullInsurance
     *
     * @return $this
     */
    public function setPullInsurance(\DateTime $pullInsurance)
    {
        $this->pullInsurance = $pullInsurance;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTechnicalDiagnosis()
    {
        return $this->technicalDiagnosis;
    }

    /**
     * @param \DateTime $technicalDiagnosis
     *
     * @return $this
     */
    public function setTechnicalDiagnosis(\DateTime $technicalDiagnosis)
    {
        $this->technicalDiagnosis = $technicalDiagnosis;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getThirdPartyInsurance()
    {
        return $this->thirdPartyInsurance;
    }

    /**
     * @param \DateTime $thirdPartyInsurance
     *
     * @return $this
     */
    public function setThirdPartyInsurance(\DateTime $thirdPartyInsurance)
    {
        $this->thirdPartyInsurance = $thirdPartyInsurance;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTrafficPlan()
    {
        return $this->trafficPlan;
    }

    /**
     * @param \DateTime $trafficPlan
     *
     * @return $this
     */
    public function setTrafficPlan(\DateTime $trafficPlan)
    {
        $this->trafficPlan = $trafficPlan;
        return $this;
    }

    /**
     * Get plaque
     *
     * @return Plaque
     */
    public function getPlaque()
    {
        return $this->plaque;
    }

    /**
     * Set plaque
     *
     * @param Plaque $plaque
     *
     * @return Car
     */
    public function setPlaque($plaque)
    {
        $this->plaque = $plaque;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return Car
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get born
     *
     * @return \DateTime
     */
    public function getBorn()
    {
        return $this->born;
    }

    /**
     * Set born
     *
     * @param \DateTime $born
     *
     * @return Car
     */
    public function setBorn($born)
    {
        $this->born = $born;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Car
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Car
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get driver
     *
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set driver
     *
     * @param Driver $driver
     *
     * @return Car
     */
    public function setDriver(Driver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get deletedBy
     *
     * @return User
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set deletedBy
     *
     * @param User $deletedBy
     *
     * @return Car
     */
    public function setDeletedBy(User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Car
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Show whether car is current
     *
     * @return boolean
     */
    public function isCurrent()
    {
        return $this->current;
    }

    /**
     * Get current
     *
     * @return boolean
     */
    public function getCurrent()
    {
        return $this->current;
    }

    /**
     * Set current
     *
     * @param boolean $current
     *
     * @return Car
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set createdBy
     *
     * @param User $createdBy
     *
     * @return Car
     */
    public function setCreatedBy(User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return Wakeful
     */
    public function getWakeful()
    {
        return $this->wakeful;
    }

    /**
     * @param Wakeful $wakeful
     *
     * @return $this
     */
    public function setWakeful($wakeful)
    {
        $this->wakeful = $wakeful;
        return $this;
    }

    /**
     * @JS\Groups({"CarStatus", "Admin"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("statusName")
     * @JS\Type("string")
     * @JS\VirtualProperty()
     *
     * @return string
     */
    public function getCurrentStatusName()
    {
        return self::getStatusName($this->getStatus());
    }

    /**
     * @param $status
     *
     * @return string
     */
    public static function getStatusName($status)
    {
        switch ($status) {
            case self::STATUS_SLEEP:
                return 'sleep';
            case self::STATUS_WAKEFUL:
                return 'wakeful';
            case self::STATUS_SERVICE_ACCEPT:
                return 'accept';
            case self::STATUS_SERVICE_PREPARE:
                return 'prepare';
            case self::STATUS_SERVICE_READY:
                return 'ready';
            case self::STATUS_SERVICE_START:
                return 'start';
            case self::STATUS_SERVICE_IN:
                return 'in service';
            case self::STATUS_SERVICE_END:
                return 'end';
            case self::STATUS_SERVICE_IN_AND_ACCEPT:
                return 'in service and accept new service';
            case self::STATUS_SERVICE_IN_AND_PREPARE:
                return 'in service and prepare new service';
            default:
                return 'unknown';
        }
    }

    /**
     * Get integer
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Car
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param File $image
     *
     * @internal param File $imageFile
     *
     * @return $this
     */
    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \Datetime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\Datetime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public static function getAvailableQuality()
    {
        return array(
            'normal' => 'normal',
            'good' => 'good',
            'perfect' => 'perfect',
        );
    }

    public static function getAvailableOwnerships()
    {
        return array(
            'own' => 'own',
            'leasing' => 'leasing',
            'corporative' => 'corporative',
            'other' => 'other'
        );
    }

    /**
     * @deprecated
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     * @deprecated
     *
     * @return $this
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @deprecated
     * @return float
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @deprecated
     * @param float $rate
     *
     * @return $this
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
        return $this;
    }
}
