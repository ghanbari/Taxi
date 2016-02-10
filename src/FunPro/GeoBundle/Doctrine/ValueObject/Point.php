<?php

namespace FunPro\GeoBundle\Doctrine\ValueObject;

use JMS\Serializer\Annotation as JS;

/**
 * Class Point
 *
 * @package FunPro\GeoBundle\Doctrine\ValueObject
 */
class Point
{
    /**
     * @var float
     *
     * @JS\Groups({"Point"})
     * @JS\Since("1.0.0")
     */
    private $longitude;

    /**
     * @var float
     *
     * @JS\Groups({"Point"})
     * @JS\Since("1.0.0")
     */
    private $latitude;

    /**
     * @param $longitude
     * @param $latitude
     */
    public function __construct($longitude, $latitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function __toString() {
        //Output from this is used with POINT_STR in DQL so must be in specific format
        return sprintf('POINT(%f %f)', $this->longitude, $this->latitude);
    }
} 