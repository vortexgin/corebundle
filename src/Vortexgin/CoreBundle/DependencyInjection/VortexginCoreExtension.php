<?php

namespace Vortexgin\CoreBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VortexginCoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $path = str_replace('\\', '/', getcwd());

        $container->setParameter('vortexgin.core.host',             $config['host']);
        $container->setParameter('vortexgin.core.path',             $path);
        $container->setParameter('vortexgin.core.uploads_dir',      $config['uploads']['dir']);
        $container->setParameter('vortexgin.core.uploads_site',     $config['host'].$config['uploads']['dir']);
        $container->setParameter('vortexgin.core.uploads_path',     $path.'/'.$config['uploads']['dir']);
        $container->setParameter('vortexgin.core.image.mime_types', $config['image']['mime_types']);
        if(array_key_exists('kilatstorage', $config) && array_key_exists('access_key', $config) && !empty($config['kilatstorage']['access_key'])){
          $container->setParameter('vortexgin.core.kilatstorage.access_key', $config['kilatstorage']['access_key']);
        }
        if(array_key_exists('kilatstorage', $config) && array_key_exists('secret_key', $config) && !empty($config['kilatstorage']['secret_key'])){
          $container->setParameter('vortexgin.core.kilatstorage.secret_key', $config['kilatstorage']['secret_key']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
