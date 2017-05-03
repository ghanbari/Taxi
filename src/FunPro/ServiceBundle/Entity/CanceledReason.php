<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CanceledReason
 *
 * @ORM\Table(name="canceled_reason")
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\CanceledReasonRepository")
 */
class CanceledReason
{
    const GROUP_PUBLIC = 0;

    const GROUP_PASSENGER = 1;

    const GROUP_DRIVER = 2;

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
     * @ORM\Column(name="title", type="string")
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(max="255")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(max="500")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     */
    private $groups;

    /**
     * @param     $title
     * @param     $description
     * @param int $groups
     */
    public function __construct($title, $description, $groups = self::GROUP_PUBLIC)
    {
        $this->title = $title;
        $this->description = $description;
        $this->setGroups($groups);
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
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param int $groups
     *
     * @return $this
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
        return $this;
    }
}
