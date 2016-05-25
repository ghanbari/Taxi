<?php

namespace FunPro\ServiceBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Event\FilterMoveEvent;
use FunPro\DriverBundle\Event\FilterSleepEvent;
use FunPro\DriverBundle\Event\FilterWakefulEvent;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\ServiceBundle\Entity\Wakeful;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\RestBundle\Context\Context;

/**
 * Class WakefulController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource(resource="wakeful", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 */
class WakefulController extends FOSRestController
{
    /**
     * Add a driver to wakeful queue
     *
     * @ApiDoc(
     *      section="Wakeful",
     *      resource=true,
     *      views={"driver"},
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Wakeful",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400="When you are in queue and try again",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_DRIVER')")
     *
     * @Rest\RequestParam(name="latitude", requirements=@Assert\Type(type="numeric"), description="latitude of driver coordinate")
     * @Rest\RequestParam(name="longitude", requirements=@Assert\Type(type="numeric"), description="longitude of driver coordinate")
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction()
    {
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));

        if ($car->getStatus() != Car::STATUS_SLEEP) {
            $error = array(
                'message' => $translator->trans('car.status.only.can.be.changed.to.wakeful.when.status.is,sleep'),
                'code' => 1,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $fether = $this->get('fos_rest.request.param_fetcher');
        $lat = $fether->get('latitude', true);
        $lon = $fether->get('longitude', true);

        $wakeful = new Wakeful($car, new Point($lon, $lat));

        try {
            $this->getDoctrine()->getManager()->persist($wakeful);

            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_WAKEFUL, new FilterWakefulEvent($wakeful));

            $context = (new Context())
                ->addGroup('Public')
                ->addGroup('Car')
                ->addGroup('Point')
                ->addGroup('CarStatus')
                ->setMaxDepth(true);
            return $this->view($wakeful, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        } catch (UniqueConstraintViolationException $e) {
            $error = array(
                'message' => $translator->trans('this.car.is.in.queue'),
                'code' => $e->getCode(),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update location of a car
     *
     * @ApiDoc(
     *      section="Wakeful",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          204="When success",
     *          404="If you aren't in queue",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_DRIVER')")
     *
     * @Rest\RequestParam(name="latitude", requirements=@Assert\Type(type="numeric"), description="latitude of driver coordinate")
     * @Rest\RequestParam(name="longitude", requirements=@Assert\Type(type="numeric"), description="longitude of driver coordinate")
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function putAction()
    {
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));
        $wakeful = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        $status = $car->getStatus();
        if ($status != Car::STATUS_WAKEFUL and $status != Car::STATUS_SERVICE_PREPARE and $status != Car::STATUS_SERVICE_IN) {
            $error = array(
                'message' => $translator->trans('car.status.must.be.wakeful.prepare.or.in.service'),
                'code' => 0,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if (!is_null($wakeful)) {
            $fether = $this->get('fos_rest.request.param_fetcher');
            $lat = $fether->get('latitude', true);
            $lon = $fether->get('longitude', true);
            $currentLocation = new Point($lon, $lat);
            $previousLocation = $wakeful->getPoint();

            $wakeful->setPoint($currentLocation);

            $movieEvent = new FilterMoveEvent($wakeful->getCar(), $previousLocation, $currentLocation);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_MOVE, $movieEvent);

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            $error = array(
                'message' => $translator->trans('this.car.is.not.in.queue'),
                'code' => 0,
            );
            return $this->view($error, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Remove a car from queue.
     *
     * @ApiDoc(
     *      section="Wakeful",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          204="When success",
     *          400="When your status is not wakeful or sleep",
     *          404="If you aren't in queue",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_DRIVER')")
     *
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction()
    {
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));
        $wakeful = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if ($car->getStatus() != Car::STATUS_WAKEFUL and $car->getStatus() != Car::STATUS_SLEEP) {
            $error = array(
                'message' => $translator->trans('car.status.must.be.wakeful'),
                'code' => 0,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if (!is_null($wakeful)) {
            $car->setWakeful(null);
            $this->getDoctrine()->getManager()->remove($wakeful);

            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_SLEEP, new FilterSleepEvent($car));

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            $error = array(
                'message' => $translator->trans('this.car.is.not.in.queue'),
                'code' => 0,
            );
            return $this->view($error, Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Detect status of driver.
     *
     * @ApiDoc(
     *      section="Wakeful",
     *      resource=true,
     *      views={"driver"},
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Wakeful",
     *          "groups"={"Public", "Car", "CarStatus", "Point"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When driver is in queue",
     *          204="When driver isn't in queue",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated() and has_role('ROLE_DRIVER')")
     *
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getStatusAction()
    {
        $driver = $this->getUser();
        $car = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));
        $wakeful = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if (!is_null($wakeful)) {
            $context = (new Context())
                ->addGroup('Public')
                ->addGroup('Car')
                ->addGroup('CarStatus')
                ->addGroup('Point')
                ->setMaxDepth(true);
            return $this->view($wakeful, Response::HTTP_OK)
                ->setSerializationContext($context);
        } else {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Get a list from wakeful cars.
     *
     * @ApiDoc(
     *      section="Wakeful",
     *      resource=true,
     *      views={"driver"},
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Wakeful",
     *          "groups"={"Public", "Car", "Point"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          204="When no data is exists",
     *      }
     * )
     *
     * @Rest\QueryParam(name="latitude", strict=true, requirements=@Assert\Type(type="numeric"), description="latitude of driver coordinate")
     * @Rest\QueryParam(name="longitude", strict=true, requirements=@Assert\Type(type="numeric"), description="longitude of driver coordinate")
     * @Rest\QueryParam(name="limit", default=500, requirements=@Assert\Range(max="500"), description="Maximum of result")
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $fether = $this->get('fos_rest.request.param_fetcher');
        $lat = $fether->get('latitude', true);
        $lon = $fether->get('longitude', true);
        $limit = intval($fether->get('limit', true));

        $wakefuls = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->getAllWakefulNearTo($lon, $lat, 2, $limit);

        if (count($wakefuls)) {
            $context = (new Context())
                ->addGroup('Public')
                ->addGroup('Car')
                ->addGroup('Point')
                ->addGroup('Driver')
                ->setMaxDepth(true);
            return $this->view($wakefuls, Response::HTTP_OK)
                ->setSerializationContext($context);
        } else {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
    }

    public function getAction() {}
}
