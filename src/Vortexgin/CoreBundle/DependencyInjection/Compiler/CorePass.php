<?php

namespace Vortexgin\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CorePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $path = str_replace('\\', '/', getcwd());
        $uploads_dir = $path.'/uploads';
        if(!file_exists($uploads_dir) && !is_dir($uploads_dir)){
            @mkdir($uploads_dir);
        }
    }
}
