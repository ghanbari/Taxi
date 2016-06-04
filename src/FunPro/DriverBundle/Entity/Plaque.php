<?php

namespace FunPro\DriverBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Plaque
 *
 * @ORM\Table(name="plaque", uniqueConstraints={@ORM\UniqueConstraint(name="plaque_UNIQUE", columns={"first_number", "second_number", "city_number", "area_code"})})
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\PlaqueRepository")
 */
class Plaque
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
     * @var integer
     *
     * @ORM\Column(name="first_number", type="smallint")
     *
     * @Assert\NotBlank(groups={"Create","Update"})
     * @Assert\Length(min="2", max="2", groups={"Create","Update"})
     * @Assert\Type("numeric", groups={"Create","Update"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $firstNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="second_number", type="smallint")
     *
     * @Assert\NotBlank(groups={"Create","Update"})
     * @Assert\Length(min="3", max="3", groups={"Create","Update"})
     * @Assert\Type("numeric", groups={"Create","Update"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $secondNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="city_number", type="smallint")
     *
     * @Assert\NotBlank(groups={"Create","Update"})
     * @Assert\Length(min="2", max="2", groups={"Create","Update"})
     * @Assert\Type("numeric", groups={"Create","Update"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $cityNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="area_code", type="string", length=2)
     *
     * @Assert\NotBlank(groups={"Create","Update"})
     * @Assert\Length(min="1", max="1", groups={"Create","Update"})
     * @Assert\Type("string", groups={"Create","Update"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $areaCode;

    /**
     * @var Car
     *
     * @ORM\OneToOne(targetEntity="FunPro\DriverBundle\Entity\Car", mappedBy="plaque", orphanRemoval=true)
     */
    private $car;

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
     * Set firstNumber
     *
     * @param integer $firstNumber
     * @return Plaque
     */
    public function setFirstNumber($firstNumber)
    {
        $this->firstNumber = $firstNumber;

        return $this;
    }

    /**
     * Get firstNumber
     *
     * @return integer 
     */
    public function getFirstNumber()
    {
        return $this->firstNumber;
    }

    /**
     * Set secondNumber
     *
     * @param integer $secondNumber
     * @return Plaque
     */
    public function setSecondNumber($secondNumber)
    {
        $this->secondNumber = $secondNumber;

        return $this;
    }

    /**
     * Get secondNumber
     *
     * @return integer 
     */
    public function getSecondNumber()
    {
        return $this->secondNumber;
    }

    /**
     * Set cityNumber
     *
     * @param integer $cityNumber
     * @return Plaque
     */
    public function setCityNumber($cityNumber)
    {
        $this->cityNumber = $cityNumber;

        return $this;
    }

    /**
     * Get cityNumber
     *
     * @return integer 
     */
    public function getCityNumber()
    {
        return $this->cityNumber;
    }

    /**
     * Set areaCode
     *
     * @param string $areaCode
     * @return Plaque
     */
    public function setAreaCode($areaCode)
    {
        $this->areaCode = $areaCode;

        return $this;
    }

    /**
     * Get areaCode
     *
     * @return string 
     */
    public function getAreaCode()
    {
        return $this->areaCode;
    }

    /**
     * Set car
     *
     * @param \FunPro\DriverBundle\Entity\Car $car
     * @return Plaque
     */
    public function setCar(\FunPro\DriverBundle\Entity\Car $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get car
     *
     * @return \FunPro\DriverBundle\Entity\Car 
     */
    public function getCar()
    {
        return $this->car;
    }
}
