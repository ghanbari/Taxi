<?php

namespace FunPro\ServiceBundle\Controller\Management;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\DriverBundle\Entity\Car;
use FunPro\EngineBundle\Utility\DataTable;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Entity\ServiceLog;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ServiceController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource(resource="service", pluralize=false)
 * @Rest\NamePrefix("fun_pro_admin_")
 */
class ServiceController extends FOSRestController
{
    /**
     * Show Service
     *
     * @Security("has_role('ROLE_OPERATOR')")
     *
     * @Rest\QueryParam(name="start", requirements="\d+", default="0", nullable=true, strict=true)
     * @Rest\QueryParam(name="length", requirements="\d+", default="10", nullable=true, strict=true)
     * @Rest\View("FunProServiceBundle:Service:cget.html.twig")
     */
    public function cgetAction(Request $request)
    {
        $this->getDoctrine()->getManager()->getFilters()->disable('softdeleteable');
        $max = $this->getParameter('ui.data_table.max_per_page');
        $offset = $this->get('fos_rest.request.param_fetcher')->get('start');
        $length = $this->get('fos_rest.request.param_fetcher')->get('length');

        $offset = max($offset, 0);
        $length = min($length, $max);

        $queryBuilder = $this->getDoctrine()->getRepository('FunProServiceBundle:Service')->getAllQueryBuilder();

        DataTable::orderBy($queryBuilder, $request);
        DataTable::filterBy($queryBuilder, $request);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($queryBuilder, floor($offset / $length)+1, $length);

        $context = (new Context())
            ->addGroups(array('Admin', 'Public', 'Car', 'Driver', 'Point'))
            ->setMaxDepth(1);
        return $this->view(array(
            "recordsTotal" => $pagination->getTotalItemCount(),
            "recordsFiltered" => $pagination->getTotalItemCount(),
            "data" => $pagination->getItems()
        ))->setSerializationContext($context);
    }

    /**
     * Show Service
     *
     * @Security("has_role('ROLE_OPERATOR')")
     * @ParamConverter(name="service", class="FunProServiceBundle:Service")
     * 
     * @Rest\View("FunProServiceBundle:Service:cget.html.twig")
     */
    public function cancelAction(Request $request, $id)
    {
        /** @var Service $service */
        $service = $request->attributes->get('service');
        $manager = $this->getDoctrine()->getManager();

        if ($service->getStatus() === ServiceLog::STATUS_ACCEPTED
            or $service->getStatus() === ServiceLog::STATUS_REQUESTED
            or $service->getStatus() === ServiceLog::STATUS_READY
        ) {
            if ($service->getCar()) {
                if ($service->getCar()->getWakeful()) {
                    $service->getCar()->setStatus(Car::STATUS_WAKEFUL);
                } else {
                    $service->getCar()->setStatus(Car::STATUS_SLEEP);
                }
            }
            $service->setCanceledBy($this->getUser());
            $log = new ServiceLog($service, ServiceLog::STATUS_CANCELED);
            $manager->persist($log);
            $manager->flush();

            return $this->view(null, Response::HTTP_NO_CONTENT);
        } else {
            return $this->view(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
