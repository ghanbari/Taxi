<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use JMS\Serializer\Annotation as JS;

/**
 * DriverLog
 *
 * @ORM\Table(name="driver_log")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\CarLogRepository")
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
     * @ORM\Column(name="status", length=15)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $status;

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

    /**
     * @param Car   $car
     * @param       $status
     * @param Point $point
     */
    public function __construct(Car $car, $status, Point $point = null)
    {
        $this->setCar($car);
        $this->setStatus($status);
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
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
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
     * @param Car $car
     * @return CarLog
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
}
