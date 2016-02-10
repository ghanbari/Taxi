<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use JMS\Serializer\Annotation as JS;

/**
 * DriverLog
 *
 * @ORM\Table(name="driver_log")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\DriverLogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CarLog
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
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="cascade")
     *
     * @JS\Groups({"Car"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $car;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="at_time", type="datetime")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $atTime;

    /**
     * @var integer
     *
     * @ORM\Column(name="log", length=15)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $log;

    /**
     * @var Point
     *
     * @ORM\Column(name="point", type="point", nullable=true)
     *
     * @JS\Groups({"Point"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $point;

    public function __construct(Car $car, $log, Point $point=null)
    {
        $this->setCar($car);
        $this->setLog($log);
        $this->setPoint($point);
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
     * Set atTime
     * @ORM\PrePersist()
     */
    public function setAtTime()
    {
        $this->atTime = new \DateTime();
    }

    /**
     * Get atTime
     *
     * @return \DateTime 
     */
    public function getAtTime()
    {
        return $this->atTime;
    }

    /**
     * Set log
     *
     * @param string $log
     * @return CarLog
     */
    public function setLog($log)
    {
        $this->log = $log;

        return $this;
    }

    /**
     * Get log
     *
     * @return string 
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Set point
     *
     * @param point $point
     * @return CarLog
     */
    public function setPoint($point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return point 
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Set car
     *
     * @param \FunPro\DriverBundle\Entity\Car $car
     * @return CarLog
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
