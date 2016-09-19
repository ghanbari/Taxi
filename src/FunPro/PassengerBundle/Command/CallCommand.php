<?php

namespace FunPro\PassengerBundle\Command;

use SoapClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CallCommand
 *
 * @package FunPro\PassengerBundle\Command
 */
class CallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('passenger:call')
            ->setDescription('...')
            ->addArgument('tokenId', InputArgument::REQUIRED, 'Token id');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tokenId = $input->getArgument('tokenId');
        $container = $this->getContainer();

        $token = $container->get('doctrine')->getRepository('FunProUserBundle:Token')->find($tokenId);

        if (!$token) {
            $container->get('logger')->addError('token is not exists');
            return;
        }

        if (!$token->isExpired()) {
            date_default_timezone_set('Asia/tehran');
            $wsdl = 'http://portal.avanak.ir/webservice3.asmx?WSDL';
            $client = new SoapClient($wsdl);
            $param = array(
                'userName' => $container->getParameter('avanak.user'),
                'password' => $container->getParameter('avanak.pass'),
                'text' => $token->getToken(),
                'number' => $token->getUser()->getMobile(),
                'vote' => false
            );
            try {
                $res = $client->QuickSendWithTTS($param);
                $container->get('logger')->addInfo('calling', array($res));
            } catch (\Exception $e) {
                $container->get('logger')->addError($e->getMessage());
            }
        }
    }
}
