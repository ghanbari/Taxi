<?php

namespace FunPro\PassengerBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\PassengerBundle\Form\Type\RegisterType;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type;
use FOS\RestBundle\Context\Context;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class PassengerController
 *
 * @package FunPro\PassengerBundle\Controller
 *
 * @Rest\RouteResource("passenger", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 */
class PassengerController extends FOSRestController
{
    /**
     * Show a form for create of passenger
     *
     * @Security("!is_authenticated()")
     *
     * @Rest\View(template="FunProPassengerBundle:Passenger:new.html.twig")
     *
     * @return \Symfony\Component\Form\Form
     */
    public function newAction()
    {
    }

    /**
     * Create a passenger
     *
     * @ApiDoc(
     *      section="Register",
     *      resource=true,
     *      views={"passenger"},
     *      statusCodes={
     *          204="When success",
     *          403= {
     *              "when you are a user and you are login in currently",
     *          },
     *      }
     * )
     *
     * @Security("!is_authenticated()")
     *
     * @Rest\View()
     * @Rest\RequestParam(name="phone", requirements="09[0-9]{9}", strict=true)
     *
     * @param Request $request
     * @return \FOS\RestBundle\View\View|\Symfony\Component\Form\Form
     */
    public function postAction(Request $request)
    {
        $phone = $this->get('fos_rest.request.param_fetcher')->get('phone');
        $passenger = $this->getDoctrine()->getRepository('FunProPassengerBundle:Passenger')->findOneByMobile($phone);

        if (is_null($passenger)) {
            $passenger = new Passenger();
            $passenger->setMobile($phone);
            $passenger->setPassword(bin2hex(random_bytes(10)));
        }

            $verifyNumber = random_int(11111, 99999);
            $passenger->setToken($verifyNumber);
            $this->get('fos_user.user_manager')->updateUser($passenger);
            $this->get('sms.sender')->send($passenger->getMobile(), $verifyNumber);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Confirm register & login
     *
     * @ApiDoc(
     *      section="Register",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\PassengerBundle\Entity\Passenger",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          201="When success",
     *          400="When form validation failed.",
     *          403= {
     *              "when csrf token is invalid",
     *              "when you are a user and you are login in currently",
     *          },
     *          404={
     *              "When user is not exists",
     *              "When Device is not exists",
     *          },
     *      }
     * )
     *
     * @Security("!is_authenticated()")
     *
     * @Rest\RequestParam(name="token", requirements="\d+", strict=true)
     * @Rest\RequestParam(name="phone", requirements="09[0-9]{9}", strict=true)
     * @Rest\RequestParam(name="deviceId", requirements="\d+", strict=true)
     */
    public function postConfirmAction()
    {
        $translator = $this->get('translator');
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $phone = $fetcher->get('phone');
        $token = $fetcher->get('token');
        $deviceId = $fetcher->get('deviceId');

        $manager = $this->getDoctrine()->getManager();

        /** @var Passenger $user */
        $user = $manager->getRepository('FunProPassengerBundle:Passenger')->findOneByMobile($phone);
        if (!$user) {
            $error = array(
                'code' => 0,
                'message' => $translator->trans('user.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_NOT_FOUND);
        }

        $now = new \DateTime();
        $diff = $now->diff($user->getTokenRequestedAt());
        if ($user->getToken() != $token or $diff->days >= 1) {
            $error = array(
                'code' => 0,
                'message' => $translator->trans('token.is.not.valid'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $device = $manager->getRepository('FunProEngineBundle:Device')->findOneBy(array(
            'deviceIdentifier' => $deviceId,
        ));
        if (!$device) {
            $error = array(
                'code' => 1,
                'message' => $translator->trans('device.is.not.exists'),
            );
            return $this->view($error, Response::HTTP_NOT_FOUND);
        }

        $apiKey = bin2hex(random_bytes(100));

        $manager->getConnection()->beginTransaction();
        $device->setOwner($user);
        $device->setApiKey($apiKey);
        $user->setToken(null);
        $manager->flush();
        $manager->getConnection()->commit();

        $context = (new Context())
            ->addGroups(array('Owner', 'Devices', 'Public', 'Register'))
            ->setMaxDepth(2);
        return $this->view($user, Response::HTTP_CREATED)
            ->setSerializationContext($context);
    }

    /**
     * Get current user profile.
     *
     * @ApiDoc(
     *      section="Profile",
     *      resource=true,
     *      views={"passenger"},
     *      output={
     *          "class"="FunPro\UserBundle\Entity\User",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not login currently",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated()")
     *
     * @return View
     */
    public function getProfileAction()
    {
        $user = $this->getUser();

        $context = new Context();
        $context->addGroups(array('Public', 'Profile'))
            ->setMaxDepth(2);
        return $this->view($user, Response::HTTP_OK)
            ->setSerializationContext($context);
    }

    public function getAction($passengerId)
    {

    }
}