<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('netgen_enhanced_ez_binary_file');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
