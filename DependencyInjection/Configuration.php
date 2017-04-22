<?php

namespace FourLabs\GampBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('four_labs_gamp');

        $rootNode
            ->children()
                ->integerNode('protocol_version')
                    ->defaultValue(1)
                ->end()
                ->scalarNode('tracking_id')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('use_ssl')
                    ->defaultTrue()
                ->end()
                ->booleanNode('anonymize_ip')
                    ->defaultFalse()
                ->end()
                ->booleanNode('async_requests')
                    ->defaultTrue()
                ->end()
                ->booleanNode('sandbox')
                    ->defaultFalse()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
