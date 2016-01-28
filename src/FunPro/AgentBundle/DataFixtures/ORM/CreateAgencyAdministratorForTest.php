<?php

namespace FunPro\AgentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateAgencyAdministratorForTest extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var Container
     */
    private $container;
    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container->getParameter('test.passenger')) {
            return;
        }

        $userManager = $this->container->get('fos_user.user_manager');
        $credentials = $this->container->getParameter('test.agency.admin.credentials');

        foreach ($credentials as $username => $pass) {
            $user = $userManager->createUser();
            $user->setUsername($username);
            $user->setPlainPassword($pass);
            $user->setEmail($username.'@itaxico.ir');
            $user->setEnabled(true);
            $user->setName($username);
            $user->addRole(User::ROLE_AGENCY_ADMIN);

            $manager->persist($user);

            $this->setReference('agency-admin-' . $username, $user);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 20;
    }
} 