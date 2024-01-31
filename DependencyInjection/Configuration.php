<?php
/**
 * This file is part of the SpsBundle.
 *
 * (c) Evgeniy Budanov <budanov.ua@gmail.comm> 2017.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace Zk2\SpsBundle\DependencyInjection;

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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zk2_sps');
        $rootNode = $treeBuilder->getRootNode();
        $sessionKeys = ['by_route', 'by_query', null];

        $rootNode
            ->children()
                ->arrayNode('templates')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pagination_template')
                            ->info('Pagination template')
                            ->defaultValue('@Zk2Sps/Template/pagination.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('sortable_template')
                            ->info('Sortable template')
                            ->defaultValue('@Zk2Sps/Template/sortable.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('filter_template')
                            ->info('Filter form template')
                            ->defaultValue('@Zk2Sps/Template/filter.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('table_template')
                            ->info('General table template')
                            ->defaultValue('@Zk2Sps/Template/table.html.twig')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('full_path_to_web_root')
                    ->info('Full path to web-root directory')
                    ->defaultValue('%kernel.project_dir%/public')
                ->end()
                ->scalarNode('td_builder_service_class')
                    ->info('Service implement TdBuilderInterface and return HTML <td>{content}</td>')
                    ->defaultValue('Zk2\SpsBundle\Model\TdBuilderService')
                    ->validate()
                        ->ifTrue(
                            function ($v) {
                                if (null === $v) {
                                    return false;
                                }

                                return !class_exists($v) or !is_subclass_of($v, 'Zk2\SpsBundle\Model\TdBuilderInterface');
                            }
                        )->thenInvalid('%s must be instanceof TdBuilderInterface')
                    ->end()
                ->end()
                ->scalarNode('session_key')
                    ->info('Key for the session: "by_route", "by_query" or null')
                    ->defaultValue('by_route')
                    ->validate()
                        ->ifNotInArray($sessionKeys)
                        ->thenInvalid('Invalid session_key %s. Use "by_route", "by_query" or null')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
