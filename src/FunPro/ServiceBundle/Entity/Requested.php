<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\AgentBundle\Entity\Agent;
use FunPro\DriverBundle\Entity\Car;
use FunPro\GeoBundle\Doctrine\ValueObject\LineString;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\PassengerBundle\Entity\Passenger;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Requested
 *
 * @ORM\Table(name="requested")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\RequestedRepository")
 */
class Requested
{
    const TYPE_DISTANCE = 'distance';
    const TYPE_TIMING   = 'timing';

    const DESIRE_QUALITY = 'quality';
    const DESIRE_PRICE   = 'price';
    const DESIRE_FAST    = 'fast';
    const DESIRE_CLASS   = 'class';
    const DESIRE_SUGGEST = 'suggest';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var Passenger
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger_id", referencedColumnName="id", onDelete="cascade")
     *
     * @Assert\Type(type="FunPro\PassengerBundle\Entity\Passenger", groups={"Create"})
     *
     * @JS\Groups({"Passenger", "Driver", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $passenger;

    /**
     * @var Agent
     *
     * @ORM\ManyToOne(targetEntity="FunPro\AgentBundle\Entity\Agent")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="cascade")
     *
     * @Assert\Type(type="FunPro\AgentBundle\Entity\Agent", groups={"Create"})
     *
     * @JS\Groups({"Driver", "Agent", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $agent;

    /**
     * @var Point
     *
     * @ORM\Column(name="start_point", type="point")
     *
     * @Assert\NotNull(groups={"Create"})
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin", "Point"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $startPoint;

    /**
     * @var Point
     *
     * @ORM\Column(name="end_point", type="point", nullable=true)
     *
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Passenger", "Driver", "Admin", "Point"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $endPoint;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Choice(callback="getTypes", groups={"Create"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Choice(callback="getDesireOptions", groups={"Create"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $desire;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(max="4000", groups={"Create"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $description;

    /**
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="cascade")
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $car;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=2, scale=1, nullable=true)
     *
     * @Assert\Range(min="0", max="5", groups={"Rate"})
     * @Assert\NotNull(groups={"Rate"})
     * @Assert\Type(type="numeric", groups={"Rate"})
     *
     * @JS\Groups({"Passenger", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $driverRate;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=2, scale=1, nullable=true)
     *
     * @Assert\Range(min="0", max="5", groups={"Rate"})
     * @Assert\NotNull(groups={"Rate"})
     * @Assert\Type(type="numeric", groups={"Rate"})
     *
     * @JS\Groups({"Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $passengerRate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_time", type="datetime", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_time", type="datetime", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $endTime;

    /**
     * @var LineString
     *
     * @ORM\Column(type="linestring", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $route;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=6, scale=3, nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $distance;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $price;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Public"})
     */
    private $createdAt;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     *
     * @JS\Groups({"Public"})
     */
    private $updatedAt;

    public function __construct()
    {
        $this->setRoute(new LineString());
        $this->setStartTime(new \DateTime());
    }

    /**
     * get available types
     *
     * @JS\SerializedName("types")
     * @JS\Since("1.0.0")
     */
    public static function getTypes()
    {
        return array(
            self::TYPE_DISTANCE => self::TYPE_DISTANCE,
            self::TYPE_TIMING => self::TYPE_TIMING,
        );
    }

    /**
     * get available options for desire field
     *
     * @JS\SerializedName("desires")
     * @JS\Since("1.0.0")
     */
    public static function getDesireOptions()
    {
        return array(
            self::DESIRE_QUALITY => self::DESIRE_QUALITY,
            self::DESIRE_PRICE => self::DESIRE_PRICE,
            self::DESIRE_FAST => self::DESIRE_FAST,
            self::DESIRE_CLASS => self::DESIRE_CLASS,
            self::DESIRE_SUGGEST => self::DESIRE_SUGGEST,
        );
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
     * Set startPoint
     *
     * @param point $startPoint
     * @return Requested
     */
    public function setStartPoint($startPoint)
    {
        $this->startPoint = $startPoint;

        return $this;
    }

    /**
     * Get startPoint
     *
     * @return point
     */
    public function getStartPoint()
    {
        return $this->startPoint;
    }

    /**
     * Set endPoint
     *
     * @param point $endPoint
     * @return Requested
     */
    public function setEndPoint($endPoint)
    {
        $this->endPoint = $endPoint;

        return $this;
    }

    /**
     * Get endPoint
     *
     * @return point
     */
    public function getEndPoint()
    {
        return $this->endPoint;
    }

    /**
     * Set type
     *
     * @param string $type
     * @throws \InvalidArgumentException
     * @return Requested
     */
    public function setType($type)
    {
        if (!in_array($type, $this->getTypes())) {
            throw new \InvalidArgumentException('Invalid type');
        }

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
     * Set desire
     *
     * @param string $desire
     * @return Requested
     */
    public function setDesire($desire)
    {
        if (!in_array($desire, $this->getDesireOptions())) {
            throw new \InvalidArgumentException('Invalid desire');
        }

        $this->desire = $desire;

        return $this;
    }

    /**
     * Get desire
     *
     * @Assert\NotNull(groups={"Create"})
     *
     * @return string 
     */
    public function getDesire()
    {
        return $this->desire;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Requested
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
     * Set driverRate
     *
     * @param string $driverRate
     * @return Requested
     */
    public function setDriverRate($driverRate)
    {
        $this->driverRate = $driverRate;

        return $this;
    }

    /**
     * Get driverRate
     *
     * @return string 
     */
    public function getDriverRate()
    {
        return $this->driverRate;
    }

    /**
     * Set passengerRate
     *
     * @param string $passengerRate
     * @return Requested
     */
    public function setPassengerRate($passengerRate)
    {
        $this->passengerRate = $passengerRate;

        return $this;
    }

    /**
     * Get passengerRate
     *
     * @return string 
     */
    public function getPassengerRate()
    {
        return $this->passengerRate;
    }

    /**
     * Set startTime
     *
     * @param \DateTime $startTime
     * @return Requested
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return \DateTime 
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime
     *
     * @param \DateTime $endTime
     * @return Requested
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return \DateTime 
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set route
     *
     * @param linestring $route
     * @return Requested
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return linestring 
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set distance
     *
     * @param string $distance
     * @return Requested
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return string 
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return Requested
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
     * Set passenger
     *
     * @param \FunPro\PassengerBundle\Entity\Passenger $passenger
     * @return Requested
     */
    public function setPassenger(\FunPro\PassengerBundle\Entity\Passenger $passenger = null)
    {
        $this->passenger = $passenger;

        return $this;
    }

    /**
     * Get passenger
     *
     * @return \FunPro\PassengerBundle\Entity\Passenger 
     */
    public function getPassenger()
    {
        return $this->passenger;
    }

    /**
     * Set agent
     *
     * @param \FunPro\AgentBundle\Entity\Agent $agent
     * @return Requested
     */
    public function setAgent(\FunPro\AgentBundle\Entity\Agent $agent = null)
    {
        $this->agent = $agent;

        return $this;
    }

    /**
     * Get agent
     *
     * @return \FunPro\AgentBundle\Entity\Agent 
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Set car
     *
     * @param \FunPro\DriverBundle\Entity\Car $car
     * @return Requested
     */
    public function setCar(\FunPro\DriverBundle\Entity\Car $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get car
     *
     * @return \FunPro\DriverBundle\Entity\Car 
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @Assert\Callback(groups={"Create"})
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getPassenger() and $this->getAgent()) {
            $context->buildViolation("you must set only either passenger or agent")
                ->atPath('passenger')
                ->addViolation();
        } elseif (!($this->getPassenger() or $this->getAgent())) {
            $context->buildViolation("you must set passenger or agent")
                ->atPath('passenger')
                ->addViolation();
        }
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
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
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
