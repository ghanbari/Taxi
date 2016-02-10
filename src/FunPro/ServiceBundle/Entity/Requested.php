<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\AgentBundle\Entity\Agent;
use FunPro\DriverBundle\Entity\Car;
use FunPro\GeoBundle\Doctrine\ValueObject\LineString;
use FunPro\PassengerBundle\Entity\Passenger;
use JMS\Serializer\Annotation as JS;

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
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var Passenger
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="passenger_id", referencedColumnName="id", onDelete="cascade")
     */
    private $passenger;

    /**
     * @var Agent
     *
     * @ORM\ManyToOne(targetEntity="FunPro\AgentBundle\Entity\Agent")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="cascade")
     */
    private $agent;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=15, nullable=true)
     */
    private $desire;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="cascade")
     */
    private $car;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=2, scale=2, nullable=true)
     */
    private $driverRate;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=2, scale=2, nullable=true)
     */
    private $passengerRate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endTime;

    /**
     * @var LineString
     *
     * @ORM\Column(type="linestring")
     */
    private $route;

    /**
     * @var double
     *
     * @ORM\Column(type="decimal", precision=6, scale=3, nullable=true)
     */
    private $distance;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    public function __construct()
    {
        $this->setRoute(new LineString());
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
     * @return Requested
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
     * Set desire
     *
     * @param string $desire
     * @return Requested
     */
    public function setDesire($desire)
    {
        $this->desire = $desire;

        return $this;
    }

    /**
     * Get desire
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
}
