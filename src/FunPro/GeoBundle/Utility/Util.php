<?php

namespace FunPro\GeoBundle\Utility;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;

/**
 * Class Util
 *
 * @package FunPro\GeoBundle\Utility
 */
class Util
{
    /**
     * @param LineString $lineString
     *
     * @return float|int
     */
    public static function lengthOfLineString(LineString $lineString)
    {
        $length = 0;
        $count = count($lineString->toArray());
        for ($counter = 0; $counter < $count; $counter++) {
            if (($counter + 1) != $count) {
                $length += self::distance(
                    $lineString->getPoint($counter)->getLatitude(),
                    $lineString->getPoint($counter)->getLongitude(),
                    $lineString->getPoint($counter + 1)->getLatitude(),
                    $lineString->getPoint($counter + 1)->getLongitude()
                );
            }
        }

        return $length * 1000;
    }

    /**
     *  This routine calculates the distance between two points (given the
     *  latitude/longitude of those points). It is being used to calculate
     *  the distance between two locations using GeoDataSource(TM) Products
     *
     *  Definitions:
     *    South latitudes are negative, east longitudes are positive
     *
     *  Passed to function:
     *    startPointLat, startPointLon = Latitude and Longitude of point 1 (in decimal degrees)
     *    endPointLat, endPointLon = Latitude and Longitude of point 2 (in decimal degrees)
     *    unit = the unit you desire for results
     *           where: 'M' is statute miles (default)
     *                  'K' is kilometers
     *                  'N' is nautical miles
     *  Worldwide cities and other features databases with latitude longitude
     *  are available at http://www.geodatasource.com
     *
     *  For enquiries, please contact sales@geodatasource.com
     *
     *  Official Web site: http://www.geodatasource.com
     *
     *         GeoDataSource.com (C) All Rights Reserved 2015
     *
     * @param float  $startPointLat
     * @param float  $startPointLon
     * @param float  $endPointLat
     * @param float  $endPointLon
     * @param string $unit
     *
     * @return float
     */
    public static function distance($startPointLat, $startPointLon, $endPointLat, $endPointLon, $unit = 'K')
    {
        $theta = $startPointLon - $endPointLon;
        $dist = sin(deg2rad($startPointLat)) * sin(deg2rad($endPointLat)) + cos(deg2rad($startPointLat))
            * cos(deg2rad($endPointLat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit === 'K') {
            return ($miles * 1.609344);
        } elseif ($unit === 'N') {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}
