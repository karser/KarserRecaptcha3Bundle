<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\DependencyInjection;

use Karser\Recaptcha3Bundle\Services\HostProviderInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('karser_recaptcha3');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('karser_recaptcha3', 'array');
        }
        $rootNode
            ->children()
                ->scalarNode('site_key')->isRequired()->end()
                ->scalarNode('secret_key')->isRequired()->end()
                ->floatNode('score_threshold')->min(0.0)->max(1.0)->defaultValue(0.5)->end()
                ->scalarNode('host')
                    ->defaultValue(HostProviderInterface::DEFAULT_HOST)
                    ->info(sprintf(
                        'Default host is "%s", if it is not reachable then use "%s" instead.',
                        HostProviderInterface::DEFAULT_HOST,
                        HostProviderInterface::ALT_HOST
                    ))
                ->end()
                ->booleanNode('enabled')->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
