<?php

namespace FunPro\UserBundle\Controller\Management;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\EngineBundle\Utility\DataTable;
use FunPro\UserBundle\Entity\Operator;
use FunPro\UserBundle\Entity\User;
use FunPro\UserBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OperatorController
 *
 * @Rest\RouteResource(resource="operator", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 *
 * @package FunPro\UserBundle\Controller\Management
 */
class OperatorController extends FOSRestController
{
    public function getForm(Operator $operator, $method)
    {
        $options = ['method' => $method];
        $options['action'] = strtolower($method) === 'post' ?
            $this->generateUrl('fun_pro_admin_post_operator') : $this->generateUrl('fun_pro_admin_put_operator', array('id' => $operator->getId()));
        return $this->createForm(UserType::class, $operator, $options);
    }

    /**
     * show form
     *
     * @Rest\View("FunProUserBundle:Management/Operator:new.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newAction()
    {
        $operator = new Operator();
        $form = $this->getForm($operator, 'post');
        return $form;
    }

    /**
     * create operator
     *
     * @Security("is_authenticated() or has_role('ROLE_ADMIN')")
     *
     * @Rest\View("FunProUserBundle:Management/Operator:new.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $operator = new Operator();
        $form = $this->getForm($operator, 'post');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($operator);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', $this->get('translator')->trans('operator.created'));
            return $this->routeRedirectView('fun_pro_admin_cget_operator');
        }

        return $form;
    }

    /**
     * show form for edit
     *
     * @Security("is_authenticated() or has_role('ROLE_ADMIN')")
     * @ParamConverter(name="operator", class="FunProUserBundle:Operator")
     *
     * @Rest\View("FunProUserBundle:Management/Operator:new.html.twig")
     *
     * @param $id
     *
     * @return \Symfony\Component\Form\Form
     */
    public function editAction($id, $operator)
    {
        $form = $this->getForm($operator, 'put');
        return $form;
    }

    /**
     * create operator
     *
     * @Security("is_authenticated() or has_role('ROLE_ADMIN')")
     * @ParamConverter(name="operator", class="FunProUserBundle:Operator")
     *
     * @Rest\View("FunProUserBundle:Management/Operator:new.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function putAction(Request $request, $id, $operator)
    {
        $form = $this->getForm($operator, 'put');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($operator);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', $this->get('translator')->trans('operator.created'));
            return $this->routeRedirectView('fun_pro_admin_cget_operator');
        }

        return $form;
    }

    /**
     * Show Operators
     *
     * @Security("is_authenticated() and has_role('ROLE_ADMIN')")
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true, strict=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true, strict=true)
     * @Rest\View("FunProUserBundle:Management/Operator:cget.html.twig")
     */
    public function cgetAction(Request $request)
    {
        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start');
        $length = $this->get('fos_rest.request.param_fetcher')->get('length');

        $offset = max($offset, 0);
        $length = min($length, $max);

        $queryBuilder = $this->getDoctrine()->getRepository('FunProUserBundle:User')->getQueryBuilder();
        $queryBuilder->where('u INSTANCE OF ' . Operator::class);

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
