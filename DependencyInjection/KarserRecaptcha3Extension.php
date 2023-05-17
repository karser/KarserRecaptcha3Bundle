<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KarserRecaptcha3Extension extends ConfigurableExtension implements PrependExtensionInterface
{
    public function loadInternal(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
        foreach ($configs as $key => $value) {
            $container->setParameter('karser_recaptcha3.'.$key, $value);
        }

        if (interface_exists(HttpClientInterface::class)) {
            $container->setAlias('karser_recaptcha3.google.request_method', 'karser_recaptcha3.request_method.symfony_http_client');
        } else {
            $container->removeDefinition('karser_recaptcha3.request_method.symfony_http_client');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('twig')) {
            // inject template
            $container->prependExtensionConfig('twig', ['form_themes' => ['@KarserRecaptcha3/Form/karser_recaptcha3_widget.html.twig']]);
        }
    }
}
