<?php

namespace FunPro\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Interfaces\SMSInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JS;

/**
 * Passenger
 *
 * @ORM\Table(name="passenger")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\PassengerRepository")
 *
 * @UniqueEntity("mobile", groups={"Register"})
 */
class Passenger extends User implements SMSInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=11, unique=true)
     *
     * @Assert\NotBlank(groups={"Register", "Profile"})
     * @Assert\Regex(pattern="/09\d{9}/", groups={"Register", "Profile"})
     *
     * @JS\Groups({"PassengerMobile", "Profile"})
     * @JS\Since("1.0.0")
     */
    protected $mobile;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="referer_id", referencedColumnName="id", onDelete="Restrict")
     *
     * @Assert\Type(type="FunPro\PassengerBundle\Entity\Passenger", groups={"Register", "Profile"})
     *
     * @JS\Groups({"Referrer", "Profile"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $referrer;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="decimal", precision=2, scale=1, options={"default"=0})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $rate;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
     * @JS\Exclude()
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="token_requested_at", type="datetime", nullable=true)
     *
     * @JS\Exclude()
     */
    private $tokenRequestedAt;

    public function __construct()
    {
        parent::__construct();
        $this->addRole(self::ROLE_PASSENGER);
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
     * Set referrer
     *
     * @param \FunPro\PassengerBundle\Entity\Passenger $referrer
     * @return Passenger
     */
    public function setReferrer(\FunPro\PassengerBundle\Entity\Passenger $referrer = null)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Get referrer
     *
     * @return \FunPro\PassengerBundle\Entity\Passenger 
     */
    public function getReferrer()
    {
        return $this->referrer;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
        $this->setTokenRequestedAt(new \DateTime());
    }

    /**
     * @return \DateTime
     */
    public function getTokenRequestedAt()
    {
        return $this->tokenRequestedAt;
    }

    /**
     * @param \DateTime $tokenRequestedAt
     */
    public function setTokenRequestedAt($tokenRequestedAt)
    {
        $this->tokenRequestedAt = $tokenRequestedAt;
    }
}
