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

class ConfigurationStruct implements \JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var string|null
     */
    private $helpText;

    /**
     * @var string|null
     */
    private $supportText;

    /**
     * @var int|null
     */
    private $position = null;

    /**
     * @var bool
     */
    private $displayInBackend = false;

    /**
     * @var bool
     */
    private $custom = false;

    /**
     * @var bool
     */
    private $configured = false;

    /**
     * @var bool
     */
    private $translatable = false;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $columnName;

    /**
     * @var string|int|float|null
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $identifier;

    /**
     * @var bool
     */
    private $core;

    /**
     * @var string
     */
    private $columnType;

    /**
     * @var array
     */
    private $elasticSearchType;

    /**
     * @var string
     */
    private $dbalType;

    /**
     * @var string
     */
    private $sqlType;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $arrayStore;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string|null $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string|null
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * @param string|null $helpText
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;
    }

    /**
     * @return string|null
     */
    public function getSupportText()
    {
        return $this->supportText;
    }

    /**
     * @param string|null $supportText
     */
    public function setSupportText($supportText)
    {
        $this->supportText = $supportText;
    }

    /**
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function displayInBackend()
    {
        return $this->displayInBackend;
    }

    /**
     * @param bool $displayInBackend
     */
    public function setDisplayInBackend($displayInBackend)
    {
        $this->displayInBackend = $displayInBackend;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return $this->configured;
    }

    /**
     * @param bool $configured
     */
    public function setConfigured($configured)
    {
        $this->configured = $configured;
    }

    /**
     * @return bool
     */
    public function isTranslatable()
    {
        return $this->translatable;
    }

    /**
     * @param bool $translatable
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * @param string $columnName
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
    }

    /**
     * @return bool
     */
    public function isIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param bool $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return bool
     */
    public function isCore()
    {
        return $this->core;
    }

    /**
     * @param bool $core
     */
    public function setCore($core)
    {
        $this->core = $core;
    }

    /**
     * @return string
     */
    public function getColumnType()
    {
        return $this->columnType;
    }

    /**
     * @param string $columnType
     */
    public function setColumnType($columnType)
    {
        $this->columnType = $columnType;
    }

    /**
     * @return string
     */
    public function getDbalType()
    {
        return $this->dbalType;
    }

    /**
     * @param string $dbalType
     */
    public function setDbalType($dbalType)
    {
        $this->dbalType = $dbalType;
    }

    /**
     * @return string
     */
    public function getSqlType()
    {
        return $this->sqlType;
    }

    /**
     * @param string $sqlType
     */
    public function setSqlType($sqlType)
    {
        $this->sqlType = $sqlType;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->custom;
    }

    /**
     * @param bool $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getArrayStore()
    {
        return $this->arrayStore;
    }

    /**
     * @param string $arrayStore
     */
    public function setArrayStore($arrayStore)
    {
        $this->arrayStore = $arrayStore;
    }

    /**
     * @return array
     */
    public function getElasticSearchType()
    {
        return $this->elasticSearchType;
    }

    /**
     * @param array $elasticSearchType
     */
    public function setElasticSearchType($elasticSearchType)
    {
        $this->elasticSearchType = $elasticSearchType;
    }

    /**
     * @return string|int|float|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string|int|float|null $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }
}
