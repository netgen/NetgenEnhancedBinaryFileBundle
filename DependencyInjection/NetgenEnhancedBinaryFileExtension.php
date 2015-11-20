<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class NetgenEnhancedBinaryFileExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );

        $loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . '/../Resources/config' ) );
        $loader->load( 'fieldtypes.yml' );
        $loader->load( 'field_type_handlers.yml' );
        $loader->load( 'storage_engines.yml' );
        $loader->load( 'mime.yml' );

    }
}
