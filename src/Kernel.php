<?php

namespace App;

use IdentityAccess\Infrastructure\Framework\Extension\IdentityAccessModuleExtension;
use Notifier\Infrastructure\Framework\Extension\NotifierModuleExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->registerExtension(new IdentityAccessModuleExtension());
        $container->registerExtension(new NotifierModuleExtension());
    }
}
