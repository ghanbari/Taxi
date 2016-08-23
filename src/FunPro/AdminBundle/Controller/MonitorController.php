<?php

namespace FunPro\AdminBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MonitorController
 *
 * @package FunPro\AdminBundle\Controller
 *
 * @Rest\RouteResource(resource="monitor", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class MonitorController extends FOSRestController
{
    /**
     * Show map
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Rest\View("FunProAdminBundle:Monitor:map.html.twig")
     */
    public function mapAction()
    {
    }

    /**
     * Get a list from wakeful cars.
     *
     * @ApiDoc(
     *      section="Monitor",
     *      resource=true,
     *      views={"admin"},
     *      output={
     *          "class"="FunPro\ServiceBundle\Entity\Wakeful",
     *          "groups"={"Public", "Car", "Point"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          204="When no data is exists",
     *          403="when you are not login",
     *      }
     * )
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\QueryParam(name="latitude", strict=true, nullable=false)
     * @Rest\QueryParam(name="longitude", strict=true, nullable=false)
     * @Rest\QueryParam(name="distance", default=20000, description="Max of result")
     * @Rest\QueryParam(name="limit", default=20, description="Max of result")
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function cgetWakefulAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $lat = $fetcher->get('latitude', true);
        $lon = $fetcher->get('longitude', true);
        $distance = $fetcher->get('distance', true);
        $limit = intval($fetcher->get('limit', true));

        $wakefulList = $this->getDoctrine()->getRepository('FunProServiceBundle:Wakeful')
            ->getAllNearWakeful($lon, $lat, $distance, $limit);

        if (count($wakefulList)) {
            $context = (new Context())
                ->addGroups(array('Public', 'Car', 'Point', 'Driver', 'Admin'))
                ->setMaxDepth(true);
            return $this->view($wakefulList, Response::HTTP_OK)
                ->setSerializationContext($context);
        } else {
            return $this->view(null, Response::HTTP_NO_CONTENT);
        }
    }
}
