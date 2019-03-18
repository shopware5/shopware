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

namespace Shopware\Bundle\AttributeBundle\Service;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\Configuration;

class CrudService
{
    const EXT_JS_PREFIX = '__attribute_';
    const NULL_STRING = 'NULL';

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var SchemaOperator
     */
    private $schemaOperator;

    /**
     * @var TableMapping
     */
    private $tableMapping;

    /**
     * @var TypeMapping
     */
    private $typeMapping;

    public function __construct(
        ModelManager $entityManager,
        SchemaOperator $schemaOperator,
        TableMapping $tableMapping,
        TypeMapping $typeMapping
    ) {
        $this->entityManager = $entityManager;
        $this->schemaOperator = $schemaOperator;
        $this->tableMapping = $tableMapping;
        $this->typeMapping = $typeMapping;
    }

    /**
     * @param string $table
     * @param string $column
     * @param bool   $updateDependingTables
     *
     * @throws \Exception
     */
    public function delete($table, $column, $updateDependingTables = false)
    {
        $column = $this->formatColumnName($column);

        if (!$this->tableMapping->isTableColumn($table, $column)) {
            throw new \RuntimeException(sprintf('Table %s has no column with name %s', $table, $column));
        }

        $this->schemaOperator->dropColumn($table, $column);

        $repository = $this->entityManager->getRepository(Configuration::class);

        $entity = $repository->findOneBy([
            'tableName' => $table,
            'columnName' => $column,
        ]);

        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush($entity);
        }

        if (!$updateDependingTables) {
            return;
        }

        $dependingTables = $this->tableMapping->getDependingTables($table);
        foreach ($dependingTables as $dependingTable) {
            $this->delete($dependingTable, $column);
        }
    }

    /**
     * Translations for different fields (help, support, label) can be configured via snippets.
     * Snippet namespace         :  backend/attribute_columns
     * Snippet name label        :  s_articles_attributes_attr1_label
     * Snippet name support text :  s_articles_attributes_attr1_supportText
     * Snippet name help text    :  s_articles_attributes_attr1_helpText
     *
     * @param string                $table
     * @param string                $columnName
     * @param string                $unifiedType
     * @param string|null           $newColumnName
     * @param bool                  $updateDependingTables
     * @param string|int|float|null $defaultValue
     *
     * @throws \Exception
     */
    public function update(
        $table,
        $columnName,
        $unifiedType,
        array $data = [],
        $newColumnName = null,
        $updateDependingTables = false,
        $defaultValue = null
    ) {
        $columnName = $this->formatColumnName($columnName);
        $newColumnName = $newColumnName ? $this->formatColumnName($newColumnName) : $columnName;

        $config = $this->get($table, $columnName);

        if (!$config) {
            $this->createAttribute($table, $columnName, $unifiedType, $data, $defaultValue);
        } else {
            $this->changeAttribute($table, $columnName, $newColumnName, $unifiedType, $data, $defaultValue);
        }

        if (!$updateDependingTables) {
            return;
        }

        $dependingTables = $this->tableMapping->getDependingTables($table);
        foreach ($dependingTables as $dependingTable) {
            $this->update($dependingTable, $columnName, $unifiedType, $data, $newColumnName, false, $defaultValue);
        }
    }

    /**
     * @param string $table
     * @param string $columnName
     *
     * @return ConfigurationStruct|null
     */
    public function get($table, $columnName)
    {
        $columnName = $this->formatColumnName($columnName);

        $columns = $this->getList($table);
        foreach ($columns as $column) {
            if ($column->getColumnName() == $columnName) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @param string $table
     *
     * @return ConfigurationStruct[]
     */
    public function getList($table)
    {
        if (!$this->tableMapping->isAttributeTable($table)) {
            return [];
        }

        $columns = $this->tableMapping->getTableColumns($table);
        $configuration = $this->getTableConfiguration($table);

        $items = [];
        foreach ($columns as $column) {
            $name = strtolower($column->getName());

            $item = new ConfigurationStruct();
            $item->setTableName($table);
            $item->setColumnName($column->getName());
            $item->setIdentifier($this->tableMapping->isIdentifierColumn($table, $column->getName()));
            $item->setCore($this->tableMapping->isCoreColumn($table, $column->getName()));
            $item->setColumnType($this->typeMapping->dbalToUnified($column->getType()));
            $item->setElasticSearchType($this->typeMapping->unifiedToElasticSearch($item->getColumnType()));
            $item->setDefaultValue($column->getDefault());

            if (isset($configuration[$name])) {
                $config = $configuration[$name];
                $item->setId((int) $config['id']);
                $item->setColumnType($config['columnType']);
                $item->setSupportText($config['supportText']);
                $item->setHelpText($config['helpText']);
                $item->setDisplayInBackend((bool) $config['displayInBackend']);
                $item->setLabel($config['label']);
                $item->setPosition((int) $config['position']);
                $item->setCustom((bool) $config['custom']);
                $item->setTranslatable((bool) $config['translatable']);
                $item->setConfigured(true);
                $item->setDbalType($column->getType()->getName());
                $item->setSqlType($this->typeMapping->unifiedToSQL($item->getColumnType()));
                $item->setEntity($config['entity']);
                $item->setArrayStore($config['arrayStore']);
                $item->setElasticSearchType($this->typeMapping->unifiedToElasticSearch($config['columnType']));
                $item->setDefaultValue($config['defaultValue']);
            }
            $items[] = $item;
        }

        usort($items, function (ConfigurationStruct $a, ConfigurationStruct $b) {
            if ($a->getPosition() === null && $b->getPosition() !== null) {
                return true;
            }
            if ($b->getPosition() === null && $a->getPosition() !== null) {
                return false;
            }
            if ($a->getPosition() == $b->getPosition()) {
                return strnatcasecmp($a->getColumnName(), $b->getColumnName());
            }

            return $a->getPosition() > $b->getPosition();
        });

        return $items;
    }

    /**
     * @param int|null $id
     */
    private function updateConfig($id, array $data)
    {
        $model = null;

        if ($id) {
            $model = $this->entityManager->find('Shopware\Models\Attribute\Configuration', $id);
        }

        if (isset($data['arrayStore']) && is_array($data['arrayStore'])) {
            $data['arrayStore'] = json_encode($data['arrayStore']);
        }

        if (!$model) {
            $model = new Configuration();
            $this->entityManager->persist($model);
        }

        $model->fromArray($data);
        $this->entityManager->flush($model);
    }

    /**
     * @param string                $name
     * @param string                $type
     * @param string|int|float|null $defaultValue
     *
     * @return bool
     */
    private function schemaChanged(ConfigurationStruct $config, $name, $type, $defaultValue = null)
    {
        return $config->getColumnType() !== $type
            || $config->getColumnName() !== $name
            || $config->getDefaultValue() != $defaultValue
        ;
    }

    /**
     * @param string $table
     *
     * @return array
     */
    private function getTableConfiguration($table)
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select('configuration')
            ->from('Shopware\Models\Attribute\Configuration', 'configuration', 'configuration.columnName')
            ->where('configuration.tableName = :tableName')
            ->orderBy('configuration.position')
            ->setParameter('tableName', $table);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param string                $table
     * @param string                $column
     * @param string                $unifiedType
     * @param string|int|float|null $defaultValue
     *
     * @throws \Exception
     */
    private function createAttribute($table, $column, $unifiedType, array $data = [], $defaultValue = null)
    {
        $this->schemaOperator->createColumn(
            $table,
            $column,
            $this->typeMapping->unifiedToSQL($unifiedType),
            $this->parseDefaultValue($unifiedType, $defaultValue)
        );

        $data = array_merge($data, [
            'tableName' => $table,
            'columnName' => $column,
            'columnType' => $unifiedType,
            'defaultValue' => $defaultValue,
        ]);

        $configId = null;
        if (array_key_exists('id', $data)) {
            $configId = $data['id'];
        }

        $this->updateConfig($configId, $data);
    }

    /**
     * @param string                $table
     * @param string                $originalColumnName
     * @param string                $newColumnName
     * @param string                $unifiedType
     * @param string|int|float|null $defaultValue
     *
     * @throws \Exception
     */
    private function changeAttribute(
        $table,
        $originalColumnName,
        $newColumnName,
        $unifiedType,
        array $data = [],
        $defaultValue = null
    ) {
        $config = $this->get($table, $originalColumnName);

        $data = array_merge($data, [
            'tableName' => $table,
            'columnName' => $newColumnName,
            'columnType' => $unifiedType,
            'defaultValue' => $defaultValue,
        ]);

        $this->updateConfig($config->getId(), $data);

        $schemaChanged = $this->schemaChanged(
            $config,
            $newColumnName,
            $unifiedType,
            $defaultValue
        );

        if (!$schemaChanged) {
            return;
        }

        $this->schemaOperator->changeColumn(
            $config->getTableName(),
            $originalColumnName,
            $newColumnName,
            $this->typeMapping->unifiedToSQL($unifiedType),
            $this->parseDefaultValue($unifiedType, $defaultValue)
        );
    }

    /**
     * @param string                $type
     * @param string|int|float|null $defaultValue
     *
     * @return string|int|float|null
     */
    private function parseDefaultValue($type, $defaultValue)
    {
        $types = $this->typeMapping->getTypes();
        $type = $types[$type];

        if ($type['unified'] === TypeMapping::TYPE_BOOLEAN) {
            return (bool) $defaultValue === true ? 1 : 0;
        }
        if (!$type['allowDefaultValue'] || $defaultValue === null) {
            return self::NULL_STRING;
        }
        if ($defaultValue == self::NULL_STRING) {
            return $defaultValue;
        }
        if ($type['quoteDefaultValue'] && $defaultValue !== null) {
            return $this->entityManager->getConnection()->quote($defaultValue);
        }

        return $defaultValue;
    }

    /**
     * Process the column name to handle edge cases
     *
     * @param string $column
     *
     * @return string
     */
    private function formatColumnName($column)
    {
        return strtolower($column);
    }
}
