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

namespace Shopware\Models\Attribute;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="s_attribute_configuration")
 * @ORM\Entity
 */
class Configuration extends ModelEntity
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $tableName
     * @ORM\Column(name="table_name", type="string", nullable=false)
     */
    private $tableName;

    /**
     * @var string $name
     * @ORM\Column(name="column_name", type="string", nullable=false)
     */
    private $columnName;

    /**
     * @var string $type
     * @ORM\Column(name="column_type", type="string", nullable=false)
     */
    private $columnType;

    /**
     * @var string
     * @ORM\Column(name="entity", type="string", nullable=false)
     */
    private $entity;

    /**
     * @var string $label
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label;

    /**
     * @var string $help
     * @ORM\Column(name="help_text", type="string", nullable=true)
     */
    private $helpText;

    /**
     * @var string $help
     * @ORM\Column(name="support_text", type="string", nullable=true)
     */
    private $supportText;

    /**
     * @var boolean $translatable
     * @ORM\Column(name="translatable", type="boolean")
     */
    private $translatable = false;

    /**
     * @var bool
     * @ORM\Column(name="display_in_backend", type="boolean")
     */
    private $displayInBackend = true;

    /**
     * @var bool
     * @ORM\Column(name="custom", type="boolean")
     */
    private $custom = false;

    /**
     * @var null
     * @ORM\Column(name="plugin_id", type="integer", nullable=true)
     */
    private $pluginId = null;

    /**
     * @var string $position
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var string
     * @ORM\Column(name="array_store", type="text", nullable=true)
     */
    private $arrayStore = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getHelpText()
    {
        return $this->helpText;
    }

    /**
     * @param string $helpText
     */
    public function setHelpText($helpText)
    {
        $this->helpText = $helpText;
    }

    /**
     * @return string
     */
    public function getSupportText()
    {
        return $this->supportText;
    }

    /**
     * @param string $supportText
     */
    public function setSupportText($supportText)
    {
        $this->supportText = $supportText;
    }

    /**
     * @return boolean
     */
    public function isTranslatable()
    {
        return $this->translatable;
    }

    /**
     * @param boolean $translatable
     */
    public function setTranslatable($translatable)
    {
        $this->translatable = $translatable;
    }

    /**
     * @return boolean
     */
    public function isDisplayInBackend()
    {
        return $this->displayInBackend;
    }

    /**
     * @param boolean $displayInBackend
     */
    public function setDisplayInBackend($displayInBackend)
    {
        $this->displayInBackend = $displayInBackend;
    }

    /**
     * @return null
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @param null $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return boolean
     */
    public function isCustom()
    {
        return $this->custom;
    }

    /**
     * @param boolean $custom
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
}
