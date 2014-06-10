<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Property\Set;

class Property implements Facet
{
    /**
     * Flag if the facet is filtered with a condition
     * @var bool
     */
    private $filtered = false;

    /**
     * @var Set[]
     */
    private $properties;

    /**
     * @return string
     */
    public function getName()
    {
        return 'manufacturer';
    }

    /**
     * @param bool $filtered
     */
    public function setFiltered($filtered)
    {
        $this->filtered = $filtered;
    }

    /**
     * @return bool
     */
    public function isFiltered()
    {
        return $this->filtered;
    }

    /**
     * @return \Shopware\Struct\Property\Set[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param \Shopware\Struct\Property\Set[] $properties
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
    }
}