<?php

namespace FunPro\FinancialBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FunPro\FinancialBundle\Entity\Transaction;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReportController
 *
 * @package FunPro\FinancialBundle\Controller
 *
 * @Rest\RouteResource(resource="report", pluralize=false)
 */
class ReportController extends FOSRestController
{
    /**
     * Get list of user transaction paginated
     *
     * @ApiDoc(
     *      section="Payment",
     *      resource=true,
     *      output={
     *          "class"="FunPro\FinancialBundle\Entity\Transaction",
     *          "groups"={"Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are not passenger or driver",
     *          },
     *      }
     * )
     *
     * @Security("has_role('ROLE_PASSENGER') or has_role('ROLE_DRIVER')")
     *
     * @Rest\QueryParam(name="min", nullable=true, requirements="\d+", strict=true)
     * @Rest\QueryParam(name="max", nullable=true, requirements="\d+", strict=true)
     * @Rest\QueryParam(name="direction", nullable=true, requirements="income|outcome", strict=true)
     * @Rest\QueryParam(name="type", nullable=true, requirements="pay|wage|reward|commission|credit|withdraw|move", strict=true)
     * @Rest\QueryParam(name="limit", nullable=true, default="10", requirements="\d+", strict=true)
     * @Rest\QueryParam(name="offset", nullable=true, default="0", requirements="\d+", strict=true)
     */
    public function cgetTransactionAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $limit  = min(20, $fetcher->get('limit'));
        $offset = max(0, $fetcher->get('offset'));

        $min = $fetcher->get('min');
        $max = $fetcher->get('max');

        switch ($fetcher->get('direction')) {
            case 'income':
                $direction = Transaction::DIRECTION_INCOME;
                break;
            case 'outcome':
                $direction = Transaction::DIRECTION_OUTCOME;
                break;
            default:
                $direction = null;
        }

        $type = $this->convertStringToTransactionType($fetcher->get('type'));

        $transactions = $this->getDoctrine()->getRepository('FunProFinancialBundle:Transaction')
            ->getAllFilterBy($min, $max, $direction, $type, $limit, $offset);

        $statusCode = empty($transactions) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $context = (new Context())->addGroups(array('Wallet', 'Service', 'Owner', 'Public'));
        return $this->view($transactions, $statusCode)
            ->setSerializationContext($context);
    }

    /**
     * convert a word to correspond transaction type
     *
     * @param $type
     *
     * @return int
     */
    private function convertStringToTransactionType($type)
    {
        switch ($type) {
            case 'pay':
                return Transaction::TYPE_PAY;
            case 'wage':
                return Transaction::TYPE_WAGE;
            case 'reward':
                return Transaction::TYPE_REWARD;
            case 'commission':
                return Transaction::TYPE_COMMISSION;
            case 'credit':
                return Transaction::TYPE_CREDIT;
            case 'withdraw':
                return Transaction::TYPE_WITHDRAW;
            case 'move':
                return Transaction::TYPE_MOVE;
        }
    }
}
