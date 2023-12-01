<?php declare(strict_types=1);

namespace Karser\Recaptcha3Bundle;

use Karser\Recaptcha3Bundle\DependencyInjection\Compiler\UseRequestMethodPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KarserRecaptcha3Bundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new UseRequestMethodPass());
    }
}
