<?php

namespace FunPro\PassengerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PassengerControllerTest extends WebTestCase
{
    public static function tearDownAfterClass()
    {
        $container = static::createClient()->getContainer();
        $manager = $container->get('doctrine')->getManager();
        $user = $manager->getRepository('FunProPassengerBundle:Passenger')->findOneByMobile('09371630000');
        if ($user) {
            $manager->remove($user);
            $manager->flush();
        }
    }

    public function getPagesUrl()
    {
        return array(
            'create user form' => array('GET', BASE_URL.'/passenger/new', null, array(), false),
            'edit user form' =>array('GET', BASE_URL.'/passenger/edit'),
            'show profile' => array('GET', BASE_URL.'/passenger/profile'),
            'create user' => array('POST', BASE_URL.'/passenger', '{"plainPassword": {"first": 123456,"second": 123456},"mobile": "09371630000","name": "aaaaa"}', array(), false),
//            'update user' => array('PUT', BASE_URL.'/passenger', ''),
        );
    }

    /**
     * @dataProvider getPagesUrl
     *
     * @param $url
     */
    public function testPagesIsSuccessful($method, $url, $content=null, $files=array(), $login=true)
    {
        $server = array();

        if ($login) {
            $server['PHP_AUTH_USER'] = '09371639233';
            $server['PHP_AUTH_PW'] = '1';
        }

        $client = static::createClient(array(), $server);
        $client->enableProfiler();

        $client->request($method, $url, array(), $files, array('CONTENT_TYPE' => 'application/json'), $content);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testPostActionForbiddenWhenUserIsLogin()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => PASSENGER_USERNAME,
            'PHP_AUTH_PW' => PASSENGER_PASSWORD,
        ));

        $client->request('get', BASE_URL . '/passenger/new');
        $this->assertTrue($client->getResponse()->isForbidden());
    }

//    public function testPostAction()
//    {
//        $client = static::createClient(array(), array(
//            'PHP_AUTH_USER' => PASSENGER_USERNAME,
//            'PHP_AUTH_PW' => PASSENGER_PASSWORD,
//        ));
//
//        $client->request('post', BASE_URL . '/passenger/new');
//        $this->assertTrue($client->getResponse()->isSuccessful());
//    }
}