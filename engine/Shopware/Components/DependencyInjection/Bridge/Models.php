<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\DependencyInjection\Bridge;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Proxy\Autoloader;
use Shopware\Components\Model\Configuration;
use Shopware\Components\Model\ModelManager;

/**
 * Wrapper service class for the doctrine entity manager.
 * The factory function creates the new instance of the entity manager.
 * The class constructor injects all required components and services
 * which required for the entity manager.
 *
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
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
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Contains the current application database pdo adapter.
     * This adapter is injected into the doctrine environment for the
     * database connection of doctrine processes.
     *
     * @var \Pdo
     */
    protected $db;

    /**
     * Contains the directory path of the shopware installation.
     *
     * @var string
     */
    protected $kernelRootDir;

    /**
     * @param EventManager      $eventManager
     * @param Configuration     $config
     * @param \Enlight_Loader   $loader
     * @param \Pdo              $db
     * @param string            $modelPath
     * @param string            $kernelRootDir
     * @param AnnotationDriver  $modelAnnotation
     */
    public function __construct(
        EventManager $eventManager,
        Configuration $config,
        \Enlight_Loader $loader,
        \Pdo $db,
        $modelPath,
        $kernelRootDir,
        AnnotationDriver $modelAnnotation
    ) {
        $this->eventManager = $eventManager;
        $this->config = $config;
        $this->modelPath = $modelPath;
        $this->loader = $loader;
        $this->db = $db;
        $this->kernelRootDir = $kernelRootDir;

        // annotation driver is not really used here but has to be loaded first
        $this->modelAnnotation = $modelAnnotation;
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

        $this->loader->registerNamespace(
            'Shopware\Models\Attribute',
            $this->config->getAttributeDir()
        );

        // now create the entity manager and use the connection
        // settings we defined in our application.ini
        $conn = DriverManager::getConnection(
            array('pdo' => $this->db),
            $this->config,
            $this->eventManager
        );

        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $conn->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        $entityManager = ModelManager::create(
            $conn,
            $this->config,
            $this->eventManager
        );

        Autoloader::register(
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
