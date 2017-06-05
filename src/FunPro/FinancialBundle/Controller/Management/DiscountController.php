<?php

namespace FunPro\FinancialBundle\Controller\Management;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\FinancialBundle\Entity\DiscountCode;
use FunPro\FinancialBundle\Form\DiscountCodeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return \Symfony\Component\Form\Form
     */
    private function getForm(DiscountCode $code, $update=false)
    {
        $options['method'] = $update ? 'put' : 'post';
        $options['validation_groups'] = $update ? array('Update', 'Point') : array('Create', 'Point');
        $options['action'] = $update ? $this->generateUrl('fun_pro_admin_put_discount') : $this->generateUrl('fun_pro_admin_post_discount');

        $form = $this->createForm(DiscountCodeType::class, $code, $options);
        return $form;
    }

    /**
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

    public function editAction()
    {

    }

    public function putAction()
    {

    }

    public function cgetAction()
    {
        
    }
}