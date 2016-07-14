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

namespace Shopware\Components\Form;

use Shopware\Components\Form\Interfaces\Field as FieldInterface;
use Shopware\Components\Form\Interfaces\Validate;

/**
 * Class Field
 * @package Shopware\Components\Form
 */
class Field extends Base implements FieldInterface, Validate
{
    /**
     * @optional
     * @var string $label
     */
    protected $label = '';

    /**
     * @required
     * @var string $name
     */
    protected $name;

    /**
     * @optional
     * @var mixed
     */
    protected $defaultValue;

    /**
     * Contains additional data for each
     * config field.
     *
     * @optional
     * @var array
     */
    protected $attributes = array();

    /**
     * Help text for the user to explain
     * which effects has this field configuration.
     *
     * @var string
     */
    protected $help;

    /**
     * Defines if the field is
     * required to configured.
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @param boolean $required
     */
    public function setRequired($required)
    {
        $this->required = $required;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Validates the form element
     * and throws an exception if
     * some requirements are not set.
     *
     * @throws \Exception
     */
    public function validate()
    {
        if (!$this->name) {
            throw new \Exception(sprintf(
                "Field %s requires a configured name",
                get_class($this)
            ));
        }
    }
}
