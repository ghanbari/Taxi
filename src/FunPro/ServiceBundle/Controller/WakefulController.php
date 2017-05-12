<?php

namespace FunPro\ServiceBundle\Controller;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\DriverBundle\CarEvents;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Event\CarEvent;
use FunPro\DriverBundle\Event\GetMoveCarEvent;
use FunPro\DriverBundle\Event\WakefulEvent;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\ServiceBundle\Entity\Wakeful;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\VarDumper\VarDumper;

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
     *          "groups"={"Public", "Car", "CarStatus", "Point"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400= {
     *              "When driver's car is not defined(code: 1)",
     *              "When car's status is not sleep(code: 2)",
     *              "When car is in queue(code: 3)",
     *              "Coordinate is blank or zero(code: 4)",
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
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $lat = $fetcher->get('latitude', true);
        $lon = $fetcher->get('longitude', true);

        if (empty($lon) or empty($lat)) {
            $this->get('logger')->addWarning('coordinate is null');
            $error = array(
                'code' => 4,
                'message' => $this->get('translator')->trans('coordinate.is.null'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

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

        $wakeful = new Wakeful($car, new Point($lon, $lat));

        try {
            $manager->persist($wakeful);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_WAKEFUL, new WakefulEvent($wakeful));
            $manager->flush();

            $logger->addInfo('driver\'s status change to wakeful');

            $context = (new Context())
                ->addGroups(array('Public', 'Car', 'Point', 'CarStatus'))
                ->setMaxDepth(true);
            return $this->view($wakeful, Response::HTTP_CREATED)
                ->setSerializationContext($context);
        } catch (UniqueConstraintViolationException $e) {
            $logger->addError('Your status are wakeful, why do you try again?', array('driverId' => $driver->getId()));
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
     *              "Coordinate is blank or zero(code: 3)",
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
        $serializer = $this->get('jms_serializer');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $lat = $fetcher->get('latitude', true);
        $lon = $fetcher->get('longitude', true);

        if (empty($lon) or empty($lat)) {
            $this->get('logger')->addWarning('coordinate is null');
            $error = array(
                'code' => 3,
                'message' => $this->get('translator')->trans('coordinate.is.null'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

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
            $logger->addError('Car\'s status must not be sleep', array('car' => $car->getId()));
            $error = array(
                'message' => $translator->trans('car.status.is.sleep'),
                'code' => 2,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $wakeful = $manager->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if (!is_null($wakeful)) {
            $currentLocation = new Point($lon, $lat);
            $previousLocation = $wakeful->getPoint();

            $wakeful->setPoint($currentLocation);

            $context = SerializationContext::create()->setGroups(array('Point'));
            $logger->addInfo('car is moving', array(
                'previous location' => $serializer->serialize($previousLocation, 'json', clone $context),
                'current location' => $serializer->serialize($currentLocation, 'json', clone $context),
            ));

            $movieEvent = new GetMoveCarEvent($wakeful->getCar(), $previousLocation, $currentLocation);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_MOVE, $movieEvent);
            $manager->flush();

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            $logger->addError('Why you are not wakeful?', array('carId' => $car->getId()));
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

        if ($car->getStatus() !== Car::STATUS_WAKEFUL and $car->getStatus() !== Car::STATUS_SERVICE_END) {
            $logger->addWarning('Car\'s status must be wakeful or service end', array('car' => $car->getId()));
            $error = array(
                'message' => $translator->trans('car.status.must.be.wakeful.or.end'),
                'code' => 2,
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $wakeful = $manager->getRepository('FunProServiceBundle:Wakeful')
            ->findOneByCar($car);

        if (!is_null($wakeful)) {
            $manager->remove($wakeful);
            $this->get('event_dispatcher')->dispatch(CarEvents::CAR_SLEEP, new CarEvent($car));
            $manager->flush();

            $logger->addInfo('driver go sleep');

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            $logger->addError('Why you are not wakeful?', array('carId' => $car->getId()));
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
     *          400={
     *              "When driver's car is not defined(code: 1)",
     *          },
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

        $wakeful = $manager->getRepository('FunProServiceBundle:Wakeful')
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
     *          400="Coordinate is blank or zero(code: 1)",
     *          403="when you are not login",
     *      }
     * )
     *
     * @Security("is_authenticated()")
     *
     * @Rest\QueryParam(name="latitude", strict=true, requirements=@Assert\Type(type="numeric"), nullable=false)
     * @Rest\QueryParam(name="longitude", strict=true, requirements=@Assert\Type(type="numeric"), nullable=false)
     * @Rest\QueryParam(name="limit", default=20, requirements=@Assert\Range(max="50"), description="Max of result")
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $lat = $fetcher->get('latitude', true);
        $lon = $fetcher->get('longitude', true);
        $limit = intval($fetcher->get('limit', true));

        if (empty($lon) or empty($lat)) {
            $this->get('logger')->addWarning('coordinate is null');
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('coordinate.is.null'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if ($this->getUser() and $this->getUser() instanceof Driver) {
            $disappear = $this->getUser();
        } else {
            $disappear = null;
        }

        $wakefulList = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->getAllFreeAndNearWakeful($lon, $lat, $this->getParameter('service.visible_radius'), $limit, $disappear);

        if (count($wakefulList)) {
            $context = (new Context())
                ->addGroups(array('Public', 'Car', 'Point', 'Driver'))
                ->setMaxDepth(true);
            return $this->view($wakefulList, Response::HTTP_OK)
                ->setSerializationContext($context);
        } else {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
    }

    public function getAction()
    {
    }
}
