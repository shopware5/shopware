<?php

namespace Shopware\Gateway;
use Shopware\Struct as Struct;

interface Translation
{
    /**
     * Translates the passed product with the stored translations.
     *
     * This function translates only the product data, which
     * stored in the s_core_translations table under the object key
     * `article`
     *
     * The loaded translations has to be injected in the passed Struct\ProductMini object.
     *
     * @param Struct\ProductMini $product
     * @param Struct\Shop $shop
     */
    public function translateProduct(
        Struct\ProductMini $product,
        Struct\Shop $shop
    );

    /**
     * Translates the passed unit with the stored translations.
     *
     * This function translates only the unit data, which
     * stored in the s_core_translations table under the object key
     * `config_units`
     *
     * The loaded translations has to be injected in the passed Struct\Unit object.
     *
     * @param Struct\Unit $unit
     * @param Struct\Shop $shop
     */
    public function translateUnit(
        Struct\Unit $unit,
        Struct\Shop $shop
    );

    /**
     * Translates the passed manufacturer with the stored translations.
     *
     * This function translates only the manufacturer data, which
     * stored in the s_core_translations table under the object key
     * `supplier`
     *
     * The loaded translations has to be injected in the passed Struct\Manufacturer object.
     *
     * @param Struct\Manufacturer $manufacturer
     * @param Struct\Shop $shop
     */
    public function translateManufacturer(
        Struct\Manufacturer $manufacturer,
        Struct\Shop $shop
    );

    /**
     * Translates the passed property set with the stored translations.
     *
     * The property set contains the following data sources:
     *  - Property set (s_filter)
     *  - Property groups (s_filter_options)
     *  - Property options (s_filter_values)
     *
     * This function has to translate all stored property data.
     * Each translation is stored in the s_core_translations table.
     *
     * The property set data can be identified over the object key
     * `propertygroup`.
     *
     * The property group data can be identified over the object key
     * `propertyoption`.
     *
     * And the property option data can be identified over the object key
     * `propertyvalue`
     *
     * All translations has to be injected into the associated Struct objects.
     *
     * @param Struct\PropertySet $set
     * @param Struct\Shop $shop
     */
    public function translatePropertySet(
        Struct\PropertySet $set,
        Struct\Shop $shop
    );
}