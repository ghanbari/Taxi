<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;

/**
 * Log
 *
 * @ORM\Table(name="log")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\LogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Log
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
     * @var string
     *
     * @ORM\Column(length=15)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $status;

    /**
     * @var Requested
     *
     * @ORM\ManyToOne(targetEntity="FunPro\ServiceBundle\Entity\Requested")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", onDelete="cascade")
     *
     * @JS\Groups({"Public"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $service;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Log
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
     * Set atTime
     *
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
     * Set service
     *
     * @param \FunPro\ServiceBundle\Entity\Requested $service
     * @return Log
     */
    public function setService(\FunPro\ServiceBundle\Entity\Requested $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return \FunPro\ServiceBundle\Entity\Requested 
     */
    public function getService()
    {
        return $this->service;
    }
}
