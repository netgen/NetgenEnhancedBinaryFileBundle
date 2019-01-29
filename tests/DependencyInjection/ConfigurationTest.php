<?php

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Netgen\Bundle\EnhancedBinaryFileBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    public function testConfigurationValuesAreOkAndValid()
    {
        $this->assertConfigurationIsValid(
            [
                'netgen_enhanced_ez_binary_file' => [
                ],
            ]
        );
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
