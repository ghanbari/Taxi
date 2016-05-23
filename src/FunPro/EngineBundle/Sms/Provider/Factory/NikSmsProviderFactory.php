<?php

namespace FunPro\EngineBundle\Sms\Provider\Factory;

use KPhoen\SmsSenderBundle\DependencyInjection\Factory\ProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Nexmo provider factory
 *
 * @author Kévin Gomez <contact@kevingomez.fr>
 */
class NikSmsProviderFactory implements ProviderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
//        $definition = new Definition('FunPro\EngineBundle\Sms\Provider\SmsIrProvider');
//        $definition->addArgument($container->findDefinition("sms.http_adapter"));
//        $definition->addArgument($config['user']);
//        $definition->addArgument($config['pass']);
//        $definition->addArgument($config['from']);
//        $definition->addArgument($config['international_prefix']);
//        $container->addDefinitions(array('sms.provider.sms_ir' => $definition));

        $container->getDefinition($id)
            ->replaceArgument(1, $config['user'])
            ->replaceArgument(2, $config['pass'])
            ->replaceArgument(3, $config['from'])
            ->replaceArgument(4, $config['international_prefix'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'nik_sms';
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('user')->isRequired()->end()
                ->scalarNode('pass')->isRequired()->end()
                ->scalarNode('from')->isRequired()->end()
                ->scalarNode('international_prefix')->defaultValue('+98')->end()
            ->end()
        ;
    }
}
