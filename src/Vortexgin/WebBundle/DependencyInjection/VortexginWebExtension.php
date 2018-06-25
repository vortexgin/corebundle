<?php

namespace Vortexgin\WebBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 * 
 * @category DependencyInjection
 * @package  Vortexgin\WebBundle\DependencyInjection
 * @author   vortexgin <vortexgin@gmail.com>
 * @license  Apache 2.0
 * @link     https://github.com/vortexgin/corebundle
 */
class VortexginWebExtension extends Extension
{
    /**
     * {@inheritdoc}
     * 
     * @param array                                                  $configs   Config parameter
     * @param Symfony\Component\DependencyInjection\ContainerBuilder $container Container interface
     * 
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $env = $container->getParameter('kernel.environment');
        $container->setParameter('vortexgin.library.endpoint.bridge', $config['bridge']['endpoint']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
