<?php

namespace FunPro\ServiceBundle\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use FunPro\FinancialBundle\Entity\DiscountCode;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FunPro\AgentBundle\Entity\Agent;
use FunPro\DriverBundle\Entity\Car;
use FunPro\FinancialBundle\Entity\BaseCost;
use FunPro\FinancialBundle\Entity\Currency;
use FunPro\GeoBundle\Utility\Util;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Repository\ServiceRepository;
use FunPro\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Service
 *
 * @ORM\Table(name="service")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\ServiceRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="canceledAt", timeAware=false)
 */
class Service
{
    const TYPE_DISTANCE = 1;
    const TYPE_TIMING = 2;

    const DESIRE_QUALITY = 1;
    const DESIRE_PRICE = 2;
    const DESIRE_FAST = 3;
    const DESIRE_CLASS = 4;
    const DESIRE_SUGGEST = 5;

    const PROPAGATION_TYPE_ALL = 1;
    const PROPAGATION_TYPE_LIST = 2;
    const PROPAGATION_TYPE_SINGLE = 3;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin", "Public"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var Passenger
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\Type(type="FunPro\PassengerBundle\Entity\Passenger", groups={"Create"})
     *
     * TODO: why passenger must see his information
     * @JS\Groups({"Passenger", "Driver", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $passenger;

    /**
     * @var Agent
     *
     * @ORM\ManyToOne(targetEntity="FunPro\AgentBundle\Entity\Agent")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\Type(type="FunPro\AgentBundle\Entity\Agent", groups={"Create"})
     *
     * TODO: why agent must see his information
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
     * @ORM\Column(name="origin_point", type="point", nullable=true)
     *
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin", "Point"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $originPoint;

    /**
     * @var string
     *
     * @ORM\Column(name="start_address", length=500, options={"default"="???"})
     *
     * @Assert\Length(max="500", groups={"Create", "Update"})
     * @Assert\NotNull(groups={"Create", "Update"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin", "Point"})
     * @JS\Since("1.0.0")
     */
    private $startAddress;

    /**
     * @var Point
     *
     * @ORM\Column(name="end_point", type="point")
     *
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     * @Assert\NotNull(groups={"Create", "Update"})
     *
     * @JS\Groups({"Passenger", "Driver", "Admin", "Point"})
     * @JS\Type(name="FunPro\GeoBundle\Doctrine\ValueObject\Point")
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $endPoint;

    /**
     * @var Point
     *
     * @ORM\Column(name="destination_point", type="point", nullable=true)
     *
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Passenger", "Driver", "Admin", "Point"})
     * @JS\Type(name="FunPro\GeoBundle\Doctrine\ValueObject\Point")
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $destinationPoint;

    /**
     * @var string
     *
     * @ORM\Column(name="end_address", length=500, options={"default"="???"})
     *
     * @Assert\Length(max="500", groups={"Create", "Update"})
     * @Assert\NotNull(groups={"Create", "Update"})
     *
     * @JS\Groups({"Passenger", "Driver", "Admin", "Point"})
     * @JS\Since("1.0.0")
     */
    private $endAddress;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Choice(callback="getTypes", groups={"Create"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
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
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="RESTRICT")
     *
     * @JS\Groups({"Car"})
     * @JS\MaxDepth(2)
     * @JS\Since("1.0.0")
     */
    private $car;

    /**
     * @var integer
     *
     * @ORM\Column(name="propagation_type", type="smallint")
     *
     * @Assert\NotBlank(groups={"Create"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $propagationType;

    /**
     * @var PropagationList
     *
     * @ORM\OneToMany(targetEntity="FunPro\ServiceBundle\Entity\PropagationList", mappedBy="service")
     *
     * @JS\Groups({"PropagationList"})
     * @JS\MaxDepth(3)
     * @JS\Since("1.0.0")
     */
    private $propagationList;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=1, nullable=true)
     *
     * @Assert\Range(min="0", max="6", groups={"Rate"})
     * @Assert\NotNull(groups={"Rate"})
     * @Assert\Type(type="numeric", groups={"Rate"})
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $driverRate;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=1, nullable=true)
     *
     * @Assert\Range(min="0", max="6", groups={"Rate"})
     * @Assert\NotNull(groups={"Rate"})
     * @Assert\Type(type="numeric", groups={"Rate"})
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $passengerRate;

    /**
     * @var LineString
     *
     * @ORM\Column(type="linestring", nullable=true)
     *
     * TODO: must do mapping for third party library JS\Groups({"Passenger", "Driver", "Admin"})
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     * @JS\Type(name="CrEOF\Spatial\PHP\Types\Geometry\LineString")
     */
    private $route;

    /**
     * @var integer $distance distance based on meter calculated by google
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $distance;

    /**
     * @var integer $distance distance based on meter calculated by gps
     *
     * @ORM\Column(name="real_distance", type="integer", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $realDistance;

    /**
     * @var int $price total price of service. calculated based on $distance.
     *
     * this value is original price of service.(Excluding discounts)
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JS\Groups({"Driver", "Admin"})
     * @JS\SerializedName("netPrice")
     * @JS\Since("1.0.0")
     */
    private $price;
    
    /**
     * @var int $price total price of service. calculated based on $distance.
     *
     * this value is discounted price of service.
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @JS\Exclude()
     */
    private $discountedPrice;

    /**
     * @var integer $realPrice total price of service. calculated based on $realDistance
     *
     * @ORM\Column(name="real_price", type="integer", nullable=true, options={"default"="0"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("2.0.0")
     * FIXME: user should not can see this value. change serialize groups.
     */
    private $realPrice;

    /**
     * @var ArrayCollection
     * @deprecated
     *
     * @ORM\OneToMany(targetEntity="FunPro\ServiceBundle\Entity\FloatingCost", mappedBy="service")
     *
     * @JS\Groups({"Cost"})
     * @JS\Since("1.0.0")
     * @JS\Exclude()
     */
    private $floatingCosts;

    /**
     * @var BaseCost
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\BaseCost")
     *
     * @Assert\Type(type="FunPro\FinancialBundle\Entity\BaseCost")
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $baseCost;

    /**
     * @var DiscountCode
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\DiscountCode")
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $discountCode;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="FunPro\ServiceBundle\Entity\ServiceLog", mappedBy="service")
     *
     * @JS\Groups({"ServiceLogs"})
     * @JS\Since("1.0.0")
     */
    private $logs;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     *
     * @JS\Groups({"Public", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $updatedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="canceled_by", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Gedmo\Blameable(on="change", field="canceledAt")
     *
     * @JS\Groups({"CanceledBy"})
     * @JS\Since("1.0.0")
     */
    private $canceledBy;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="canceled_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $canceledAt;

    /**
     * @var CanceledReason
     *
     * @ORM\ManyToOne(targetEntity="FunPro\ServiceBundle\Entity\CanceledReason")
     * @ORM\JoinColumn(name="canceled_reason", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $canceledReason;

    /**
     * @deprecated
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="FunPro\FinancialBundle\Entity\Currency")
     * @ORM\JoinColumn(name="currency_id", referencedColumnName="id")
     *
     * @JS\Groups({"Currency"})
     * @JS\Since("1.0.0")
     */
    private $currency;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint")
     *
     * @JS\Groups({"Public"})
     * @JS\SerializedName("statusNumber")
     * @JS\Since("1.0.0")
     */
    private $status;

    public function __construct()
    {
        $this->setStatus(ServiceLog::STATUS_REQUESTED);
        $this->setEndPoint(null);
        $this->setType(self::TYPE_DISTANCE);
        $this->setDesire(self::DESIRE_SUGGEST);
        $this->setCreatedAt(new \DateTime());
        $this->floatingCosts = new ArrayCollection();
        $this->propagationList = new ArrayCollection();
        $this->setPropagationType(self::PROPAGATION_TYPE_ALL);
        $this->logs = new ArrayCollection();
        $this->extraData = new ArrayCollection();
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
            'distance' => self::TYPE_DISTANCE,
            'timing' => self::TYPE_TIMING,
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
            'quality' => self::DESIRE_QUALITY,
            'price' => self::DESIRE_PRICE,
            'fast' => self::DESIRE_FAST,
            'class' => self::DESIRE_CLASS,
            'suggest' => self::DESIRE_SUGGEST,
        );
    }

    /**
     * @return array
     */
    public static function getPropaginationTypes()
    {
        return array(
            'all' => self::PROPAGATION_TYPE_ALL,
            'list' => self::PROPAGATION_TYPE_LIST,
            'single' => self::PROPAGATION_TYPE_SINGLE,
        );
    }
    
    /**
     * show rounded price of service
     *
     * @JS\VirtualProperty()
     * @JS\SerializedName("price")
     * @JS\Type(name="integer")
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     *
     * @return array
     */
    public function getRoundedPrice()
    {
        return self::roundPrice($this->getDiscountedPrice());
    }

    /**
     * show time of service
     *
     * @JS\VirtualProperty()
     * @JS\SerializedName("period")
     * @JS\Type(name="integer")
     * @JS\Groups({"Passenger", "Driver"})
     * @JS\Since("1.0.0")
     *
     * @return array
     */
    public function getPeriod()
    {
        $fisrtLog = $this->logs->first();
        $lastLog = $this->logs->last();

        if ($fisrtLog and $lastLog) {
            $period = $lastLog->getAtTime()->getTimestamp() - $fisrtLog->getAtTime()->getTimestamp();
        } else {
            $period = -1;
        }

        return $period;
    }

    /**
     * return service status
     *
     * @JS\VirtualProperty()
     * @JS\SerializedName("status")
     * @JS\Type(name="string")
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     *
     * @return array
     */
    public function getStatusName()
    {
        switch ($this->status) {
            case ServiceLog::STATUS_REQUESTED:
                return 'requested';
            case ServiceLog::STATUS_CANCELED:
                return 'canceled';
            case ServiceLog::STATUS_REJECTED:
                return 'rejected';
            case ServiceLog::STATUS_ACCEPTED:
                return 'accepted';
            case ServiceLog::STATUS_READY:
                return 'ready';
            case ServiceLog::STATUS_START:
                return 'start';
            case ServiceLog::STATUS_FINISH:
                return 'finish';
            case ServiceLog::STATUS_PAYED:
                return 'payed';
            default:
                return 'unknown';
        }
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
     * Get startPoint
     *
     * @return Point
     */
    public function getStartPoint()
    {
        return $this->startPoint;
    }

    /**
     * Set startPoint
     *
     * @param Point $startPoint
     *
     * @return Service
     */
    public function setStartPoint(Point $startPoint)
    {
        $this->startPoint = $startPoint;

        return $this;
    }

    /**
     * @return Point
     */
    public function getOriginPoint()
    {
        return $this->originPoint;
    }

    /**
     * @param Point $originPoint
     * @return Service;
     */
    public function setOriginPoint($originPoint)
    {
        $this->originPoint = $originPoint;
        return $this;
    }

    /**
     * Get endPoint
     *
     * @return Point
     */
    public function getEndPoint()
    {
        return $this->endPoint;
    }

    /**
     * Set endPoint
     *
     * @param Point $endPoint
     *
     * @return Service
     */
    public function setEndPoint(Point $endPoint = null)
    {
        $this->endPoint = $endPoint;

        return $this;
    }

    /**
     * @return Point
     */
    public function getDestinationPoint()
    {
        return $this->destinationPoint;
    }

    /**
     * @param Point $destinationPoint
     * @return Service;
     */
    public function setDestinationPoint($destinationPoint)
    {
        $this->destinationPoint = $destinationPoint;
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndAddress()
    {
        return $this->endAddress;
    }

    /**
     * @param mixed $endAddress
     *
     * @return $this
     */
    public function setEndAddress($endAddress)
    {
        $this->endAddress = $endAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartAddress()
    {
        return $this->startAddress;
    }

    /**
     * @param string $startAddress
     *
     * @return $this
     */
    public function setStartAddress($startAddress)
    {
        $this->startAddress = $startAddress;
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
     * Set type
     *
     * @param integer $type
     *
     * @throws \InvalidArgumentException
     * @return Service
     */
    public function setType($type)
    {
        if ($type === null) {
            return;
        }

        if (!in_array($type, $this->getTypes())) {
            throw new \InvalidArgumentException('Invalid type');
        }

        $this->type = $type;

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
     * Set desire
     *
     * @param integer $desire
     *
     * @return Service
     */
    public function setDesire($desire)
    {
        if ($desire === null) {
            return;
        }

        if (!in_array($desire, $this->getDesireOptions())) {
            throw new \InvalidArgumentException('Invalid desire');
        }

        $this->desire = $desire;

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
     * @return Service
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * Set driverRate
     *
     * @param string $driverRate
     *
     * @return Service
     */
    public function setDriverRate($driverRate)
    {
        $this->driverRate = $driverRate;

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
     * Set passengerRate
     *
     * @param string $passengerRate
     *
     * @return Service
     */
    public function setPassengerRate($passengerRate)
    {
        $this->passengerRate = $passengerRate;

        return $this;
    }

    /**
     * Get distance
     *
     * @return integer
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set distance
     *
     * @param integer $distance
     *
     * @return Service
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     * @return Service;
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

//    /**
//     * Set price
//     *
//     * @throws \RuntimeException
//     * @return Service
//     */
//    public function calculatePrice()
//    {
//        if ($this->getDistance() == 0) {
//            throw new \RuntimeException('distance is null');
//        }
//
//        $baseCosts = $this->getBaseCost();
//        $this->price = $baseCosts->getEntranceFee() + ($baseCosts->getCostPerMeter() * $this->getDistance());
////        $this->price -= ($this->price * $baseCosts->getDiscountPercent()) / 100;
//
//        return $this;
//    }
//
//    /**
//     * Calculate final price. price & discount.
//     *
//     * @return int
//     */
//    public function getDiscountedPrice()
//    {
//        $price = $this->price - ($this->price * $this->baseCost->getDiscountPercent() / 100);
//
//        if ($discount = $this->getDiscountCode()) {
//            $price -= $discount->getDiscount();
//        }
//
//        return $price > 0 ? $price : 0;
//    }

    /**
     * Round price
     *
     * @param $price
     *
     * @return float
     */
    public static function roundPrice($price)
    {
        return ($price % 500 > 250) ? ceil($price / 500) * 500 : floor($price / 500) * 500;
    }

    /**
     * Get car
     *
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * Set car
     *
     * @param Car $car
     *
     * @return Service
     */
    public function setCar(Car $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * @Assert\Callback(groups={"Create"})
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getPassenger() and $this->getAgent()) {
            $context->buildViolation('you must set only either passenger or agent')
                ->atPath('passenger')
                ->addViolation();
        } elseif (!($this->getPassenger() or $this->getAgent())) {
            $context->buildViolation('you must set passenger or agent')
                ->atPath('passenger')
                ->addViolation();
        }
    }

    /**
     * Get passenger
     *
     * @return Passenger
     */
    public function getPassenger()
    {
        return $this->passenger;
    }

    /**
     * Set passenger
     *
     * @param Passenger $passenger
     *
     * @return Service
     */
    public function setPassenger(Passenger $passenger = null)
    {
        $this->passenger = $passenger;

        return $this;
    }

    /**
     * Get agent
     *
     * @return Agent
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * Set agent
     *
     * @param Agent $agent
     *
     * @return Service
     */
    public function setAgent(Agent $agent = null)
    {
        $this->agent = $agent;

        return $this;
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
    public function setCreatedAt(\DateTime $createdAt)
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
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getFloatingCosts()
    {
        return $this->floatingCosts;
    }

    /**
     * @param ArrayCollection $floatingCosts
     *
     * @return $this
     */
    public function setFloatingCosts(ArrayCollection $floatingCosts)
    {
        $this->floatingCosts = $floatingCosts;
        return $this;
    }

    /**
     * Get propagationType
     *
     * @return integer
     */
    public function getPropagationType()
    {
        return $this->propagationType;
    }

    /**
     * Set propagationType
     *
     * @param integer $propagationType
     *
     * @return Service
     */
    public function setPropagationType($propagationType)
    {
        $this->propagationType = $propagationType;

        return $this;
    }

    /**
     * Add propagationList
     *
     * @param PropagationList $propagationList
     *
     * @return Service
     */
    public function addPropagationList(PropagationList $propagationList)
    {
        $this->propagationList[] = $propagationList;

        return $this;
    }

    /**
     * Remove propagationList
     *
     * @param PropagationList $propagationList
     */
    public function removePropagationList(PropagationList $propagationList)
    {
        $this->propagationList->removeElement($propagationList);
    }

    /**
     * Get propagationList
     *
     * @return ArrayCollection
     */
    public function getPropagationList()
    {
        return $this->propagationList;
    }

    /**
     * Add floatingCosts
     *
     * @param FloatingCost $floatingCosts
     *
     * @return Service
     */
    public function addFloatingCost(FloatingCost $floatingCosts)
    {
        $this->floatingCosts[] = $floatingCosts;

        return $this;
    }

    /**
     * Remove floatingCosts
     *
     * @param FloatingCost $floatingCosts
     */
    public function removeFloatingCost(FloatingCost $floatingCosts)
    {
        $this->floatingCosts->removeElement($floatingCosts);
    }

    /**
     * @return ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * @param ArrayCollection $logs
     *
     * @return $this
     */
    public function setLogs(ArrayCollection $logs)
    {
        $this->logs = $logs;
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCanceledAt()
    {
        return $this->canceledAt;
    }

    /**
     * @param \Datetime $canceledAt
     *
     * @return $this
     */
    public function setCanceledAt(\DateTime $canceledAt)
    {
        $this->canceledAt = $canceledAt;
        return $this;
    }

    /**
     * @return User
     */
    public function getCanceledBy()
    {
        return $this->canceledBy;
    }

    /**
     * @param User $canceledBy
     *
     * @return $this
     */
    public function setCanceledBy(User $canceledBy)
    {
        $this->canceledBy = $canceledBy;
        return $this;
    }

    /**
     * @return CanceledReason
     */
    public function getCanceledReason()
    {
        return $this->canceledReason;
    }

    /**
     * @param CanceledReason $canceledReason
     *
     * @return $this
     */
    public function setCanceledReason(CanceledReason $canceledReason)
    {
        $this->canceledReason = $canceledReason;
        return $this;
    }

    public function calculateRealDistance()
    {
        if (is_object($this->getRoute()) and count($this->getRoute()->toArray()) > 1) {
            $this->setRealDistance(Util::lengthOfLineString($this->getRoute()));
        }
    }

    /**
     * Get route
     *
     * @return LineString
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set route
     *
     * @param LineString $route
     *
     * @return Service
     */
    public function setRoute(LineString $route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @deprecated
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @deprecated
     * @param Currency $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getExtraData()
    {
        return $this->extraData;
    }

    /**
     * @param ArrayCollection $extraData
     *
     * @return $this
     */
    public function setExtraData(ArrayCollection $extraData)
    {
        $this->extraData = $extraData;
        return $this;
    }

    /**
     * @return int
     */
    public function getRealPrice()
    {
        return $this->realPrice;
    }

    /**
     * @throws \RuntimeException
     * @return $this
     */
    public function calculateRealPrice()
    {
        $this->realPrice = ServiceRepository::calculatePrice($this->getBaseCost(), $this->getRealDistance(), true, $this->getDiscountCode());

        return $this;
    }

    /**
     * @return int
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
     * @return Service
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Add logs
     *
     * @param ServiceLog $logs
     *
     * @return Service
     */
    public function addLog(ServiceLog $logs)
    {
        $this->logs[] = $logs;

        return $this;
    }

    /**
     * Remove logs
     *
     * @param ServiceLog $logs
     */
    public function removeLog(ServiceLog $logs)
    {
        $this->logs->removeElement($logs);
    }

    /**
     * @return BaseCost
     */
    public function getBaseCost()
    {
        return $this->baseCost;
    }

    /**
     * @param BaseCost $baseCost
     *
     * @return $this
     */
    public function setBaseCost(BaseCost $baseCost)
    {
        $this->baseCost = $baseCost;
        return $this;
    }

    /**
     * @return int
     */
    public function getRealDistance()
    {
        return $this->realDistance;
    }

    /**
     * @param int $realDistance
     *
     * @return $this
     */
    public function setRealDistance($realDistance)
    {
        if (is_nan($realDistance) or !is_numeric($realDistance)) {
            return;
        }

        $this->realDistance = $realDistance;
        return $this;
    }

    /**
     * @return DiscountCode
     */
    public function getDiscountCode()
    {
        return $this->discountCode;
    }

    /**
     * @param DiscountCode $discountCode
     * @return Service;
     */
    public function setDiscountCode(DiscountCode $discountCode)
    {
        $this->discountCode = $discountCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getDiscountedPrice()
    {
        return $this->discountedPrice;
    }

    /**
     * @param int $discountedPrice
     * @return Service;
     */
    public function setDiscountedPrice($discountedPrice)
    {
        $this->discountedPrice = $discountedPrice;
        return $this;
    }
}
