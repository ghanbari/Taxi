<?php

namespace FunPro\DriverBundle\Controller;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\DriverBundle\Entity\Driver;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileController
 *
 * @package FunPro\DriverBundle\Controller
 *
 * @Rest\RouteResource(resource="profile", pluralize=false)
 * @Rest\NamePrefix("fun_pro_driver_api_")
 */
class ProfileController extends FOSRestController
{
    /**
     * Get Distance, Service time & Online time of driver
     *
     * @ApiDoc(
     *      section="Profile",
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
        /** @var Driver $driver */
        $driver = $this->getUser();
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $from = new \DateTime($fetcher->get('from'));
        $till = new \DateTime($fetcher->get('till'));
        $data = array();

        # user can only get report for 30 day
        if (($till->getTimestamp() - $from->getTimestamp()) > 2592000) {
            $error = array(
                'code' => 1,
                'message' => $this->get('translator')->trans('you.can.get.report.for.30.day'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        try {
            $data['distance'] = $this->getDoctrine()->getRepository('FunProDriverBundle:CarLog')
                ->getDistance($driver, $from, $till);
        } catch (NoResultException $e) {
            $data['distance'] = 0;
        }

        $data['service_time'] = $this->getDoctrine()->getRepository('FunProServiceBundle:ServiceLog')
            ->getServiceTime($driver, $from, $till);
        $data['service_time'] = gmdate('H:i', $data['service_time']);

        $data['online_time'] = $this->getDoctrine()->getRepository('FunProDriverBundle:CarLog')
            ->getOnlineTime($driver, $from, $till);
        $data['online_time'] = gmdate('H:i', $data['online_time']);

        $data['service_count'] = $this->getDoctrine()->getRepository('FunProServiceBundle:Service')
            ->getServiceCountOfDriver($driver, $from, $till);

        $car = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')
            ->findOneBy(array('driver' => $driver, 'current' => true));

//        $currency = $this->getDoctrine()->getRepository('FunProFinancialBundle:Currency')->findOneByCode('IRR');
//        $wallet = $this->getDoctrine()->getRepository('FunProFinancialBundle:Wallet')
//            ->getUserWallet($driver, $currency);

        $data['avatar'] = $driver->getAvatarPath();
        $data['name'] = $driver->getName();
        $data['car_model'] = $car->getType();
        $data['car_brand'] = $car->getType();
        $data['wallet'] = $driver->getCredit();

        return $this->view($data, Response::HTTP_OK);
    }
}
