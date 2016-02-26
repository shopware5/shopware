<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Shopware\Components\Model\Configuration;

/**
 * Service class to initialize the doctrine annotation driver.
 * This driver is used to register the different model namespaces
 * Shopware\Models and Shopware\CustomModels.
 *
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ModelAnnotation
{
    /**
     * Creates the entity manager for the application.
     *
     * @param Configuration $config
     * @param string        $modelPath
     * @return AnnotationDriver
     */
    public function factory(Configuration $config, $modelPath)
    {
        $annotationDriver = new AnnotationDriver(
            $config->getAnnotationsReader(),
            array(
                $modelPath,
                $config->getAttributeDir(),
            )
        );

        // create a driver chain for metadata reading
        $driverChain = new MappingDriverChain();

        // register annotation driver for our application
        $driverChain->addDriver($annotationDriver, 'Shopware\\Models\\');
        $driverChain->addDriver($annotationDriver, 'Shopware\\CustomModels\\');

        $config->setMetadataDriverImpl($driverChain);

        return $annotationDriver;
    }
}
