<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\Tests\DependencyInjection;

use Karser\Recaptcha3Bundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    /**
     * @dataProvider dataForProcessedConfiguration
     */
    public function testProcessedConfiguration($configs, $expectedConfig)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);
        self::assertSame($expectedConfig, $config);
    }

    public static function dataForProcessedConfiguration()
    {
        return [
            [
                [
                    'karser_recaptcha3' => [
                        'site_key' => 'key',
                        'secret_key' => 'secret',
                    ]
                ],
                [
                    'site_key' => 'key',
                    'secret_key' => 'secret',
                    'score_threshold' => 0.5,
                    'host' => 'www.google.com',
                    'enabled' => true,
                ],
            ],
            [
                [
                    'karser_recaptcha3' => [
                        'site_key' => 'key',
                        'secret_key' => 'secret',
                        'host' => 'www.google.com',
                    ]
                ],
                [
                    'site_key' => 'key',
                    'secret_key' => 'secret',
                    'host' => 'www.google.com',
                    'score_threshold' => 0.5,
                    'enabled' => true,
                ],
            ],
            [
                [
                    'karser_recaptcha3' => [
                        'site_key' => 'key',
                        'secret_key' => 'secret',
                        'host' => 'www.recaptcha.net',
                    ]
                ],
                [
                    'site_key' => 'key',
                    'secret_key' => 'secret',
                    'host' => 'www.recaptcha.net',
                    'score_threshold' => 0.5,
                    'enabled' => true,
                ],
            ],
            [
                [
                    'karser_recaptcha3' => [
                        'site_key' => 'key',
                        'secret_key' => 'secret',
                        'score_threshold' => 0.7,
                        'enabled' => false,
                    ]
                ],
                [
                    'site_key' => 'key',
                    'secret_key' => 'secret',
                    'score_threshold' => 0.7,
                    'enabled' => false,
                    'host' => 'www.google.com',
                ],
            ],
        ];
    }
}
