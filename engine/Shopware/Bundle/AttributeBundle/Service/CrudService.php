<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\AttributeBundle\Service;

use Exception;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\Configuration;

class CrudService implements CrudServiceInterface
{
    private ModelManager $entityManager;

    private SchemaOperatorInterface $schemaOperator;

    private TableMappingInterface $tableMapping;

    private TypeMappingInterface $typeMapping;

    public function __construct(
        ModelManager $entityManager,
        SchemaOperatorInterface $schemaOperator,
        TableMappingInterface $tableMapping,
        TypeMappingInterface $typeMapping
    ) {
        $this->entityManager = $entityManager;
        $this->schemaOperator = $schemaOperator;
        $this->tableMapping = $tableMapping;
        $this->typeMapping = $typeMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($table, $column, $updateDependingTables = false)
    {
        $column = $this->formatColumnName($column);

        if (!$this->tableMapping->isTableColumn($table, $column)) {
            throw new RuntimeException(sprintf('Table %s has no column with name %s', $table, $column));
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function get($table, $columnName)
    {
        $columnName = $this->formatColumnName($columnName);

        $columns = $this->getList($table);
        foreach ($columns as $column) {
            if ($column->getColumnName() === $columnName) {
                return $column;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
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
            $default = $column->getDefault();

            if ($default === 'NULL') {
                $default = null;
            }

            $item = new ConfigurationStruct();
            $item->setTableName($table);
            $item->setColumnName($column->getName());
            $item->setIdentifier($this->tableMapping->isIdentifierColumn($table, $column->getName()));
            $item->setCore($this->tableMapping->isCoreColumn($table, $column->getName()));
            $item->setColumnType($this->typeMapping->dbalToUnified($column->getType()));
            $item->setElasticSearchType($this->typeMapping->unifiedToElasticSearch($item->getColumnType()));
            $item->setDefaultValue($default);

            if (isset($configuration[$name])) {
                $config = $configuration[$name];
                $item->setId((int) $config['id']);
                $item->setColumnType($config['columnType']);
                $item->setSupportText($config['supportText']);
                $item->setHelpText($config['helpText']);
                $item->setDisplayInBackend((bool) $config['displayInBackend']);
                $item->setReadonly((bool) $config['readonly']);
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
                $item->setDefaultValue($config['defaultValue'] === 'NULL' ? null : $config['defaultValue']);
            }
            $items[] = $item;
        }

        usort($items, function (ConfigurationStruct $a, ConfigurationStruct $b) {
            if ($a->getPosition() === null && $b->getPosition() !== null) {
                return 1;
            }
            if ($b->getPosition() === null && $a->getPosition() !== null) {
                return -1;
            }
            if ($a->getPosition() == $b->getPosition()) {
                return strnatcasecmp($a->getColumnName(), $b->getColumnName());
            }

            return $a->getPosition() <=> $b->getPosition();
        });

        return $items;
    }

    private function updateConfig(?int $id, array $data): void
    {
        $model = null;

        if ($id) {
            $model = $this->entityManager->find(Configuration::class, $id);
        }

        if (isset($data['arrayStore']) && \is_array($data['arrayStore'])) {
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
     * @param string|int|float|null $defaultValue
     */
    private function schemaChanged(ConfigurationStruct $config, string $name, string $type, $defaultValue = null): bool
    {
        return $config->getColumnType() !== $type
            || $config->getColumnName() !== $name
            || $config->getDefaultValue() != $defaultValue
        ;
    }

    private function getTableConfiguration(string $table): array
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select('configuration')
            ->from(Configuration::class, 'configuration', 'configuration.columnName')
            ->where('configuration.tableName = :tableName')
            ->orderBy('configuration.position')
            ->setParameter('tableName', $table);

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param string|int|float|null $defaultValue
     *
     * @throws Exception
     */
    private function createAttribute(string $table, string $column, string $unifiedType, array $data = [], $defaultValue = null): void
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
        if (\array_key_exists('id', $data)) {
            $configId = $data['id'];
        }

        $this->updateConfig($configId, $data);
    }

    /**
     * @param string|int|float|null $defaultValue
     *
     * @throws Exception
     */
    private function changeAttribute(
        string $table,
        string $originalColumnName,
        string $newColumnName,
        string $unifiedType,
        array $data = [],
        $defaultValue = null
    ): void {
        $config = $this->get($table, $originalColumnName);
        if (!$config instanceof ConfigurationStruct) {
            return;
        }

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
     * @param string|int|float|null $defaultValue
     *
     * @return string|int|float
     */
    private function parseDefaultValue(string $type, $defaultValue)
    {
        $types = $this->typeMapping->getTypes();
        $typeArray = $types[$type];

        if ($typeArray['unified'] === TypeMappingInterface::TYPE_BOOLEAN) {
            return (bool) $defaultValue === true ? 1 : 0;
        }
        if (!$typeArray['allowDefaultValue'] || $defaultValue === null) {
            return CrudServiceInterface::NULL_STRING;
        }
        if ($defaultValue === CrudServiceInterface::NULL_STRING) {
            return $defaultValue;
        }
        if ($typeArray['quoteDefaultValue']) {
            return $this->entityManager->getConnection()->quote($defaultValue);
        }

        return $defaultValue;
    }

    /**
     * Process the column name to handle edge cases
     */
    private function formatColumnName(string $column): string
    {
        return strtolower($column);
    }
}
