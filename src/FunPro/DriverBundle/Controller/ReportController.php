<?php

namespace FunPro\DriverBundle\Controller;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class ReportController
 *
 * @package FunPro\DriverBundle\Controller
 *
 * @Rest\RouteResource(resource="report", pluralize=false)
 * @Rest\NamePrefix("fun_pro_driver_api_")
 */
class ReportController extends FOSRestController
{
    /**
     * Calculate online time of driver
     *
     * @ApiDoc(
     *      section="Report",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\QueryParam(name="from", requirements=@Assert\Date(), nullable=false, strict=true)
     * @Rest\QueryParam(name="till", requirements=@Assert\Date(), nullable=false, strict=true)
     */
    public function getOnlineTimeAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $from = new \DateTime($fetcher->get('from'));
        $till = new \DateTime($fetcher->get('till'));

        $total = $this->getDoctrine()->getRepository('FunProDriverBundle:CarLog')
            ->getOnlineTime($this->getUser(), $from, $till);

        return $this->view(array('total' => $total), Response::HTTP_OK);
    }

    /**
     * Calculate service time of driver
     *
     * @ApiDoc(
     *      section="Report",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\QueryParam(name="from", requirements=@Assert\Date(), nullable=false, strict=true)
     * @Rest\QueryParam(name="till", requirements=@Assert\Date(), nullable=false, strict=true)
     */
    public function getServiceTimeAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $from = new \DateTime($fetcher->get('from'));
        $till = new \DateTime($fetcher->get('till'));

        $total = $this->getDoctrine()->getRepository('FunProServiceBundle:ServiceLog')
            ->getServiceTime($this->getUser(), $from, $till);

        return $this->view(array('total' => $total), Response::HTTP_OK);
    }

    /**
     * Calculate distance traveled of driver
     *
     * @ApiDoc(
     *      section="Report",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\QueryParam(name="from", requirements=@Assert\Date(), nullable=false, strict=true)
     * @Rest\QueryParam(name="till", requirements=@Assert\Date(), nullable=false, strict=true)
     */
    public function getDistanceAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $from = new \DateTime($fetcher->get('from'));
        $till = new \DateTime($fetcher->get('till'));

        try {
            $total = $this->getDoctrine()->getRepository('FunProDriverBundle:CarLog')
                ->getDistance($this->getUser(), $from, $till);
        } catch (NoResultException $e) {
            $total = 0;
        }

        return $this->view(array('total' => $total), Response::HTTP_OK);
    }

    /**
     * Get Distance, Service time & Online time of driver
     *
     * @ApiDoc(
     *      section="Report",
     *      resource=true,
     *      views={"driver"},
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_DRIVER')")
     *
     * @Rest\QueryParam(name="from", requirements=@Assert\Date(), nullable=false, strict=true)
     * @Rest\QueryParam(name="till", requirements=@Assert\Date(), nullable=false, strict=true)
     */
    public function cgetAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $from = new \DateTime($fetcher->get('from'));
        $till = new \DateTime($fetcher->get('till'));
        $data = array();

        try {
            $data['distance'] = $this->getDoctrine()->getRepository('FunProDriverBundle:CarLog')
                ->getDistance($this->getUser(), $from, $till);
        } catch (NoResultException $e) {
            $data['distance'] = 0;
        }

        $data['service_time'] = $this->getDoctrine()->getRepository('FunProServiceBundle:ServiceLog')
            ->getServiceTime($this->getUser(), $from, $till);

        $data['online_time'] = $this->getDoctrine()->getRepository('FunProDriverBundle:CarLog')
            ->getOnlineTime($this->getUser(), $from, $till);

        return $this->view($data, Response::HTTP_OK);
    }
}
