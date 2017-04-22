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
class SmsIrProviderFactory implements ProviderFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $id, array $config)
    {
        $container->getDefinition($id)
            ->replaceArgument(2, $config['user'])
            ->replaceArgument(3, $config['pass'])
            ->replaceArgument(4, $config['from'])
            ->replaceArgument(5, $config['international_prefix'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey()
    {
        return 'sms_ir';
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
