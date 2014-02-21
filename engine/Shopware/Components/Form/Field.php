<?php

namespace Shopware\Components\Form;

use Shopware\Components\Form\Interfaces\Field as FieldInterface;

class Field extends Base implements FieldInterface
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


}