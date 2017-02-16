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

namespace Shopware\Components\Plugin;

use DateTime;
use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;

/**
 * Class AttributeSynchronizer
 * @package Shopware\Components\Plugin
 */
class AttributeSynchronizer
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $models;

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * AttributeSynchronizer constructor.
     * @param Connection $connection
     * @param ModelManager $models
     * @param CrudService $crudService
     */
    public function __construct(Connection $connection, ModelManager $models, CrudService $crudService)
    {
        $this->connection = $connection;
        $this->models = $models;
        $this->crudService = $crudService;
    }

    /**
     * @param array $attributes
     * @throws \InvalidArgumentException
     */
    public function installAttributes(array $attributes)
    {
        foreach ($attributes as $table => $attribute) {
            $this->addAttributes($table, $attribute);
        }

        $this->models->generateAttributeModels(array_keys($attributes));
    }

    /**
     * @param array $attributes
     * @throws \InvalidArgumentException
     */
    public function uninstallAttributes(array $attributes)
    {
        foreach ($attributes as $table => $attribute) {
            $this->removeAttributes($table, $attribute);
        }

        $this->models->generateAttributeModels(array_keys($attributes));
    }

    /**
     * @param string $tableName
     * @param array $attributeConfigurations
     */
    private function addAttributes($tableName, $attributeConfigurations)
    {
        foreach ($attributeConfigurations as $attributeConfiguration) {
            $this->crudService->update(
                $tableName,
                $attributeConfiguration['name'],
                $attributeConfiguration['type'],
                $attributeConfiguration,
                null,
                $attributeConfiguration['updateDependingTables'],
                $attributeConfiguration['defaultValue']
            );
        }
    }

    /**
     * @param string $tableName
     * @param array $attributeConfigurations
     */
    private function removeAttributes($tableName, $attributeConfigurations)
    {
        foreach ($attributeConfigurations as $attributeConfiguration) {
            $this->crudService->delete(
                $tableName,
                $attributeConfiguration['name'],
                $attributeConfiguration['updateDependingTables']
            );
        }
    }
}
