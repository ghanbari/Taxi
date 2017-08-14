<?php

namespace FunPro\GeoBundle\Service;

use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Service\Base\Location\CoordinateLocation;
use Ivory\GoogleMap\Service\Base\TravelMode;
use Ivory\GoogleMap\Service\Base\UnitSystem;
use Ivory\GoogleMap\Service\Direction\DirectionService;
use Ivory\GoogleMap\Service\Direction\Request\DirectionRequest;

class Direction
{
    /**
     * @var DirectionService
     */
    private $directionService;

    /**
     * @var array
     */
    private $data;
    
    /**
     * Price constructor.
     */
    public function __construct(DirectionService $directionService)
    {
        $this->directionService = $directionService;
        $this->data = [];
    }

    private function getKey(Point $origin, Point $destination)
    {
        return $origin . '->' . $destination;
    }

    /**
     * @param Point $origin
     * @param Point $destination
     * @return array[distance, duration]
     */
    private function calculateDistanceAndDuration(Point $origin, Point $destination)
    {
        if (isset($this->data[$this->getKey($origin, $destination)])) {
            return $this->data[$this->getKey($origin, $destination)];
        }
        
        $request = new DirectionRequest(
            new CoordinateLocation(new Coordinate($origin->getLatitude(), $origin->getLongitude())),
            new CoordinateLocation(new Coordinate($destination->getLatitude(), $destination->getLongitude()))
        );

        $request->setUnitSystem(UnitSystem::METRIC);
        $request->setTravelMode(TravelMode::DRIVING);
        $request->setProvideRouteAlternatives(true);

        #FIXME: if connection to google have problem then crashed and not send notification to drivers
        $response = $this->directionService->route($request);

        $bestRoute = null;
        $routes = $response->getRoutes();

        foreach ($routes as $route) {
            if ($bestRoute === null) {
                $bestRoute = $route;
            } else {
                if (count($route->getLegs()) > 0
                    and count($bestRoute->getLegs()) > 0
                    and $route->getLegs()[0]->getDistance()->getValue() < $bestRoute->getLegs()[0]->getDistance()->getValue()
                ) {
                    $bestRoute = $route;
                }
            }
        }

        if (!$bestRoute) {
            throw new \Exception('route is not found');
        }

        $legs = $bestRoute->getLegs();
        $distance = $legs[0]->getDistance()->getValue();
        $duration = $legs[0]->getDuration()->getValue();

        $this->data[$this->getKey($origin, $destination)] = array('distance' => $distance, 'duration' => $duration);
        return $this->data[$this->getKey($origin, $destination)];
    }

    public function distance(Point $origin, Point $destination, $unit='km', $decimal=1)
    {
        $data = $this->calculateDistanceAndDuration($origin, $destination);

        switch ($unit) {
            case 'km':
                $distance = round($data['distance'] / 1000, $decimal);
                break;
            case 'm':
            default:
                $distance = round($data['distance'], $decimal);
        }

        return $distance;
    }

    public function duration(Point $origin, Point $destination, $unit='m', $decimal=0)
    {
        $data = $this->calculateDistanceAndDuration($origin, $destination);

        switch ($unit) {
            case 'h':
                $duration = round($data['duration'] / 3600, $decimal);
                break;
            case 'm':
                $duration = round($data['duration'] / 60, $decimal);
                break;
            case 's':
            default:
                $duration = round($data['duration'], $decimal);
        }

        return $duration;
    }
}