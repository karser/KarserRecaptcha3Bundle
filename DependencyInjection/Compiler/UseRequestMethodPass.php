<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class UseRequestMethodPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('http_client')) {
            $container->setAlias('karser_recaptcha3.google.request_method', 'karser_recaptcha3.request_method.symfony_http_client');
        } else {
            $container->removeDefinition('karser_recaptcha3.request_method.symfony_http_client');
        }
    }
}
