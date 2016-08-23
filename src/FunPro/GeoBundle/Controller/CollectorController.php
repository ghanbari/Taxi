<?php

namespace FunPro\GeoBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\GeoBundle\Entity\IpLocation;
use FunPro\GeoBundle\Form\IpLocationType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CollectorController
 *
 * @package FunPro\GeoBundle\Controller
 *
 * @Rest\RouteResource(resource="collector", pluralize=false)
 * @Rest\NamePrefix("fun_pro_geo_api_")
 */
class CollectorController extends FOSRestController
{
    public function getForm(IpLocation $entity)
    {
        $form = $this->createForm(new IpLocationType(), $entity, array(
            'method' => 'POST',
            'action' => $this->generateUrl('fun_pro_geo_api_post_collector'),
            'validation_groups' => array('Create'),
        ));

        return $form;
    }

    /**
     * collect ip & location for analyze.
     *
     * @ApiDoc(
     *      section="Geo",
     *      resource=true,
     *      input={
     *          "class"="FunPro\GeoBundle\Form\IpLocationType",
     *          "data"={
     *              "class"="FunPro\GeoBundle\Entity\IpLocation",
     *              "groups"={"Public"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\GeoBundle\Entity\IpLocation",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not login",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated()")
     *
     */
    public function postAction(Request $request)
    {
        $location = new IpLocation();
        $location->setReporter($this->getUser());
        $location->setRealIp($request->server->get('REMOTE_ADDR', '0.0.0.0'));

        $form = $this->getForm($location);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($location);
            $this->getDoctrine()->getManager()->flush();

            return $this->view($location, Response::HTTP_CREATED);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }
}
