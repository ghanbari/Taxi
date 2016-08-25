<?php

namespace FunPro\GeoBundle\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\Point as SPoint;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;

/**
 * City
 *
 * @ORM\Table(name="city")
 * @ORM\Entity(repositoryClass="FunPro\GeoBundle\Repository\CityRepository")
 *
 * @Gedmo\Tree(type="nested")
 */
class City
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
     * @ORM\Column()
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $name;

    /**
     * @var SPoint
     *
     * @ORM\Column(type="point", nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $point;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeLeft()
     *
     * @JS\Exclude()
     */
    private $lft;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeRight()
     *
     * @JS\Exclude()
     */
    private $rgt;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeLevel()
     *
     * @JS\Exclude()
     */
    private $lvl;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="FunPro\GeoBundle\Entity\City", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="cascade")
     * @Gedmo\TreeParent()
     *
     * @JS\Groups({"Parent"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="FunPro\GeoBundle\Entity\City", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     *
     * @JS\Groups({"Children"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $children;

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
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return City
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set point
     *
     * @param SPoint $point
     * @return City
     */
    public function setPoint(SPoint $point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return SPoint
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     * @return City
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return City
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     * @return City
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set parent
     *
     * @param City $parent
     * @return City
     */
    public function setParent(City $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return City
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param City $children
     * @return City
     */
    public function addChild(City $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param City $children
     */
    public function removeChild(City $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }
}
