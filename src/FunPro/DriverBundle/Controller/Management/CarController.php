<?php

namespace FunPro\DriverBundle\Controller\Management;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Form\CarType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

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
                $options['validation_groups'] = array('Create');
                break;
            case 'PUT':
                $options['action'] = $this->generateUrl('fun_pro_admin_put_driver_car', array('id'=>$car->getId()));
                $options['validation_groups'] = array('Update');
            case 'DELETE':
                $options['action'] = $this->generateUrl('fun_pro_admin_delete_driver_car', array('id'=>$car->getId()));
        }

        $form = $this->createForm(CarType::class, $car, $options);

        return $form;
    }

    /**
     * @ParamConverter("driver", class="FunProDriverBundle:Driver", options={"id"="driverId"})
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

    public function postAction($driverId)
    {
        $driverId = intval($driverId);

    }

    /**
     *
     * @Rest\Get("/driver/car/{id}/edit", requirements={"id"="\d+"})
     *
     * @param $id
     */
    public function editAction($id)
    {

    }

    /**
     * @Rest\Put("/driver/car/{id}", requirements={"id"="\d+"})
     *
     * @param $driverId
     * @param $id
     */
    public function putAction($driverId, $id)
    {

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
}