<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Components_Model
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

namespace Shopware\Components\Model;
use Doctrine\ORM\Tools\EntityGenerator,
    Doctrine\ORM\Tools\DisconnectedClassMetadataFactory,
    Doctrine\ORM\ORMException,
    Doctrine\Common\EventManager,
    Doctrine\DBAL\Connection,
    Doctrine\Common\Util\Inflector,
    Doctrine\ORM\Mapping\ClassMetadata;

class Generator extends EntityGenerator
{
    protected static $_classTemplate =
        '<?php

<namespace>

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

<entityAnnotation>
<entityClassName> extends ModelEntity
{
<entityBody>
}';

    public $tableMappings = array();

    /**
     * @var ModelManager
     */
    protected $em = null;

    /**
     * Generates the models for the extension tables like s_user_extension.
     * This tables can be modified from plugins so we have to generate the models after each plugin installation.
     */
    public function generateAttributeModels($entityManager, $path, $tableNames = array())
    {
        $this->em = $entityManager;

        $this->setGenerateAnnotations(true);
        $this->setGenerateStubMethods(true);
        $this->setRegenerateEntityIfExists(true);
        $this->setUpdateEntityIfExists(false);
        $this->setBackupExisting(false);

        $tableMapping = $this->getTableMapping();
        $this->tableMappings = $tableMapping;
        $allMetaData = $this->getAttributeTablesMetaData();

        if (count($allMetaData) > 0) {
            /**@var $metaData \Doctrine\ORM\Mapping\ClassMetadata*/
            foreach ($allMetaData as $metaData) {
                if (empty($metaData)) {
                    continue;
                }

                if (count($tableNames) && !in_array($metaData->getTableName(), $tableNames)) {
                    continue;
                }

                if (array_key_exists($metaData->name, $tableMapping)) {
                    $this->writeEntityClass($metaData, $path);
                }
            }
        }
    }

    /**
     * Generate a PHP5 Doctrine 2 entity class from the given ClassMetadataInfo instance
     *
     * @param ClassMetadataInfo $metadata
     * @return string $code
     */
    public function generateEntityClass(ClassMetadataInfo $metadata)
    {
        $placeHolders = array(
            '<namespace>',
            '<entityAnnotation>',
            '<entityClassName>',
            '<entityBody>'
        );

        $replacements = array(
            $this->_generateEntityNamespace($metadata),
            $this->_generateEntityDocBlock($metadata),
            $this->_generateEntityClassName($metadata),
            $this->_generateEntityBody($metadata)
        );

        $code = str_replace($placeHolders, $replacements, self::$_classTemplate);
        return str_replace('<spaces>', $this->_spaces, $code);
    }

    /**
     * @return array
     */
    private function getAttributeTablesMetaData()
    {
        $factory = $this->getFactoryWithDatabaseDriver();
        $allMetaData = $factory->getAllMetadata();
        $extensionTables = array();

        /**@var $metaData \Doctrine\ORM\Mapping\ClassMetadata*/
        foreach ($allMetaData as $metaData) {
            if (strpos($metaData->getTableName(), '_attributes') !== false) {
                $extensionTables[] = $metaData;
            }
        }
        return $extensionTables;
    }

    /**
     * Creates a mapping array for each shopware model.
     * The array key is the table name and the array item contains the model name and the namespace of the model.
     * @return array
     */
    private function getTableMapping()
    {
        $driver = $this->getDatabaseDriver();
        $allMetaData = $this->em->getMetadataFactory()->getAllMetadata();
        $tableMapping = array();
        /**@var $metaData \Doctrine\ORM\Mapping\ClassMetadata*/
        foreach ($allMetaData as $metaData) {
            $arrayKey = $driver->getClassNameForTable($metaData->getTableName());
            $tableMapping[$arrayKey] = array(
                'namespace' => $metaData->namespace,
                'name' => str_replace($metaData->namespace . '\\', '', $metaData->name)
            );
        }

        return $tableMapping;
    }

    /**
     * Creates a disconnected meta data factory with a database mapping driver
     * to get the meta data for the extension tables directly from the database.
     *
     * @return Doctrine\ORM\Tools\DisconnectedClassMetadataFactory
     */
    private function getFactoryWithDatabaseDriver()
    {
        $driver = $this->getDatabaseDriver();
        $this->em->getConfiguration()->setMetadataDriverImpl($driver);
        $factory = new DisconnectedClassMetadataFactory();
        $factory->setEntityManager($this->em);
        return $factory;
    }

    /**
     * @return \Shopware\Components\Model\DatabaseDriver
     */
    private function getDatabaseDriver()
    {
        $platform = $this->em->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');
        $driver = new \Shopware\Components\Model\DatabaseDriver(
            $this->em->getConnection()->getSchemaManager()
        );
        return $driver;
    }

    /**
     * Generated and write entity class to disk for the given ClassMetadataInfo instance
     *
     * @param ClassMetadataInfo $metadata
     * @param string            $outputDirectory
     * @throws \RuntimeException
     * @return void
     */
    public function writeEntityClass(ClassMetadataInfo $metadata, $outputDirectory)
    {
        //Shopware fix. Here we check the shopware model mappings to set the correct namespace for the passed meta data.
        if (array_key_exists($metadata->name, $this->tableMappings)) {
            $name = $metadata->name;
            $metadata->name = $this->tableMappings[$name]['namespace'] . '\\' . $this->tableMappings[$name]['name'];
            $metadata->namespace = $this->tableMappings[$name]['namespace'];

            //all shopware attributes tables has an @OneToOne association.
            if (strpos($metadata->getTableName(), '_attributes') !== false) {
                foreach ($metadata->associationMappings as &$mapping) {
                    $mapping['type'] = 1;
                    $mapping['inversedBy'] = 'attribute';
                }
            }
        }

        $outputDirectory = rtrim($outputDirectory, '/');
        $path = $outputDirectory . '/' . str_replace('\\', DIRECTORY_SEPARATOR, $metadata->name) . $this->_extension;
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $this->_isNew = !file_exists($path) || (file_exists($path) && $this->_regenerateEntityIfExists);

        if (!$this->_isNew) {
            $this->_parseTokensInEntityFile(file_get_contents($path));
        } else {
            $this->_staticReflection[$metadata->name] = array('properties' => array(), 'methods' => array());
        }

        if ($this->_backupExisting && file_exists($path)) {
            $backupPath = dirname($path) . DIRECTORY_SEPARATOR . basename($path) . "~";
            if (!copy($path, $backupPath)) {
                throw new \RuntimeException("Attempt to backup overwritten entity file but copy operation failed.");
            }
        }
        // If entity doesn't exist or we're re-generating the entities entirely
        if ($this->_isNew) {
            $code = $this->generateEntityClass($metadata);
            file_put_contents($path, $code);
            // If entity exists and we're allowed to update the entity class
        } else {
            if (!$this->_isNew && $this->_updateEntityIfExists) {
                $code = $this->generateUpdatedEntityClass($metadata, $path);
                file_put_contents($path, $code);
            }
        }
    }

    protected function _generateEntityAssociationMappingProperties(ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->associationMappings as $key => $associationMapping) {
            if ($this->_hasProperty($associationMapping['fieldName'], $metadata)) {
                continue;
            }
            //check if the target entity is a mapped shopware model
            if (array_key_exists($associationMapping['targetEntity'], $this->tableMappings)) {

                //draw mapping for the shopware model
                $mapping = $this->tableMappings[$associationMapping['targetEntity']];

                //explode namespace to generate an unique property name
                $namespaces = explode('\\', $mapping['namespace']);

                //if the last namespace fragment equals the mapping name then use only the mapping name
                //otherwise concat the mapping name and the last namespace fragment.
                //example (Namespace equals Name):
                //      Namespace           => Shopware\Models\Article
                //      Target Entity Name  => Article
                //      Result              => $article
                //
                //example (Namespace not equals Name):
                //      Namespace           => Shopware\Models\Article
                //      Target Entity Name  => Detail
                //      Result              => $articleDetail
                if ($namespaces[count($namespaces) - 1] !== $mapping['name']) {
                    $fieldName = lcfirst($namespaces[count($namespaces) - 1]) . $mapping['name'];
                } else {
                    $fieldName = lcfirst($mapping['name']);
                }

                //save original name
                $associationMapping['original'] = array(
                    'targetEntity' => $associationMapping['targetEntity'],
                    'fieldName' => $associationMapping['fieldName'],
                );

                //set new entity and field name based on the shopware mapping
                $associationMapping['targetEntity'] = $mapping['namespace'] . '\\' . $mapping['name'];
                $associationMapping['fieldName'] = $fieldName;

                //set new field mapping to generate an id property for the association
                $associationMapping['fieldMapping'] = array(
                    'fieldName' => $fieldName . 'Id',
                    'columnName' => $associationMapping['joinColumns'][0]['name'],
                    'type' => 'integer',
                    'nullable' => true,
                );

                //use internal helper function to get the column default
                $default = $this->getColumnDefault($associationMapping['fieldMapping']);
                //generate the property doc block for the id property.
                $lines[] = $this->_generateFieldMappingPropertyDocBlock($associationMapping['fieldMapping'], $metadata);
                $lines[] = $this->_spaces . 'private $' . $associationMapping['fieldMapping']['fieldName']
                    . $default . ";\n";

                $metadata->associationMappings[$key] = $associationMapping;
            }

            //generates the association property
            $lines[] = $this->_generateAssociationMappingPropertyDocBlock($associationMapping, $metadata);
            $lines[] = $this->_spaces . 'private $' . $associationMapping['fieldName']
                . ($associationMapping['type'] == 'manyToMany' ? ' = array()' : null) . ";\n";
        }

        return implode("\n", $lines);
    }

    protected function _generateEntityFieldMappingProperties(ClassMetadataInfo $metadata)
    {
        $lines = array();

        foreach ($metadata->fieldMappings as $fieldMapping) {
            if ($this->_hasProperty($fieldMapping['fieldName'], $metadata) ||
                $metadata->isInheritedField($fieldMapping['fieldName'])
            ) {
                continue;
            }

            $default = $this->getColumnDefault($fieldMapping);
            $lines[] = $this->_generateFieldMappingPropertyDocBlock($fieldMapping, $metadata);
            $lines[] = $this->_spaces . 'private $' . $fieldMapping['fieldName']
                . $default . ";\n";
        }

        return implode("\n", $lines);
    }

    /**
     * Internal helper function to get the column default declaration.
     * @param $fieldMapping
     * @return null|string
     */
    protected function getColumnDefault($fieldMapping)
    {
        if (isset($fieldMapping['default'])) {
            return ' = ' . var_export($fieldMapping['default'], true);
        } else {
            if ($fieldMapping['nullable'] == 1) {
                return ' = null';
            } else {
                return null;
            }
        }
    }

}
