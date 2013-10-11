<?php

namespace Shopware\DependencyInjection\Bridge;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Shopware\Components\Model\CategoryDenormalization;
use Shopware\Components\Model\CategorySubscriber;
use Shopware\Components\Model\Configuration;
use Shopware\Components\Model\EventSubscriber;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\OrderHistorySubscriber;

/**
 * Wrapper service class for the doctrine entity manager.
 * The factory function creates the new instance of the entity manager.
 * The class constructor injects all required components and services
 * which required for the entity manager.
 *
 * @package Shopware\DependencyInjection\Bridge
 */
class Models
{
    /**
     * Contains the shopware model configuration
     * @var Configuration
     */
    protected $config;

    /**
     * Paths to the doctrine entities.
     * @var
     */
    protected $modelPath;

    /**
     * Contains the current application auto loader.
     * Used to register additional namespaces
     *
     * @var \Enlight_Loader
     */
    protected $loader;

    /**
     * Contains the application event manager which is used
     * to inject it into the doctrine event manager.
     *
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * Contains the current application database pdo adapter.
     * This adapter is injected into the doctrine environment for the
     * database connection of doctrine processes.
     *
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * Instance of the application resource loader.
     * The loader is used to load resources or to add dynamically
     * new resource at runtime.
     * The model service class use it to add the shopware specified
     * doctrine event subscribers like the CategoryDenormalization.
     *
     * @var \Enlight_Components_ResourceLoader
     */
    protected $resourceLoader;

    /**
     * Contains the directory path of the shopware installation.
     *
     * @var string
     */
    protected $kernelRootDir;

    /**
     * Injects all required components.
     *
     * @param Configuration                            $config
     * @param \Enlight_Loader                          $loader
     * @param \Enlight_Event_EventManager              $eventManager
     * @param \Enlight_Components_Db_Adapter_Pdo_Mysql $db
     * @param \Enlight_Components_ResourceLoader       $resourceLoader
     * @param                                          $modelPath
     * @param                                          $kernelRootDir
     */
    public function __construct(
        Configuration $config,
        \Enlight_Loader $loader,
        \Enlight_Event_EventManager $eventManager,
        \Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        \Enlight_Components_ResourceLoader $resourceLoader,
        $modelPath,
        $kernelRootDir
    ) {
        $this->config = $config;
        $this->modelPath = $modelPath;
        $this->loader = $loader;
        $this->eventManager = $eventManager;
        $this->db = $db;
        $this->resourceLoader = $resourceLoader;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Creates the entity manager for the application.
     * @return ModelManager
     */
    public function factory()
    {
        // register standard doctrine annotations
        AnnotationRegistry::registerFile(
            'Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
        );

        // register symfony validation annotations
        AnnotationRegistry::registerAutoloadNamespace(
            'Symfony\Component\Validator\Constraint',
            realpath($this->kernelRootDir . '/vendor/symfony/validator')
        );

        $cachedAnnotationReader = $this->config->getAnnotationsReader();

        $annotationDriver = new AnnotationDriver(
            $cachedAnnotationReader,
            array(
                $this->modelPath,
                $this->config->getAttributeDir(),
            )
        );

        $this->loader->registerNamespace(
            'Shopware\Models\Attribute',
            $this->config->getAttributeDir()
        );

        // create a driver chain for metadata reading
        $driverChain = new DriverChain();

        // register annotation driver for our application
        $driverChain->addDriver($annotationDriver, 'Shopware\\Models\\');
        $driverChain->addDriver($annotationDriver, 'Shopware\\CustomModels\\');

        $this->resourceLoader->registerResource('ModelAnnotations', $annotationDriver);

        $this->config->setMetadataDriverImpl($driverChain);

        // Create event Manager
        $eventManager = new EventManager();

        // Create new shopware event subscriber to handle the entity lifecycle events.
        $lifeCycleSubscriber = new EventSubscriber(
            $this->eventManager
        );
        $eventManager->addEventSubscriber($lifeCycleSubscriber);

        $categorySubscriber = new CategorySubscriber();

        $this->resourceLoader->registerResource('CategorySubscriber', $categorySubscriber);
        $eventManager->addEventSubscriber($categorySubscriber);

        $eventManager->addEventSubscriber(new OrderHistorySubscriber());

        $categoryDenormalization = new CategoryDenormalization(
            $this->db->getConnection()
        );

        $this->resourceLoader->registerResource('CategoryDenormalization', $categoryDenormalization);

        // now create the entity manager and use the connection
        // settings we defined in our application.ini
        $conn = DriverManager::getConnection(
            array('pdo' => $this->db->getConnection()),
            $this->config,
            $eventManager
        );

        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        $entityManager = ModelManager::create(
            $conn, $this->config, $eventManager
        );

        \Doctrine\ORM\Proxy\Autoloader::register(
            $this->config->getProxyDir(),
            $this->config->getProxyNamespace(),
            function ($proxyDir, $proxyNamespace, $className) use ($entityManager) {
                if (0 === stripos($className, $proxyNamespace)) {
                    $fileName = str_replace('\\', '', substr($className, strlen($proxyNamespace) + 1));
                    if (!is_file($fileName)) {
                        $classMetadata = $entityManager->getClassMetadata($className);
                        $entityManager->getProxyFactory()->generateProxyClasses(array($classMetadata), $proxyDir);
                    }
                }
            }
        );

        return $entityManager;
    }
}
