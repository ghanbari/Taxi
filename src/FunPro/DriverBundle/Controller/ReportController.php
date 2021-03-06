<?php

namespace FunPro\DriverBundle\Controller;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

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

        # user can only get report for 30 day
        if (($till->getTimestamp() - $from->getTimestamp()) > 2592000) {
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('you.can.get.report.for.30.day'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

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

        # user can only get report for 30 day
        if (($till->getTimestamp() - $from->getTimestamp()) > 2592000) {
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('you.can.get.report.for.30.day'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

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

        # user can only get report for 30 day
        if (($till->getTimestamp() - $from->getTimestamp()) > 2592000) {
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('you.can.get.report.for.30.day'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        try {
            $total = $this->getDoctrine()->getRepository('FunProServiceBundle:Service')
                ->getDriverMileage($this->getUser(), $from, $till);
        } catch (NoResultException $e) {
            $total = 0;
        }

        return $this->view(array('total' => $total), Response::HTTP_OK);
    }
}
