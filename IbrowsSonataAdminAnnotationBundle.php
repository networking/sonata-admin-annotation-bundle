<?php

namespace Ibrows\Bundle\SonataAdminAnnotationBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

class IbrowsSonataAdminAnnotationBundle extends Bundle
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if($this->kernel){
            $bundles = array_keys($this->kernel->getBundles());
            $sonataKey = array_search('SonataAdminBundle', $bundles);
            $myKey = array_search('IbrowsSonataAdminAnnotationBundle', $bundles);

            if($sonataKey < $myKey){
                throw new \RuntimeException("Please register IbrowsSonataAdminAnnotationBundle before SonataAdminBundle in AppKernel");
            }
        }

        $container->addCompilerPass(new AutoServiceCompilerPass());
    }
}