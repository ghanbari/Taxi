<?php

namespace FunPro\PassengerBundle\Controller\Management;

use FOS\RestBundle\Context\Context;
use FunPro\EngineBundle\Utility\DataTable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class PassengerController
 *
 * @package FunPro\PassengerBundle\Controller\Management
 *
 * @Rest\RouteResource(resource="passenger", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class PassengerController extends FOSRestController
{
    /**
     * @param $id
     */
    public function getAction($id)
    {
    }

    /**
     * Show Drivers
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true, strict=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true, strict=true)
     * @Rest\View("FunProPassengerBundle:Management/Passenger:cget.html.twig")
     */
    public function cgetAction(Request $request)
    {
        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start');
        $length = $this->get('fos_rest.request.param_fetcher')->get('length');

        $offset = max($offset, 0);
        $length = min($length, $max);

        $queryBuilder = $this->getDoctrine()->getRepository('FunProPassengerBundle:Passenger')->getQueryBuilder();

        DataTable::orderBy($queryBuilder, $request);
        DataTable::filterBy($queryBuilder, $request);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($queryBuilder, floor($offset / $length)+1, $length);

        $context = (new Context())
            ->addGroup('Admin')
            ->setMaxDepth(1);
        return $this->view(array(
            "recordsTotal" => $pagination->getTotalItemCount(),
            "recordsFiltered" => $pagination->getTotalItemCount(),
            "data" => $pagination->getItems()
        ))->setSerializationContext($context);
    }
}
