<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class KarserRecaptcha3Extension extends ConfigurableExtension
{
    public function loadInternal(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        foreach ($configs as $key => $value) {
            $container->setParameter('karser_recaptcha3.'.$key, $value);
        }
        $this->injectTemplate($container);
    }

    private function injectTemplate(ContainerBuilder $container)
    {
        $resources = $container->getParameter('twig.form.resources');
        $resources[] = '@KarserRecaptcha3/Form/karser_recaptcha3_widget.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }
}
