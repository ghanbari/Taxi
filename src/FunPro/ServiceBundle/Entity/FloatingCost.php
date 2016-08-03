<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FloatingCost
 *
 * @ORM\Table(name="floating_cost")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\FloatingCostRepository")
 */
class FloatingCost
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Cost"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="cost", type="integer")
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Type("numeric", groups={"Create", "Update"})
     *
     * @JS\Groups({"Cost"})
     * @JS\Since("1.0.0")
     */
    private $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="description", length=50)
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(max="50", groups={"Create", "Update"})
     *
     * @JS\Groups({"Cost"})
     * @JS\Since("1.0.0")
     */
    private $description;

    /**
     * @var Service
     *
     * @ORM\ManyToOne(targetEntity="FunPro\ServiceBundle\Entity\Service", inversedBy="floatingCosts")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type("FunPro\ServiceBundle\Entity\Service", groups={"Create", "Update"})
     *
     * @JS\Groups({"Service"})
     * @JS\Since("1.0.0")
     */
    private $service;

    /**
     * @param Service $service
     * @param         $cost
     * @param         $description
     */
    public function __construct(Service $service, $cost, $description)
    {
        $this->service = $service;
        $this->cost = $cost;
        $this->description = $description;
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
     * @return int
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param int $cost
     *
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param Service $service
     *
     * @return $this
     */
    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }
}
