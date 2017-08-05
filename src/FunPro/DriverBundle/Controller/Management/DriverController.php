<?php

namespace FunPro\DriverBundle\Controller\Management;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Form\DriverType;
use FunPro\EngineBundle\Utility\DataTable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ManagementControler
 *
 * @package FunPro\DriverBundle\Controller
 *
 * @Rest\RouteResource("driver", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class DriverController extends FOSRestController
{
    /**
     * create a form
     *
     * @param Driver $driver
     * @param string $method
     * @return \Symfony\Component\Form\Form
     */
    private function getForm(Driver $driver, $method='POST')
    {
        $options['method'] = $method;
        $options['method'] = strtoupper($method);

        switch ($options['method']) {
            case 'POST':
                $options['action'] = $this->generateUrl('fun_pro_admin_post_driver');
                $options['validation_groups'] = array('Register', 'AddressCreate', 'Point');
                break;
            case 'PUT':
                $options['action'] = $this->generateUrl('fun_pro_admin_put_driver', array('id' => $driver->getId()));
                $options['validation_groups'] = array('Update', 'AddressUpdate', 'Point');
                break;
            case 'DELETE':
                $options['action'] = $this->generateUrl('fun_pro_admin_delete_driver', array('id' => $driver->getId()));
                break;
        }

        $requestFormat = $this->get('request_stack')->getCurrentRequest()->getRequestFormat('html');
        $options['csrf_protection'] = $requestFormat == 'html' ?: false;

        $form = $this->createForm(DriverType::class, $driver, $options);
        $form->remove('plainPassword');
        $form->remove('username');

        if ($method == 'PUT') {
            $form->remove('plainPassword');
        }

        return $form;
    }

    /**
     * Show a form for create of driver
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\View("FunProDriverBundle:Management/Driver:new.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newAction()
    {
        $driver = new Driver();
        $form = $this->getForm($driver, 'POST');
        return $form;
    }

    /**
     * Create a driver
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\View("FunProDriverBundle:Management/Driver:new.html.twig")
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function postAction(Request $request)
    {
        do {
            $apiKey = bin2hex(random_bytes(100));
            $isDuplicate = $this->getDoctrine()->getRepository('FunProUserBundle:Device')
                ->findOneByApiKey($apiKey);
        } while ($isDuplicate);

        mt_srand(time() * rand());
        $password = mt_rand(10000, 99999);
        $driver = new Driver();
        $driver->setApiKey($apiKey);
        $driver->setPlainPassword($password);

        $form = $this->getForm($driver, 'POST');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->get('fos_user.user_manager')->updateUser($driver);
            $message = $this->get('translator')->trans(
                'your.registeration.is.completed.your.password.is.%password%',
                array('%password%' => $password)
            );
            $this->get('sms.sender')->send($driver->getMobile(), $message);

            $this->addFlash('success', $this->get('translator')->trans('driver.created'));
            return $this->routeRedirectView('fun_pro_admin_cget_driver');
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Show a form for update of driver
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @ParamConverter("driver", class="FunProDriverBundle:Driver")
     *
     * @Rest\View("FunProDriverBundle:Management/Driver:new.html.twig")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\Form\Form
     */
    public function editAction(Request $request, $id)
    {
        $id = intval($id);
        $driver = $request->attributes->get('driver');
        $form = $this->getForm($driver, 'PUT');
        return $form;
    }

    /**
     * Update driver
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @ParamConverter("driver", class="FunProDriverBundle:Driver")
     *
     * @Rest\View("FunProDriverBundle:Management/Driver:new.html.twig")
     *
     * @param Request $request
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function putAction(Request $request, $id)
    {
        $driver = $request->attributes->get('driver');
        $form = $this->getForm($driver, 'PUT');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('info', $this->get('translator')->trans('driver.updated'));
            return $this->routeRedirectView('fun_pro_admin_cget_driver');
        }

        return $this->view($form, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete driver
     *
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter(name="driver", class="FunProDriverBundle:Driver")
     *
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function deleteAction(Request $request, $id)
    {
        /** @var Driver $driver */
        $driver = $request->attributes->get('driver');
        $driver->setDeletedBy($this->getUser());
        $manager = $this->getDoctrine()->getManager();

        #FIXME: sleep all cars in wakeful table
        #FIXME: this job must doing by trigger or doctrine events
        /** @var Car $car */
        foreach ($driver->getCars() as $car) {
            $manager->remove($car->getWakeful());
            $manager->remove($car);
        }

        $manager->remove($driver);
        $manager->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Recover driver
     *
     * @Security("has_role('ROLE_OPERATOR')")
     * @ParamConverter(name="driver", class="FunProDriverBundle:Driver")
     *
     * @param $id
     * @return \FOS\RestBundle\View\View
     */
    public function recoverAction(Request $request, $id)
    {
        mt_srand(time() * rand());
        $password = mt_rand(10000, 99999);
        $driver = $request->attributes->get('driver');
        $driver->setPlainPassword($password);

        $this->get('fos_user.user_manager')->updateUser($driver);
        $message = $this->get('translator')->trans(
            'your.registeration.is.completed.your.password.is.%password%',
            array('%password%' => $password)
        );
        $this->get('sms.sender')->send($driver->getMobile(), $message);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Show Drivers
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true, strict=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true, strict=true)
     * @Rest\View("FunProDriverBundle:Management/Driver:cget.html.twig")
     */
    public function cgetAction(Request $request)
    {
        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start');
        $length = $this->get('fos_rest.request.param_fetcher')->get('length');

        $offset = max($offset, 0);
        $length = min($length, $max);

        $queryBuilder = $this->getDoctrine()->getRepository('FunProDriverBundle:Driver')->getAllDriversQueryBuilder();

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

    public function getAction($id)
    {
        $id = intval($id);

    }
} 