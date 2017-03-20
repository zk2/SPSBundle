<?php

namespace Zk2\SpsBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zk2\SpsBundle\DependencyInjection\TwigExtensionCompilerPass;

class Zk2SpsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TwigExtensionCompilerPass());
    }
}
