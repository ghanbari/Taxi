<?php

namespace FunPro\ServiceBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Event\CarEvent;
use FunPro\DriverBundle\Event\FilterMoveEvent;
use FunPro\DriverBundle\Event\FilterSleepEvent;
use FunPro\DriverBundle\Event\FilterWakefulEvent;
use FunPro\DriverBundle\Event\GetMoveCarEvent;
use FunPro\DriverBundle\Event\WakefulEvent;
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
 * @Rest\NamePrefix("fun_pro_service_api_")
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
     *          400= {
     *              "When driver's car is not defined(code: 1)",
     *              "When car's status is not sleep(code: 2)",
     *              "When car is in queue(code: 3)",
     *          },
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\RequestParam(name="latitude", requirements=@Assert\Type(type="numeric"), nullable=false)
     * @Rest\RequestParam(name="longitude", requirements=@Assert\Type(type="numeric"), nullable=false)
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $manager->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));

        if (!$car) {
            $logger->addError('driver\'s car is not defined', array('driverId' => $driver->getId()));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('driver.car.is.not.defined'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($car->getStatus() !== Car::STATUS_SLEEP) {
            $logger->addError('Car\'s status must be sleep', array('car' => $car->getId()));
            $error = array(
                'message' => $translator->trans('car.status.can.be.changed.to.wakeful.only.when.status.is,sleep'),
                'code' => 2,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $lat = $fetcher->get('latitude', true);
        $lon = $fetcher->get('longitude', true);

        $wakeful = new Wakeful($car, new Point($lon, $lat));

        try {
            $manager->persist($wakeful);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_WAKEFUL, new WakefulEvent($wakeful));
            $manager->flush();

            $context = (new Context())
                ->addGroups(array('Public', 'Car', 'Point', 'CarStatus'))
                ->setMaxDepth(true);
            return $this->view($wakeful, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        } catch (UniqueConstraintViolationException $e) {
            $error = array(
                'message' => $translator->trans('this.car.is.in.queue'),
                'code' => 3,
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
     *          400= {
     *              "When driver's car is not defined(code: 1)",
     *              "When car's status is sleep(code: 2)",
     *          },
     *          404="If you aren't in queue(code: 1)",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\RequestParam(name="latitude", requirements=@Assert\Type(type="numeric"), nullable=false)
     * @Rest\RequestParam(name="longitude", requirements=@Assert\Type(type="numeric"), nullable=false)
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function putAction()
    {
        $manager = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $manager->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));

        if (!$car) {
            $logger->addError('driver\'s car is not defined', array('driverId' => $driver->getId()));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('driver.car.is.not.defined'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($car->getStatus() === Car::STATUS_SLEEP) {
            $this->logger->addError('Car\'s status must not be sleep', array('car' => $car->getId()));
            $error = array(
                'message' => $translator->trans('car.status.is.sleep'),
                'code' => 2,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $wakeful = $manager->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if (!is_null($wakeful)) {
            $fetcher = $this->get('fos_rest.request.param_fetcher');
            $lat = $fetcher->get('latitude', true);
            $lon = $fetcher->get('longitude', true);
            $currentLocation = new Point($lon, $lat);
            $previousLocation = $wakeful->getPoint();

            $wakeful->setPoint($currentLocation);

            $movieEvent = new GetMoveCarEvent($wakeful->getCar(), $previousLocation, $currentLocation);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_MOVE, $movieEvent);
            $manager->flush();

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            $error = array(
                'message' => $translator->trans('this.car.is.not.in.queue'),
                'code' => 1,
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
     *          400= {
     *              "When driver's car is not defined(code: 1)",
     *              "When car's status is not wakeful or service_end(code: 2)",
     *          },
     *          404="If you aren't in queue(code: 1)",
     *          403= {
     *              "when you are not a driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction()
    {
        $manger = $this->getDoctrine()->getManager();
        $logger = $this->get('logger');
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $manger->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));

        if (!$car) {
            $logger->addError('driver\'s car is not defined', array('driverId' => $driver->getId()));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('driver.car.is.not.defined'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($car->getStatus() !== Car::STATUS_WAKEFUL or $car->getStatus() !== Car::STATUS_SERVICE_END) {
            $this->logger->addError('Car\'s status must be wakeful or service end', array('car' => $car->getId()));
            $error = array(
                'message' => $translator->trans('car.status.must.be.wakeful.or.end'),
                'code' => 2,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $wakeful = $manger->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if (!is_null($wakeful)) {
            $manger->remove($wakeful);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_SLEEP, new CarEvent($car));
            $manger->flush();

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            $error = array(
                'message' => $translator->trans('this.car.is.not.in.queue'),
                'code' => 1,
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
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getStatusAction()
    {
        $logger = $this->get('logger');
        $translator = $this->get('translator');

        $driver = $this->getUser();
        $car = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));

        if (!$car) {
            $logger->addError('driver\'s car is not defined', array('driverId' => $driver->getId()));
            $error = array(
                'code' => 1,
                'message' => $translator->trans('driver.car.is.not.defined'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $wakeful = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if (!is_null($wakeful)) {
            $context = (new Context())
                ->addGroups(array('Public', 'Car', 'CarStatus', 'Point'))
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

        if ($this->getUser() and $this->getUser() instanceof Driver) {
            $disappear = $this->getUser();
        } else {
            $disappear = null;
        }

        $wakefuls = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->getAllWakefulNearTo($lon, $lat, $this->getParameter('service.visible_radius'), $limit, $disappear);

        if (count($wakefuls)) {
            $context = (new Context())
                ->addGroups(array('Public', 'Car', 'Point', 'Driver'))
                ->setMaxDepth(true);
            return $this->view($wakefuls, Response::HTTP_OK)
                ->setSerializationContext($context);
        } else {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
    }

    public function getAction()
    {
    }
}
