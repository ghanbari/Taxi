<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\DriverBundle\Entity\Car;
use JMS\Serializer\Annotation as JS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * PropagationList
 *
 * @ORM\Table(name="propagation_list")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\PropagationListRepository")
 */
class PropagationList
{
    const ANSWER_ACCEPTED = 1;
    const ANSWER_REJECTED = 2;

    const NOTIFY_NOT_SEND = 0;
    const NOTIFY_SEND = 1;
    const NOTIFY_DELIVER = 2;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Service
     *
     * @ORM\ManyToOne(targetEntity="FunPro\ServiceBundle\Entity\Service", inversedBy="propagationList")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $service;

    /**
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="restrict", nullable=false)
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $car;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $number;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $answer;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $notifyStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @JS\Groups({"Passenger", "Agent", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $updatedAt;

    public function __construct($service, $car, $number)
    {
        $this->setService($service);
        $this->setCar($car);
        $this->setNumber($number);
        $this->setNotifyStatus(self::NOTIFY_NOT_SEND);
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
     * Set number
     *
     * @param integer $number
     * @return PropagationList
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set answer
     *
     * @param integer $answer
     * @return PropagationList
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * Get answer
     *
     * @return integer
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set notifyStatus
     *
     * @param integer $notifyStatus
     * @return PropagationList
     */
    public function setNotifyStatus($notifyStatus)
    {
        $this->notifyStatus = $notifyStatus;

        return $this;
    }

    /**
     * Get notifyStatus
     *
     * @return integer
     */
    public function getNotifyStatus()
    {
        return $this->notifyStatus;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return PropagationList
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return PropagationList
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set service
     *
     * @param Service $service
     *
     * @return PropagationList
     */
    public function setService(Service $service)
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

    /**
     * Set car
     *
     * @param Car $car
     * @return PropagationList
     */
    public function setCar(Car $car)
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
