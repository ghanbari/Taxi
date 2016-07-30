<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FunPro\UserBundle\FunProUserBundle(),
            new FunPro\PassengerBundle\FunProPassengerBundle(),
            new FunPro\DriverBundle\FunProDriverBundle(),
            new FunPro\AgentBundle\FunProAgentBundle(),
            new FunPro\EngineBundle\FunProEngineBundle(),
            new FunPro\GeoBundle\FunProGeoBundle(),
            new FunPro\ServiceBundle\FunProServiceBundle(),
            new FunPro\AdminBundle\FunProAdminBundle(),
            new KPhoen\SmsSenderBundle\KPhoenSmsSenderBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            new Dunglas\AngularCsrfBundle\DunglasAngularCsrfBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();
            $bundles[] = new Nelmio\ApiDocBundle\NelmioApiDocBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
