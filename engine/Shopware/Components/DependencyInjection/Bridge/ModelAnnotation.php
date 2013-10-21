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
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Shopware\Components\Model\Configuration;

/**
 * Service class to initialize the doctrine annotation driver.
 * This driver is used to register the different model namespaces
 * Shopware\Models and Shopware\CustomModels.
 *
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
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
