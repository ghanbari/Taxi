<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Car
 *
 * @ORM\Table(name="car")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\CarRepository")
 */
class Car
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Driver
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Driver")
     * @ORM\JoinColumn(name="driver_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     */
    private $driver;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     */
    private $plaque;

    /**
     * @var string
     *
     * @ORM\Column(length=15)
     */
    private $color;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date")
     */
    private $born;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=2, scale=1, options={"default"=0})
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $discription;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime")
     */
    private $deletedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="deleted_by", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Blameable(on="change", field="deletedAt")
     */
    private $deletedBy;

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
     * @param string $plaque
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
     * @return string 
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
     * Set discription
     *
     * @param string $discription
     * @return Car
     */
    public function setDiscription($discription)
    {
        $this->discription = $discription;

        return $this;
    }

    /**
     * Get discription
     *
     * @return string 
     */
    public function getDiscription()
    {
        return $this->discription;
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
}
