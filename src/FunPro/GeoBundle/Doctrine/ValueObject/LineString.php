<?php

namespace FunPro\GeoBundle\Doctrine\ValueObject;

class LineString extends \SplObjectStorage
{
    public function __toString()
    {
        $linestring = 'LineString(';

        /** @var Point $point */
        foreach ($this as $point) {
            $linestring .= $point->getLongitude() . ' ' . $point->getLatitude() . ', ';
        }

        $linestring = substr($linestring, 0, strrpos($linestring, ',')) . ')';

        return $linestring;
    }
} 