<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;

/**
 * ServiceLog
 *
 * @ORM\Table(
 *      name="service_log",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="service_log_UNIQUE", columns={"status", "service_id"})}
 * )
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\ServiceLogRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ServiceLog
{
    const STATUS_REQUESTED = 1;
    const STATUS_CANCELED = -1;
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = -2;
    const STATUS_READY = 3;
    const STATUS_START = 4;
    const STATUS_FINISH = 5;
    const STATUS_PAYED = 6;

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
     * @var integer
     *
     * @ORM\Column(type="smallint")
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
     * @param Service $service
     * @param Integer $status
     */
    public function __construct(Service $service, $status)
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
     * Get status
     *
     * @return integer
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
     * @return ServiceLog
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
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
     * Set atTime
     *
     * @ORM\PrePersist()
     */
    public function setAtTime()
    {
        $this->atTime = new \DateTime();
        $this->service->setStatus($this->status);
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
}
