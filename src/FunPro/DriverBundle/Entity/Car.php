<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\ServiceBundle\Entity\Wakeful;
use FunPro\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Car
 *
 * @ORM\Table(name="car")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\CarRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Car
{
    const STATUS_WAKEFUL         = 'wakeful';
    const STATUS_SLEEP           = 'sleep';
    const STATUS_SERVICE_ACCEPT  = 'accept';
    const STATUS_SERVICE_PREPARE = 'prepare'; #go to passenger place
    const STATUS_SERVICE_READY   = 'ready'; #in passenger place
    const STATUS_SERVICE_START   = 'start';
    const STATUS_SERVICE_IN      = 'in_service';
    const STATUS_SERVICE_END     = 'end';

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
     * @JS\Groups({"Driver", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $driver;

    /**
     * @var string
     *
     * @ORM\Column()
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(min="2",max="50", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $brand;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(min="2", max="50", groups={"Create", "Update"})
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
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(max="15", groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $color;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Date(groups={"Create", "Update"})
     *
     * @JS\Groups({"Public", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $born;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=2, scale=1, options={"default"=0})
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
     * @ORM\Column(name="is_current", type="boolean", options={"default"=true})
     *
     * @JS\Groups({"Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $current;

    /**
     * @var string
     *
     * @ORM\Column(name="status", length=15)
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

    public function __construct()
    {
        //TODO: status must be disable by default
        $this->setCurrent(true);
        $this->setRate(0);
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
     * Set type
     *
     * @param string $type
     * @return Car
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
     * Set plaque
     *
     * @param Plaque $plaque
     * @return Car
     */
    public function setPlaque($plaque)
    {
        $this->plaque = $plaque;

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
     * Set color
     *
     * @param string $color
     * @return Car
     */
    public function setColor($color)
    {
        $this->color = $color;

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
     * Set born
     *
     * @param \DateTime $born
     * @return Car
     */
    public function setBorn($born)
    {
        $this->born = $born;

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
     * Set rate
     *
     * @param string $rate
     * @return Car
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
     * Set description
     *
     * @param string $description
     * @return Car
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Car
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

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
     * Set driver
     *
     * @param \FunPro\DriverBundle\Entity\Driver $driver
     * @return Car
     */
    public function setDriver(\FunPro\DriverBundle\Entity\Driver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get driver
     *
     * @return \FunPro\DriverBundle\Entity\Driver 
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set deletedBy
     *
     * @param \FunPro\UserBundle\Entity\User $deletedBy
     * @return Car
     */
    public function setDeletedBy(\FunPro\UserBundle\Entity\User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Get deletedBy
     *
     * @return \FunPro\UserBundle\Entity\User 
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Car
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

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
     * Set current
     *
     * @param boolean $current
     * @return Car
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
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
     * Set createdBy
     *
     * @param \FunPro\UserBundle\Entity\User $createdBy
     * @return Car
     */
    public function setCreatedBy(\FunPro\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \FunPro\UserBundle\Entity\User 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Car
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
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
     * Set brand
     *
     * @param string $brand
     * @return Car
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get brand
     *
     * @return string 
     */
    public function getBrand()
    {
        return $this->brand;
    }
}
