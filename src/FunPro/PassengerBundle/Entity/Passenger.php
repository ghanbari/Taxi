<?php

namespace FunPro\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;

/**
 * Passenger
 *
 * @ORM\Table(name="passenger")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\PassengerRepository")
 */
class Passenger extends User
{
    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=11, unique=true) #TODO: mobile & disc
     */
    protected $mobile;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="referer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $refrerer;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="decimal", precision=2, scale=1, options={"default"=0})
     */
    private $rate;

    public function __construct()
    {
        parent::__construct();
        $this->rate = 0;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return Passenger
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
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
     * Set rate
     *
     * @param string $rate
     * @return Passenger
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
     * Set refrerer
     *
     * @param \FunPro\PassengerBundle\Entity\Passenger $refrerer
     * @return Passenger
     */
    public function setRefrerer(\FunPro\PassengerBundle\Entity\Passenger $refrerer = null)
    {
        $this->refrerer = $refrerer;

        return $this;
    }

    /**
     * Get refrerer
     *
     * @return \FunPro\PassengerBundle\Entity\Passenger 
     */
    public function getRefrerer()
    {
        return $this->refrerer;
    }
}
