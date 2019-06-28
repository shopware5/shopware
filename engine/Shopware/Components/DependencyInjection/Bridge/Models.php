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

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Shopware\Components\Model\Configuration;
use Shopware\Components\Model\LazyFetchModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryOperatorValidator;

/**
 * Wrapper service class for the doctrine entity manager.
 * The factory function creates the new instance of the entity manager.
 * The class constructor injects all required components and services
 * which required for the entity manager.
 */
class Models
{
    /**
     * Creates the entity manager for the application.
     *
     * @return ModelManager
     */
    public function factory(
        EventManager $eventManager,
        Configuration $config,
        \Enlight_Loader $loader,
        Connection $connection,
        // Annotation driver is not really used here but has to be loaded first
        AnnotationDriver $modelAnnotation,
        QueryOperatorValidator $operatorValidator = null
    ) {
        $loader->registerNamespace(
            'Shopware\Models\Attribute',
            $config->getAttributeDir()
        );

        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $connection->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        $entityManager = ModelManager::createInstance(
            $connection,
            $config,
            $eventManager,
            $operatorValidator
        );

        LazyFetchModelEntity::setEntityManager($entityManager);

        $this->generateAttributeModels($entityManager);

        return $entityManager;
    }

    /**
     * @param ModelManager $entityManager
     */
    protected function generateAttributeModels($entityManager)
    {
        // CustomerGroup model is arbitrarily chosen
        if (!class_exists('Shopware\Models\Attribute\CustomerGroup')) {
            $entityManager->generateAttributeModels();
        }
    }
}
