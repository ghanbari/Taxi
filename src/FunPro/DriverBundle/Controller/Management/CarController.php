<?php

namespace FunPro\DriverBundle\Controller\Management;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Form\CarType;
use FunPro\EngineBundle\Utility\DataTable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CarController
 *
 * @package FunPro\DriverBundle\Controller\Management
 *
 * @Rest\RouteResource("car", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class CarController extends FOSRestController
{

    private function getForm(Car $car, $method)
    {
        $options['method'] = strtoupper($method);
        switch ($options['method']) {
            case 'POST':
                $options['action'] = $this->generateUrl('fun_pro_admin_post_driver_car', array('driverId'=>$car->getDriver()->getId()));
                $options['validation_groups'] = array('Create', 'Point');
                break;
            case 'PUT':
                $options['action'] = $this->generateUrl('fun_pro_admin_put_driver_car', array('id'=>$car->getId()));
                $options['validation_groups'] = array('Update', 'Point');
            case 'DELETE':
                $options['action'] = $this->generateUrl('fun_pro_admin_delete_driver_car', array('id'=>$car->getId()));
        }

        $form = $this->createForm(CarType::class, $car, $options);

        return $form;
    }

    /**
     * @ParamConverter("driver", class="FunProDriverBundle:Driver", options={"id"="driverId"})
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\View("FunProDriverBundle:Management/Car:new.html.twig")
     *
     * @param $driverId
     * @return \Symfony\Component\Form\Form
     */
    public function newAction(Request $request, $driverId)
    {
        $driver = $request->attributes->get('driver');
        $car = new Car();
        $car->setDriver($driver);
        $form = $this->getForm($car, 'POST');
        return $form;
    }

    /**
     * @ParamConverter("driver", class="FunProDriverBundle:Driver", options={"id"="driverId"})
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\View("FunProDriverBundle:Management/Car:new.html.twig")
     *
     * @param $driverId
     * @return \Symfony\Component\Form\Form
     */
    public function postAction(Request $request, $driverId)
    {
        $driverId = intval($driverId);
        $driver = $request->attributes->get('driver');
        $car = new Car();
        $car->setDriver($driver);
        $form = $this->getForm($car, 'POST');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($car);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', $this->get('translator')->trans('car.created'));
            return $this->routeRedirectView('fun_pro_admin_cget_driver_car', array('driverId'=>$driverId));
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @ParamConverter("car", class="FunProDriverBundle:Car")
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\Get("/driver/car/{id}/edit", requirements={"id"="\d+"})
     * @Rest\View("FunProDriverBundle:Management/Car:new.html.twig")
     *
     * @param $id
     *
     * @return \Symfony\Component\Form\Form
     */
    public function editAction(Request $request, $id)
    {
        $car = $request->attributes->get('car');
        $form = $this->getForm($car, 'PUT');

        return $form;
    }

    /**
     * @ParamConverter("car", class="FunProDriverBundle:Car")
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\Put("/driver/car/{id}", requirements={"id"="\d+"})
     * @Rest\View("FunProDriverBundle:Management/Car:new.html.twig")
     *
     * @param $id
     *
     * @return \FOS\RestBundle\View\View
     */
    public function putAction(Request $request, $id)
    {
        $car = $request->attributes->get('car');
        $form = $this->getForm($car, 'PUT');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($car);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('info', $this->get('translator')->trans('car.updated'));
            return $this->routeRedirectView(
                'fun_pro_admin_cget_driver_car',
                array('driverId' => $car->getDriver()->getId())
            );
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Delete("/driver/car/{id}", requirements={"id"="\d+"})
     *
     * @param $driverId
     * @param $id
     */
    public function deleteAction($driverId, $id)
    {

    }

    /**
     * @ParamConverter("driver", class="FunProDriverBundle:Driver", options={"id"="driverId"})
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true)
     * @Rest\View("FunProDriverBundle:Management/Car:cget.html.twig")
     *
     * @param $driverId
     * @return \FOS\RestBundle\View\View
     */
    public function cgetAction(Request $request, $driverId)
    {
        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start', 0);
        $length = $this->get('fos_rest.request.param_fetcher')->get('length', 10);

        $offset = max($offset, 0);
        $length = min($length, $max);

        $driver = $request->attributes->get('driver');
        $qb = $this->getDoctrine()->getRepository('FunProDriverBundle:Car')->getAllFilterByDriverQueryBuilder($driver);

        DataTable::orderBy($qb, $request);
        DataTable::filterBy($qb, $request);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($qb, floor($offset / $length)+1, $length);

        $context = (new Context())
            ->addGroup('Public')
            ->addGroup('Admin')
            ->addGroup('Plaque');
        return $this->view(
            array(
                "recordsTotal" => $pagination->getTotalItemCount(),
                "recordsFiltered" => $pagination->getTotalItemCount(),
                "data" => $pagination->getItems()
            )
        )
            ->setTemplateData(array('driver' => $driver))
            ->setSerializationContext($context);
    }

    /**
     * @Rest\Get("/driver/car/{id}")
     *
     * @param $id
     */
    public function getAction($id)
    {

    }
}