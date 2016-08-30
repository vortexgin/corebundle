<?php

namespace Vortexgin\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vortexgin\CoreBundle\DependencyInjection\Compiler\CorePass;

class VortexginCoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new CorePass());
    }
}
