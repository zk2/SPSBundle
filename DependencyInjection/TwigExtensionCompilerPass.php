<?php
/**
 * This file is part of the SpsBundle.
 *
 * (c) Evgeniy Budanov <budanov.ua@gmail.comm> 2017.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace Zk2\SpsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Zk2\SpsBundle\Model\TdBuilderInterface;

/**
 * Class TwigExtensionCompilerPass
 */
class TwigExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->getParameter('zk2_sps.td_builder_service_class')) {
            return;
        }

        /** @var TdBuilderInterface $serviceClass */
        $serviceClass = $container->getParameter('zk2_sps.td_builder_service_class');

        $tdBuilderService = new Definition($serviceClass);

        if ($container->getParameter('zk2_sps.full_path_to_web_root')) {
            $tdBuilderService->addMethodCall(
                'setFullWebPath',
                [$container->getParameter('zk2_sps.full_path_to_web_root')]
            );
        }

        foreach ($serviceClass::getInjections() as $setter => $service) {
            $tdBuilderService->addMethodCall($setter, [new Reference($service)]);
        }

        $container->setDefinition('zk2.sps.td_builder', $tdBuilderService);
        $twigExtension = $container->findDefinition('zk2.sps.twig_extension');
        $twigExtension->replaceArgument(0, $tdBuilderService);
    }
}
