<?php

namespace FunPro\FinancialBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\FinancialBundle\Entity\DiscountCode;
use FunPro\FinancialBundle\Entity\DiscountWrongCount;
use FunPro\FinancialBundle\Entity\FavoriteDiscountCodes;
use FunPro\PassengerBundle\Entity\Passenger;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DiscountController
 * @package FunPro\FinancialBundle\Controller
 *
 * @Rest\RouteResource(resource="discount", pluralize=false)
 * @Rest\NamePrefix("fun_pro_api_")
 */
class DiscountController extends FOSRestController
{
    /**
     * Add code to favorite list
     *
     * @ApiDoc(
     *     section="Discount",
     *     resource=true,
     *     views={"passenger"},
     *     statusCodes={
     *          204="Success",
     *          400={
     *              "User enter very wrong code (code: 1)",
     *              "code is wrong (code: 2)",
     *              "this code reached to max usage (code: 3)",
     *              "you can not use this code more than (code: 4)",
     *              "this code is expired (code: 5)"
     *          }
     *     }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     *
     * @Rest\RequestParam(name="code", requirements="\w+", strict=true, allowBlank=false, nullable=false)
     * @return \FOS\RestBundle\View\View
     */
    public function postAddAction()
    {
        /** @var Passenger $Passenger */
        $passenger = $this->getUser();
        $translator = $this->get('translator');
        $doctrine = $this->getDoctrine();
        $manager = $doctrine->getManager();
        $code = $this->get('fos_rest.request.param_fetcher')->get('code', true);

        $count = $doctrine->getRepository('FunProFinancialBundle:DiscountWrongCount')->getCount($passenger, 86400);
        if ($count > 10) {
            $error = array(
                'code' => 1,
                'message' => $translator->trans('you.enter.very.wrong.code.you.can.not.try.again'),
            );

            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        /** @var DiscountCode $discountCode */
        $discountCode = $doctrine->getRepository('FunProFinancialBundle:DiscountCode')->findOneByCode($code);
        if ($discountCode->getExpiredAt() < new \DateTime()) {
            $error = array(
                'code' => 5,
                'message' => $translator->trans('this.code.is.expired'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        if (!$discountCode) {
            $wrong = new DiscountWrongCount($passenger, $code);
            $manager->persist($wrong);
            $manager->flush();
            $error = array(
                'code' => 2,
                'message' => $translator->trans('your.code.is.wrong'),
            );

            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $favoriteRepo = $doctrine->getRepository('FunProFinancialBundle:FavoriteDiscountCodes');
        $usageCount = $favoriteRepo->getUsageCount($discountCode);
        $userUsageCount = $favoriteRepo->getUsageCount($discountCode, $passenger);

        if ($usageCount >= $discountCode->getMaxUsage() or $userUsageCount >= $discountCode->getMaxUsagePerUser()) {
            $error = array(
                'code' => ($usageCount >= $discountCode->getMaxUsage()) ? 3 : 4,
                'message' => $usageCount >= $discountCode->getMaxUsage() ?
                    $translator->trans('this.code.reached.to.max.usage')
                    : $translator->trans('you.can.not.use.this.code.more.than'),
            );

            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $favorite = $favoriteRepo->findOneBy(array(
            'passenger' => $passenger,
            'discountCode' => $discountCode,
            'used' => false,
        ));

        if (!$favorite) {
            $favorite = new FavoriteDiscountCodes($passenger, $discountCode);
            $doctrine->getManager()->persist($favorite);
            $doctrine->getManager()->flush();
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get all favorite codes.
     *
     * @ApiDoc(
     *     section="Discount",
     *     resource=true,
     *     views={"passenger"},
     *     output={
     *          "class"="FunPro\FinancialBundle\Entity\DiscountCode",
     *          "groups"={"Passenger"}
     *     },
     *     statusCodes={
     *          200="Success"
     *     }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER')")
     */
    public function cgetAction()
    {
        $codes = $this->getDoctrine()->getRepository('FunProFinancialBundle:FavoriteDiscountCodes')
            ->findBy(array('passenger' => $this->getUser(), 'used' => false));

        $context = new Context();
        $context->addGroup('Passenger');
        return $this->view($codes, Response::HTTP_OK)
            ->setSerializationContext($context);
    }

    /**
     * Active one code
     *
     * @ApiDoc(
     *     section="Discount",
     *     resource=true,
     *     views={"passenger"},
     *     statusCodes={
     *          204="Success",
     *          400={
     *              "You can not change discount code in service (code: 1)",
     *              "This code is expired (code: 2)"
     *          },
     *          404="You have not this code in your list"
     *     }
     * )
     *
     * @Rest\RequestParam(name="code", requirements="\w+", strict=true, allowBlank=false, nullable=false)
     * @Rest\RequestParam(name="status", requirements="active|deactive", strict=true, allowBlank=false, nullable=false)
     *
     * @Security("has_role('ROLE_PASSENGER')")
     */
    public function patchStatusAction()
    {
        $translator = $this->get('translator');
        $doctrine = $this->getDoctrine();
        $passenger = $this->getUser();
        $fetcher = $this->get('fos_rest.request.param_fetcher');
        $status = $fetcher->get('status', true);

        $service = $doctrine->getRepository('FunProServiceBundle:Service')
            #should show all service that status is not finished
            ->getLastActiveServiceOfPassenger($passenger);

        if ($service) {
            $activeCode = $this->getDoctrine()->getRepository('FunProFinancialBundle:FavoriteDiscountCodes')
                ->findOneBy(array('active' => true, 'passenger' => $passenger, 'used' => false));

            if ($activeCode) {
                $error = array(
                    'code' => 1,
                    'message' => $translator->trans('you.can.not.change.discount.code.in.service')
                );
                return $this->view($error, Response::HTTP_BAD_REQUEST);
            }
        }
        
        $code = $fetcher->get('code', true);
        /** @var DiscountCode $discountCode */
        $discountCode = $doctrine->getRepository('FunProFinancialBundle:DiscountCode')->findOneByCode($code);

        if ($discountCode->getExpiredAt() < new \DateTime()) {
            $error = array(
                'code' => 2,
                'message' => $translator->trans('this.code.is.expired'),
            );
            return $this->view($error, Response::HTTP_BAD_REQUEST);
        }

        $doctrine->getRepository('FunProFinancialBundle:FavoriteDiscountCodes')->deactiveCodes($passenger);
        
        if ($discountCode) {
            $favoriteCode = $doctrine->getRepository('FunProFinancialBundle:FavoriteDiscountCodes')
                ->findOneBy(array('passenger' => $passenger, 'discountCode' => $discountCode, 'used' => false));

            if (!$favoriteCode) {
                return $this->view(null, Response::HTTP_NOT_FOUND);
            }

            $favoriteCode->setActive($status === 'active' ?: false);

            $doctrine->getManager()->flush();
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}