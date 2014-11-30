<?php
/**
 * This file is part of the Ratza package.
 *
 * @author Ion Marusic <ion.marusic@gmail.com>
 */

namespace Ratza\GuzzleProfilerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from app/config files
 *
 * @package Ratza\GuzzleProfilerBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ratza_guzzle');

        $rootNode
            ->children()
                ->booleanNode('web_profiler')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
