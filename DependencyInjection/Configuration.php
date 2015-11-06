<?php

namespace Zk2\SPSBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $options = array('pagination_template', 'sortable_template', 'timezone_db');

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zk2_sps');

        $rootNode
            ->children()
                ->arrayNode('options')
                    ->cannotBeEmpty()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pagination_template')
                            ->defaultValue('Zk2SPSBundle:Form:pagination.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('sortable_template')
                            ->defaultValue('Zk2SPSBundle:Form:sortable.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('timezone_db')
                            ->defaultValue(date_default_timezone_get())
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
