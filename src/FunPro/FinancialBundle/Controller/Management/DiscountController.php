<?php

namespace FunPro\FinancialBundle\Controller\Management;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\EngineBundle\Utility\DataTable;
use FunPro\FinancialBundle\Entity\DiscountCode;
use FunPro\FinancialBundle\Form\DiscountCodeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class DiscountController
 * @package FunPro\FinancialBundle\Controller\Management
 *
 * @Rest\RouteResource(resource="discount", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class DiscountController extends FOSRestController
{
    /**
     * @param DiscountCode $code
     * @param bool $update
     * @return \Symfony\Component\Form\Form
     */
    private function getForm(DiscountCode $code, $update=false)
    {
        $options['method'] = $update ? 'put' : 'post';
        $options['validation_groups'] = $update ? array('Update', 'Point') : array('Create', 'Point');
        $options['action'] = $update ? $this->generateUrl('fun_pro_admin_put_discount', array('id' => $code->getId()))
            : $this->generateUrl('fun_pro_admin_post_discount');

        $form = $this->createForm(DiscountCodeType::class, $code, $options);
        return $form;
    }

    /**
     * show form for create
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Rest\View(template="FunProFinancialBundle:Management:newCode.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newAction()
    {
        $code = new DiscountCode();
        $form = $this->getForm($code);

        return $form;
    }

    /**
     * create discount code
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Rest\View(template="FunProFinancialBundle:Management:newCode.html.twig")
     * 
     * @param Request $request
     * @return \Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $translator = $this->get('translator');
        $code = new DiscountCode();
        $form = $this->getForm($code);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($code);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', $translator->trans('operation.done'));
            return $this->routeRedirectView('fun_pro_admin_cget_discount');
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * show edit form
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Rest\View(template="FunProFinancialBundle:Management:newCode.html.twig")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\Form\Form
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $code = $em->find('FunProFinancialBundle:DiscountCode', $id);
        $form = $this->getForm($code, true);

        return $form;
    }

    /**
     * show edit form
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Rest\View(template="FunProFinancialBundle:Management:newCode.html.twig")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\Form\Form
     */
    public function putAction(Request $request, $id)
    {
        $translator = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $code = $em->find('FunProFinancialBundle:DiscountCode', $id);
        $form = $this->getForm($code, true);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', $translator->trans('operation.done'));
            return $this->routeRedirectView('fun_pro_admin_cget_discount');
        }
        VarDumper::dump($form->isValid());
        VarDumper::dump($form->getErrors(true));

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true, strict=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true, strict=true)
     * @Rest\View(template="FunProFinancialBundle:Management:cgetDiscount.html.twig")
     */
    public function cgetAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start');
        $length = $this->get('fos_rest.request.param_fetcher')->get('length');

        $offset = max($offset, 0);
        $length = min($length, $max);

        $queryBuilder = $this->getDoctrine()->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from('FunProFinancialBundle:DiscountCode', 'd');

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
        ))->setSerializationContext($context);
    }
}