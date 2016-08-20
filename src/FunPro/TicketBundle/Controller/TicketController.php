<?php

namespace FunPro\TicketBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\TicketBundle\Entity\Ticket;
use FunPro\TicketBundle\Form\TicketType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketController
 *
 * @package FunPro\TicketBundle\Controller
 *
 * @Rest\RouteResource(resource="ticket", pluralize=false)
 * @Rest\NamePrefix("fun_pro_ticket_api_")
 */
class TicketController extends FOSRestController
{
    public function getForm(Ticket $ticket)
    {
        $format = $this->get('request')->getRequestFormat('html');
        $options = array(
            'action' => $this->generateUrl('fun_pro_ticket_api_post_ticket'),
            'method' => 'POST',
            'validation_groups' => array('Create'),
            'csrf_protection' => $format === 'html' ?: false,
        );

        $form = $this->createForm(new TicketType(), $ticket, $options);
        return $form;
    }

    /**
     * Create a ticket
     *
     * @ApiDoc(
     *      section="Ticket",
     *      resource=true,
     *      views={"passenger"},
     *      input={
     *          "class"="FunPro\TicketBundle\Form\TicketType",
     *          "data"={
     *              "class"="FunPro\TicketBundle\Entity\Ticket",
     *              "groups"={"Create"},
     *              "parsers"={
     *                  "Nelmio\ApiDocBundle\Parser\ValidationParser",
     *                  "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
     *              },
     *          },
     *      },
     *      output={
     *          "class"="FunPro\TicketBundle\Entity\Ticket",
     *          "groups"={"Owner"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are not passenger",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @Rest\RequestParam(name="data", nullable=true, strict=true)
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction(Request $request)
    {
        $ticket = new Ticket();
        $form = $this->getForm($ticket);
        $form->handleRequest($request);
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        if ($form->isValid()) {
            if ($data = $fetcher->get('data')) {
                $ticket->setData(json_decode($data));
            }
            $this->getDoctrine()->getManager()->persist($ticket);
            $this->getDoctrine()->getManager()->flush();

            return $this->view($ticket, Response::HTTP_CREATED);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }
}
