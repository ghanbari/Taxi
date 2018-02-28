<?php

namespace FunPro\HomeBundle\Controller;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\PessimisticLockException;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Exception\CarStatusException;
use FunPro\DriverBundle\Exception\DriverNotFoundException;
use FunPro\EngineBundle\Utility\DataTable;
use FunPro\FinancialBundle\Entity\BaseCost;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\GeoBundle\Utility\Util;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\FloatingCost;
use FunPro\ServiceBundle\Entity\PropagationList;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Entity\ServiceLog;
use FunPro\ServiceBundle\Event\GetCarPointServiceEvent;
use FunPro\ServiceBundle\Event\ServiceEvent;
use FunPro\ServiceBundle\Exception\ServiceStatusException;
use FunPro\ServiceBundle\Form\ServiceType;
use FunPro\ServiceBundle\Repository\ServiceRepository;
use FunPro\ServiceBundle\ServiceEvents;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ServiceController
 *
 * @package FunPro\ServiceBundle\Controller
 *
 * @Rest\RouteResource("", pluralize=false)
 * @Rest\NamePrefix("fun_pro_home_api_")
 *
 */
class DefaultController extends FOSRestController
{
    /**
     * @Rest\View()
     *
     * @return mixed
     */
    public function getAction()
    {
        return $this->render('FunProHomeBundle:Default:index.html.twig');
    }
}
