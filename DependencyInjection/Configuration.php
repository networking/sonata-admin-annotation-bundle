<?php

/**
 * Created by PhpStorm.
 * User: Mike Meier <mike.meier@ibrows.ch>
 * Date: 10.10.14
 * Time: 17:55
 */

namespace Ibrows\Bundle\SonataAdminAnnotationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ibrows_sonata_admin_annotation');

        $rootNode
            ->children()
                ->arrayNode('autoservice')
                    ->children()
                        ->arrayNode('entities')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('directory')->end()
                                    ->scalarNode('prefix')->end()

                                    ->scalarNode('admin')->end()
                                    ->scalarNode('controller')->end()
                                    ->scalarNode('label_translator_strategy')->end()
                                    ->scalarNode('label_catalog')->end()
                                    ->scalarNode('show_in_dashboard')->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('default_entity')
                            ->children()
                                ->scalarNode('admin')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('label_translator_strategy')->end()
                                ->scalarNode('label_catalog')->end()
                                ->scalarNode('show_in_dashboard')->end()
                            ->end()
                        ->end()

                        ->scalarNode('service_id_prefix')->cannotBeEmpty()->isRequired()->end()
        ;

        return $treeBuilder;
    }
}