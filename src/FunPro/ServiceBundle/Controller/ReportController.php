<?php

namespace FunPro\ServiceBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\PassengerBundle\Entity\Passenger;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReportController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource("report", pluralize=false)
 * @Rest\NamePrefix("fun_pro_service_api_")
 */
class ReportController extends FOSRestController
{
    public function getAction($id)
    {
    }

    /**
     * Get Service filtered & pagination
     *
     * @ApiDoc(
     *      section="Service",
     *      resource=true,
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Service",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are a user and you are login in currently",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_DRIVER')")
     *
     * @Rest\QueryParam(name="from", requirements=@Assert\Date(), nullable=true, strict=true)
     * @Rest\QueryParam(name="till", requirements=@Assert\Date(), nullable=true, strict=true)
     * @Rest\QueryParam(name="origin_lat", requirements=@Assert\Type("numeric"), nullable=true, strict=true)
     * @Rest\QueryParam(name="origin_long", requirements=@Assert\Type("numeric"), nullable=true, strict=true)
     * @Rest\QueryParam(name="destination_lat", requirements=@Assert\Type("numeric"), nullable=true, strict=true)
     * @Rest\QueryParam(name="destination_long", requirements=@Assert\Type("numeric"), nullable=true, strict=true)
     * @Rest\QueryParam(name="driver", requirements="\d+", nullable=true, strict=true, description="only for passenger")
     * @Rest\QueryParam(name="offset", requirements="\d+", nullable=true, strict=true, default="0")
     * @Rest\QueryParam(name="limit", requirements="\d+", nullable=true, strict=true, default="10")
     *
     */
    public function cgetAction()
    {
        $context = new Context();
        $context->addGroups(array('Public', 'Point', 'Cost'));
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $limit = $fetcher->get('limit');
        $offset = $fetcher->get('offset');

        $limit = min($limit, 20);
        $offset = max($offset, 0);

        $from = $fetcher->get('from') ? new \DateTime($fetcher->get('from')) : null;
        $till = $fetcher->get('till') ? new \DateTime($fetcher->get('till')) : null;

        $originLat = $fetcher->get('origin_lat');
        $originLong = $fetcher->get('origin_long');
        $origin = (!is_null($originLat) and !is_null($originLong)) ? new Point($originLong, $originLat) : null;

        $destLat  = $fetcher->get('destination_lat');
        $destLong = $fetcher->get('destination_long');
        $destination = (!is_null($destLat) and !is_null($destLong)) ? new Point($destLong, $destLat) : null;

        if ($this->getUser() instanceof Passenger) {
            $context->addGroups(array('Car', 'DriverInfo', 'Plaque', 'PropagationList'));
            $services = $this->getDoctrine()->getRepository('FunProServiceBundle:Service')->getPassengerServiceFilterBy(
                $this->getUser(),
                $fetcher->get('driver'),
                $origin,
                $destination,
                $from,
                $till,
                $limit,
                $offset
            );
        } else {
            $context->addGroup('Driver');
            $services = $this->getDoctrine()->getRepository('FunProServiceBundle:Service')->getDriverServiceFilterBy(
                $this->getUser(),
                $origin,
                $destination,
                $from,
                $till,
                $limit,
                $offset
            );
        }

        $statusCode = empty($services) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        return $this->view($services, $statusCode)
            ->setSerializationContext($context);
    }
}
