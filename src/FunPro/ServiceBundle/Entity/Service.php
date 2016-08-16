<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FunPro\AgentBundle\Entity\Agent;
use FunPro\DriverBundle\Entity\Car;
use FunPro\FinancialBundle\Entity\Currency;
use FunPro\GeoBundle\Doctrine\ValueObject\LineString;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\GeoBundle\Utility\Util;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Gedmo\Mapping\Annotation as Gedmo;

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
    const TYPE_TIMING   = 2;

    const DESIRE_QUALITY = 1;
    const DESIRE_PRICE   = 2;
    const DESIRE_FAST    = 3;
    const DESIRE_CLASS   = 4;
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
     * @ORM\Column(name="end_point", type="point", nullable=true)
     *
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Passenger", "Driver", "Admin", "Point"})
     * @JS\Type(name="FunPro\GeoBundle\Doctrine\ValueObject\Point")
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $endPoint;

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
     * @ORM\Column(type="decimal", precision=1, nullable=true)
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
     * @var LineString
     *
     * @ORM\Column(type="linestring", nullable=true)
     *
     * @JS\Groups({"Passenger", "Driver", "Admin"})
     * @JS\Since("1.0.0")
     * @JS\Type(name="array<FunPro\GeoBundle\Doctrine\ValueObject\Point>")
     */
    private $route;

    /**
     * @var integer $distance distance based on meter
     *
     * @ORM\Column(type="integer", nullable=true)
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
     * @var integer
     *
     * @ORM\Column(name="real_price", type="integer", nullable=true, options={"default"="0"})
     *
     * @JS\Groups({"Passenger", "Driver", "Agent", "Admin"})
     * @JS\Since("2.0.0")
     */
    private $realPrice;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="FunPro\ServiceBundle\Entity\FloatingCost", mappedBy="service")
     *
     * @JS\Groups({"Cost"})
     * @JS\Since("1.0.0")
     */
    private $floatingCosts;

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
     * container for extra data related to service.
     *
     * not persisted to database and not serialized
     *
     * @var ArrayCollection
     */
    private $extraData;

    public function __construct()
    {
        $this->setEndPoint(null);
        $this->setType(self::TYPE_DISTANCE);
        $this->setDesire(self::DESIRE_SUGGEST);
        $this->setRoute(new LineString());
        $this->setCreatedAt(new \DateTime());
        $this->floatingCosts = new ArrayCollection();
        $this->propagationList = new ArrayCollection();
        $this->setPropagationType(self::PROPAGATION_TYPE_ALL);
        $this->logs = new ArrayCollection();
        $this->extraData = new ArrayCollection();
    }

    /**
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
        $lastLog  = $this->logs->last();

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
     * @JS\Type(name="array")
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     *
     * @return array
     */
    public function getStatus()
    {
        if (!$this->logs->last()) {
            return array(
                'code' => ServiceLog::STATUS_REQUESTED,
                'name' => 'requested',
            );
        }

        switch ($this->logs->last()->getStatus()) {
            case ServiceLog::STATUS_REQUESTED:
                return array(
                    'code' => ServiceLog::STATUS_REQUESTED,
                    'name' => 'requested',
                );
            case ServiceLog::STATUS_CANCELED:
                return array(
                    'code' => ServiceLog::STATUS_CANCELED,
                    'name' => 'canceled',
                );
            case ServiceLog::STATUS_REJECTED:
                return array(
                    'code' => ServiceLog::STATUS_REJECTED,
                    'name' => 'rejected',
                );
            case ServiceLog::STATUS_ACCEPTED:
                return array(
                    'code' => ServiceLog::STATUS_ACCEPTED,
                    'name' => 'accepted',
                );
            case ServiceLog::STATUS_READY:
                return array(
                    'code' => ServiceLog::STATUS_READY,
                    'name' => 'ready',
                );
            case ServiceLog::STATUS_START:
                return array(
                    'code' => ServiceLog::STATUS_START,
                    'name' => 'start',
                );
            case ServiceLog::STATUS_FINISH:
                return array(
                    'code' => ServiceLog::STATUS_FINISH,
                    'name' => 'finish',
                );
            case ServiceLog::STATUS_PAYED:
                return array(
                    'code' => ServiceLog::STATUS_PAYED,
                    'name' => 'payed',
                );
            default:
                return array(
                    'code' => $this->logs->last()->getStatus(),
                    'name' => 'unknown',
                );
        }
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
            'price'   => self::DESIRE_PRICE,
            'fast'    => self::DESIRE_FAST,
            'class'   => self::DESIRE_CLASS,
            'suggest' => self::DESIRE_SUGGEST,
        );
    }

    public static function getPropaginationTypes()
    {
        return array(
            'all' => self::PROPAGATION_TYPE_ALL,
            'list' => self::PROPAGATION_TYPE_LIST,
            'single' => self::PROPAGATION_TYPE_SINGLE,
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
     * Get startPoint
     *
     * @return Point
     */
    public function getStartPoint()
    {
        return $this->startPoint;
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
     * Get endPoint
     *
     * @return Point
     */
    public function getEndPoint()
    {
        return $this->endPoint;
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
     *
     * @return Service
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
     *
     * @return Service
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
     *
     * @return Service
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
     * Get route
     *
     * @return LineString
     */
    public function getRoute()
    {
        return $this->route;
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
     * Get distance
     *
     * @return integer
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Service
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
     * Get passenger
     *
     * @return Passenger
     */
    public function getPassenger()
    {
        return $this->passenger;
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
     * Get agent
     *
     * @return Agent
     */
    public function getAgent()
    {
        return $this->agent;
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
     * Get car
     *
     * @return Car
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
     * Get propagationType
     *
     * @return integer
     */
    public function getPropagationType()
    {
        return $this->propagationType;
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

    public function updateDistance()
    {
        if ($this->getRoute()->count() > 1) {
            $this->setDistance(Util::lengthOfLineString($this->getRoute()));
        }
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
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
     * @param integer $realPrice
     *
     * @return $this
     */
    public function setRealPrice($realPrice)
    {
        $this->realPrice = $realPrice;
        return $this;
    }
}
