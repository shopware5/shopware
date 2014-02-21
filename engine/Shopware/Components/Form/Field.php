<?php

namespace Shopware\Components\Form;

use Shopware\Components\Form\Interfaces\Field as FieldInterface;
use Shopware\Components\Form\Interfaces\Validate;

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
    public function getRequired()
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