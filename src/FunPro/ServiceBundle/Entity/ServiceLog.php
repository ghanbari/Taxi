<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;

/**
 * ServiceLog
 *
 * @ORM\Table(name="service_log")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\ServiceLogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ServiceLog
{
    const STATUS_REQUESTED = 0;
    const STATUS_ACCEPTED  = 1;
    const STATUS_REJECTED  = 2;
    const STATUS_READY     = 3;
    const STATUS_START     = 4;
    const STATUS_FINISH    = 5;

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
     * @var Service
     *
     * @ORM\ManyToOne(targetEntity="FunPro\ServiceBundle\Entity\Service", inversedBy="logs")
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
     * @param $service
     * @param $status
     */
    public function __construct($service, $status)
    {
        $this->service = $service;
        $this->status = $status;
        $this->atTime = new \DateTime();
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
     * Set status
     *
     * @param string $status
     *
*@return ServiceLog
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
     * @param Service $service
     *
     * @return ServiceLog
     */
    public function setService(Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }
}
