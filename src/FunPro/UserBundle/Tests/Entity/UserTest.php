<?php

namespace FunPro\UserBundle\Tests\Entity;

use FunPro\UserBundle\Entity\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testName()
    {
        $user = new User();
        $this->assertNull($user->getName());

        $user->setName('Mohammad Ghanbari');
        $this->assertEquals('Mohammad Ghanbari', $user->getName());
    }

    public function testAge()
    {
        $user = new User();
        $this->assertNull($user->getAge());

        $user->setAge(27);
        $this->assertEquals(27, $user->getAge());
    }

    public function dataProviderForSex()
    {
        return array(
            'm must be converted to zero' => array(User::SEX_MALE, 'm'),
            'male equal with zero' => array(User::SEX_MALE, User::SEX_MALE),
            'f must be converted to one' => array(User::SEX_FEMALE, 'f'),
            'female equal with one' => array(User::SEX_FEMALE, User::SEX_FEMALE),
        );
    }

    /**
     * @dataProvider dataProviderForSex
     *
     * @param $expected
     * @param $actual
     */
    public function testSex($expected, $actual)
    {
        $user = new User();
        $this->assertNull($user->getSex());

        $user->setSex($actual);
        $this->assertEquals($expected, $user->getSex());
    }

    public function testDescribtion()
    {
        $user = new User();
        $this->assertNull($user->getDescribtion());

        $user->setDescribtion('test a description');
        $this->assertEquals('test a description', $user->getDescribtion());
    }

    public function testAvatar()
    {
        $user = new User();
        $this->assertNull($user->getAvatar());

        $user->setAvatar('avatar file name');
        $this->assertEquals('avatar file name', $user->getAvatar());
    }

    public function testAvatarFile()
    {
        $this->markTestIncomplete();
    }

    public function testCreatedAt()
    {
        $user = new User();
        $this->assertNull($user->getCreatedAt());

        $now = new \DateTime();
        $user->setCreatedAt($now);
        $this->assertEquals($now, $user->getCreatedAt());
    }

    public function testWrongPasswordCount()
    {
        $user = new User();
        $this->assertEquals(0, $user->getWrongPasswordCount());

        $user->setWrongPasswordCount(1);
        $this->assertEquals(1, $user->getWrongPasswordCount());
    }
} 