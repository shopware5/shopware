<?php

namespace Shopware\Gateway\Search\Facet;

use Shopware\Gateway\Search\Facet;

class Manufacturer implements Facet
{
    /**
     * Flag if the facet is filtered with a condition
     * @var bool
     */
    private $filtered = false;

    /**
     * @var \Shopware\Struct\Product\Manufacturer[]
     */
    private $manufacturers;

    /**
     * @return string
     */
    public function getName()
    {
        return 'manufacturer';
    }

    /**
     * @return \Shopware\Struct\Product\Manufacturer[]
     */
    public function getManufacturers()
    {
        return $this->manufacturers;
    }

    /**
     * @param \Shopware\Struct\Product\Manufacturer[] $manufacturers
     */
    public function setManufacturers(array $manufacturers)
    {
        $this->manufacturers = $manufacturers;
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
}