<?php

namespace Shopware\Components\Form;

use Doctrine\Common\Collections\ArrayCollection;

class Base
{
    /**
     * Outputs the object data as array.
     * Used to convert a form structure to
     * a uniform array structure which can be used
     * from the Shopware\Components\Form\Persister and
     * Shopware\Components\Form\Hydrator
     *
     * @return array
     */
    public function toArray()
    {
        $properties = get_class_vars(get_class($this));

        $data = array(
            'type' => get_class($this)
        );

        foreach ($properties as $property => $value) {
            $method = 'get' . ucfirst($property);

            if (!method_exists($this, $method)) {
                continue;
            }

            $value = $this->$method();
            
            if ($value instanceof \Traversable) {
                $converted = array();
                foreach ($value as $item) {
                    if ($item instanceof Base) {
                        $converted[] = $item->toArray();
                    } else {
                        $converted[] = $item;
                    }
                }
                $value = $converted;
            }
            $data[$property] = $value;
        }

        return $data;
    }

    /**
     * Helper function to set the object
     * data by array data.
     *
     * @param array $array
     * @return $this
     */
    public function fromArray(array $array)
    {
        foreach ($array as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }
}