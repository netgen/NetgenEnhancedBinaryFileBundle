<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Netgen\Bundle\EnhancedBinaryFileBundle\DependencyInjection\NetgenEnhancedBinaryFileExtension;

class NetgenEnhancedBinaryFileExtensionTest extends AbstractExtensionTestCase
{
    public function testItSetsValidContainerParameters()
    {
        $this->load();
    }

    protected function getContainerExtensions()
    {
        return [
            new NetgenEnhancedBinaryFileExtension(),
        ];
    }

    protected function getMinimalConfiguration()
    {
        return [];
    }
}
