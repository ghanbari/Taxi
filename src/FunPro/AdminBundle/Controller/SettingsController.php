<?php

namespace FunPro\AdminBundle\Controller;

use FOS\RestBundle\Context\Context;
use FunPro\EngineBundle\Utility\DataTable;
use FunPro\FinancialBundle\Entity\BaseCost;
use FunPro\FinancialBundle\Form\BaseCostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SettingsController
 *
 * @Rest\RouteResource(resource="settings", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class SettingsController extends FOSRestController
{
    private function createCreateForm($baseCost)
    {
        return $this->createForm(new BaseCostType(), $baseCost);
    }

    /**
     * Show Drivers
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true, strict=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true, strict=true)
     */
    public function cgetPriceAction(Request $request)
    {
        $baseCost = new BaseCost();
        $form = $this->createCreateForm($baseCost);

        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start');
        $length = $this->get('fos_rest.request.param_fetcher')->get('length');

        $offset = max($offset, 0);
        $length = min($length, $max);

        $queryBuilder = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from('FunProFinancialBundle:BaseCost', 'b');

        DataTable::orderBy($queryBuilder, $request);
        DataTable::filterBy($queryBuilder, $request);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($queryBuilder, floor($offset / $length)+1, $length);

        $context = (new Context())
            ->addGroups(array('Admin', 'Point'))
            ->setMaxDepth(true);
        return $this->view(array(
            "recordsTotal" => $pagination->getTotalItemCount(),
            "recordsFiltered" => $pagination->getTotalItemCount(),
            "data" => $pagination->getItems()
        ))
            ->setSerializationContext($context)
            ->setTemplate("FunProAdminBundle:Settings:cgetPrice.html.twig")
            ->setTemplateData(array('form' => $form->createView()));
    }

    /**
     * @return \FOS\RestBundle\View\View
     */
    public function postAction(Request $request)
    {
        $baseCost = new BaseCost();
        $form = $this->createCreateForm($baseCost);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($baseCost);
            $this->getDoctrine()->getManager()->flush();

            return $this->view(null, Response::HTTP_NO_CONTENT);
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }
}
