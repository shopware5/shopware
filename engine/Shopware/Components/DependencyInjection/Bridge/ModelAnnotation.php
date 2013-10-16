<?php

namespace Shopware\Components\DependencyInjection\Bridge;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Shopware\Components\Model\Configuration;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ResourceLoader;

/**
 * Service class to initialize the doctrine annotation driver.
 * This driver is used to register the different model namespaces
 * Shopware\Models and Shopware\CustomModels.
 *
 * @package Shopware\DependencyInjection\Bridge
 */
class ModelAnnotation
{
    /**
     * Contains the shopware model configuration
     *
     * @var Configuration
     */
    protected $config;

    /**
     * Paths to the doctrine entities.
     *
     * @var
     */
    protected $modelPath;


    /**
     * Injects all required components.
     *
     * @param Configuration                            $config
     * @param string                                   $modelPath
     */
    public function __construct(Configuration $config, $modelPath)
    {
        $this->config = $config;
        $this->modelPath = $modelPath;
    }

    /**
     * Creates the entity manager for the application.
     *
     * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
     */
    public function factory()
    {
        $annotationDriver = new AnnotationDriver(
            $this->config->getAnnotationsReader(),
            array(
                $this->modelPath,
                $this->config->getAttributeDir(),
            )
        );

        // create a driver chain for metadata reading
        $driverChain = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();

        // register annotation driver for our application
        $driverChain->addDriver($annotationDriver, 'Shopware\\Models\\');
        $driverChain->addDriver($annotationDriver, 'Shopware\\CustomModels\\');

        $this->config->setMetadataDriverImpl($driverChain);

        return $annotationDriver;
    }
}
