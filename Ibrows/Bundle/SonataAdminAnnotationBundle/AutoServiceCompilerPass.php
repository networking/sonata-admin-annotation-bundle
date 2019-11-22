<?php

/**
 * Created by PhpStorm.
 * User: Mike Meier <mike.meier@ibrows.ch>
 * Date: 10.10.14
 * Time: 15:57
 */

namespace Ibrows\Bundle\SonataAdminAnnotationBundle;

use Ibrows\AnnotationReader\AnnotationReaderInterface;
use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation\AutoService;
use mikemeier\MoserArtzBundle\Entity\User;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class AutoServiceCompilerPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('ibrows_sonata_admin_annotation.autoservice')) {
            return;
        }

        $configuration = $container->getParameter('ibrows_sonata_admin_annotation.autoservice');

        $entityConfigs = isset($configuration['entities']) ? $configuration['entities'] : array();

        if (!$entityConfigs) {
            return;
        }

        $annotationReader = $container->get('ibrows_sonataadmin.annotation.reader');

        foreach ($entityConfigs as $entityConfig) {
            foreach ($this->getEntityServices($entityConfig, $configuration, $annotationReader) as $serviceId => $definition) {
                $container->setDefinition($serviceId, $definition);
            }
        }
    }

    /**
     * @param array $entityConfig
     * @param array $configuration
     * @param AnnotationReaderInterface $annotationReader
     * @return Definition[]
     */
    protected function getEntityServices(array $entityConfig, array $configuration, AnnotationReaderInterface $annotationReader)
    {
        $services = array();

        $directory = $entityConfig['directory'];
        $namespacePrefix = $entityConfig['prefix'];
        $replacedConfig = array_replace($configuration['default_entity'], $entityConfig);

        $finder = new Finder();
        $idPrefix = $configuration['service_id_prefix'];

        /** @var SplFileInfo $file */
        foreach ($finder->in($directory)->files('*.php') as $file) {
            $baseName = $file->getBasename('.php');
            $relativePath = $file->getRelativePath();

            $namespacePieces = array_filter(array($namespacePrefix, str_replace("/", "\\", $relativePath), $baseName));
            $idPieces = array_filter(array(str_replace("/", ".", $relativePath), $baseName));

            $className = implode("\\", $namespacePieces);

            /** @var AutoService $autoServiceAnnotation */
            if ($autoServiceAnnotation = $annotationReader->getAnnotationsByType($className, 'AutoServiceInterface', $annotationReader::SCOPE_CLASS)) {
                $id = $idPrefix . '.' . implode(".", array_map('strtolower', $idPieces));
                $services[$id] = $this->getEntityService($autoServiceAnnotation, $replacedConfig, $className, $baseName);
            }
        }

        return $services;
    }

    /**
     * @param AutoService $autoServiceAnnotation
     * @param array $replacedConfig
     * @param string $className
     * @param string $baseName
     * @return Definition
     */
    protected function getEntityService(AutoService $autoServiceAnnotation, array $replacedConfig, $className, $baseName)
    {
        if (!$admin = $autoServiceAnnotation->admin ?: $this->getConfigValue($replacedConfig, 'admin')) {
            throw new \RuntimeException("Cannot determine SonataAdmin for " . $className . " provide one in configuration or on the AutoServiceAnnotation");
        }

        if (!$controller = $autoServiceAnnotation->controller ?: $this->getConfigValue($replacedConfig, 'controller')) {
            throw new \RuntimeException("Cannot determine SonataAdminController for " . $className . " provide one in configuration or on the AutoServiceAnnotation");
        }

        $service = new Definition($admin, array(null, $className, $controller));

        $service->addTag(
            'sonata.admin',
            array(
                'manager_type'              => 'orm',
                'group'                     => $autoServiceAnnotation->group ?: $baseName,
                'label'                     => $autoServiceAnnotation->label ?: $baseName,
                'show_in_dashboard'         => !is_null($autoServiceAnnotation->showInDashboard) ? $autoServiceAnnotation->showInDashboard : $this->getConfigValue($replacedConfig, 'show_in_dashboard'),
                'label_translator_strategy' => $autoServiceAnnotation->labelTranslatorStrategy ?: $this->getConfigValue($replacedConfig, 'label_translator_strategy'),
                'label_catalogue'           => $autoServiceAnnotation->labelCatalog ?: $this->getConfigValue($replacedConfig, 'label_catalogue'),
            )
        );

        return $service;
    }

    /**
     * @param array $config
     * @param string $key
     * @return null|string
     */
    protected function getConfigValue(array $config, $key)
    {
        return isset($config[$key]) ? $config[$key] : null;
    }
}
