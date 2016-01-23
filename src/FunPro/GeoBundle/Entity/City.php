<?php

namespace FunPro\GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use Gedmo\Mapping\Annotation as Gedmo;

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
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    private $name;

    /**
     * @var Point
     *
     * @ORM\Column(type="point", nullable=true)
     */
    private $point;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeLeft()
     */
    private $lft;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeRight()
     */
    private $rgt;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeLevel()
     */
    private $lvl;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="FunPro\GeoBundle\Entity\City")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="cascade")
     * @Gedmo\TreeParent()
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="FunPro\GeoBundle\Entity\City", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
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
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param point $point
     * @return City
     */
    public function setPoint($point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return point 
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
     * @param \FunPro\GeoBundle\Entity\City $parent
     * @return City
     */
    public function setParent(\FunPro\GeoBundle\Entity\City $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \FunPro\GeoBundle\Entity\City 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param \FunPro\GeoBundle\Entity\City $children
     * @return City
     */
    public function addChild(\FunPro\GeoBundle\Entity\City $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \FunPro\GeoBundle\Entity\City $children
     */
    public function removeChild(\FunPro\GeoBundle\Entity\City $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }
}
