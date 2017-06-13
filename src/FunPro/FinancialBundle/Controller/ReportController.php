<?php

namespace FunPro\FinancialBundle\Controller;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FunPro\FinancialBundle\Entity\Transaction;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ReportController
 *
 * @package FunPro\FinancialBundle\Controller
 *
 * @Rest\RouteResource(resource="report", pluralize=false)
 * @Rest\NamePrefix("fun_pro_financial_api_")
 */
class ReportController extends FOSRestController
{
    /**
     * Get User wallets
     * @deprecated
     *
     * @ApiDoc(
     *      section="Report",
     *      deprecated=true,
     *      resource=true,
     *      output={
     *          "class"="FunPro\FinancialBundle\Entity\Wallet",
     *          "groups"={"Owner", "Public"},
     *          "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"},
     *      },
     *      statusCodes={
     *          200="When success",
     *          403= {
     *              "when you are a user and you are login in currently",
     *          },
     *      }
     * )
     *
     * @Security("is_authenticated()")
     *
     * @return \FOS\RestBundle\View\View
     */
    public function getWalletAction()
    {
        $manager = $this->getDoctrine()->getManager();

        #TODO: must return all wallet in all currency
        $currency = $this->getDoctrine()->getRepository('FunProFinancialBundle:Currency')->findOneByCode('IRR');

        $wallet = $manager->getRepository('FunProFinancialBundle:Wallet')
            ->getUserWallet($this->getUser(), $currency);

        $context = (new Context())->addGroups(array('Owner', 'Currency', 'Public'))->setMaxDepth(1);
        return $this->view($wallet, Response::HTTP_OK)->setSerializationContext($context);
    }

    /**
     * Get list of user transaction paginated
     *
     * @ApiDoc(
     *      section="Report",
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
     * @Rest\QueryParam(name="from", requirements=@Assert\Date(), nullable=true, strict=true)
     * @Rest\QueryParam(name="till", requirements=@Assert\Date(), nullable=true, strict=true)
     * @Rest\QueryParam(name="min", nullable=true, requirements="\d+", strict=true)
     * @Rest\QueryParam(name="max", nullable=true, requirements="\d+", strict=true)
     * @Rest\QueryParam(name="direction", nullable=true, requirements="income|outcome", strict=true)
     * @Rest\QueryParam(name="types", nullable=true, requirements="pay|wage|reward|commission|credit|withdraw|move",
     *                               strict=true, map=true, description="array of types")
     * @Rest\QueryParam(name="limit", nullable=true, default="10", requirements="\d+", strict=true)
     * @Rest\QueryParam(name="offset", nullable=true, default="0", requirements="\d+", strict=true)
     */
    public function cgetTransactionAction()
    {
        $fetcher = $this->get('fos_rest.request.param_fetcher');

        $limit = min(20, $fetcher->get('limit'));
        $offset = max(0, $fetcher->get('offset'));
        #ali use offset as page number
        $offset = $offset * $limit;

        $from = $fetcher->get('from') ? new \DateTime($fetcher->get('from')) : null;
        $till = $fetcher->get('till') ? new \DateTime($fetcher->get('till')) : null;

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

        $types = $this->convertStringToTransactionType($fetcher->get('types'));

        $transactions = $this->getDoctrine()->getRepository('FunProFinancialBundle:Transaction')
            ->getAllFilterBy($this->getUser(), $from, $till, $min, $max, $direction, $types, $limit, $offset);

        $statusCode = empty($transactions) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        $context = (new Context())->addGroups(array('Wallet', 'Service', 'Owner', 'Public'));
        return $this->view($transactions, $statusCode)
            ->setSerializationContext($context);
    }

    /**
     * convert a word to correspond transaction type
     *
     * @param array $types
     *
     * @return int
     */
    private function convertStringToTransactionType(array $types)
    {
        $convertedTypes = array();

        foreach ($types as $type) {
            switch ($type) {
                case 'pay':
                    $convertedTypes[] = Transaction::TYPE_PAY;
                    break;
                case 'wage':
                    $convertedTypes[] = Transaction::TYPE_WAGE;
                    break;
                case 'reward':
                    $convertedTypes[] = Transaction::TYPE_REWARD;
                    break;
                case 'commission':
                    $convertedTypes[] = Transaction::TYPE_COMMISSION;
                    break;
                case 'credit':
                    $convertedTypes[] = Transaction::TYPE_CREDIT;
                    break;
                case 'withdraw':
                    $convertedTypes[] = Transaction::TYPE_WITHDRAW;
                    break;
                case 'move':
                    $convertedTypes[] = Transaction::TYPE_MOVE;
                    break;
            }
        }

        return $convertedTypes;
    }
}
