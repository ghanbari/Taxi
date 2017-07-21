<?php

namespace FunPro\FinancialBundle\Controller;

use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseCostController
 * @package FunPro\FinancialBundle\Controller
 * 
 * @Rest\RouteResource("geo/cost")
 * @Rest\NamePrefix("fun_pro_financial_cost_api_")
 */
class BaseCostController extends FOSRestController
{
    /**
     * Get base cost per service in given location
     *
     * @ApiDoc(
     *      section="Price",
     *      resource=true,
     *      output={
     *          "class"="FunPro\FinancialBundle\Entity\Wakeful\BaseCost",
     *          "groups"={"Public", "Point"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          404="When no data is exists",
     *      }
     * )
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @Rest\QueryParam(name="latitude", strict=true, nullable=false)
     * @Rest\QueryParam(name="longitude", strict=true, nullable=false)
     * @Rest\View()
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $latitude = $fetcher->get('latitude');
        $longitude = $fetcher->get('longitude');
        $doctrine = $this->getDoctrine();

        try {
            $context = new Context();
            $context->addGroups(array('Public', 'Point'));
            $cost = $doctrine->getRepository('FunProFinancialBundle:BaseCost')->getLast($longitude, $latitude);
            return $this->view($cost, Response::HTTP_OK)
                ->setSerializationContext($context);
        } catch (NoResultException $e) {
            return $this->view(null, Response::HTTP_NOT_FOUND);
        }
    }
}