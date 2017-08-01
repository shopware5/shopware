<?php declare(strict_types=1);

namespace Shopware\Product\Writer;

class ProductFieldConfiguration
{
    public function getColumns()
    {
        return [
            new IntField('id'),
            new IntField('supplierID'),
            new StringField('name'),
            new StringField('description'),
            new StringField('description_long'),
            new DateField('shippingtime'),
            new StringField('datum'),
            new StringField('active'),
            new IntField('pseudosales'),
            new IntField('topseller'),
            new IntField('metaTitle'),
            new IntField('keywords'),
            new IntField('changetime'),
            new IntField('pricegroupActive'),
            new IntField('laststock'),
            new IntField('crossbundlelook'),
            new IntField('notification'),
            new StringField('template'),
            new DateField('available_from'),
            new DateField('available_to'),
            new IntField('mode'),
            new DateField('main_detail_id'),
            new IntField('configurator_set_id'),
            new IntField('taxID'),
            new IntField('filtergroupID'),
            new IntField('pricegroupID'),
        ];
    }
}

abstract class Field
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}

class DateField extends Field
{

}

class IntField extends Field
{

}

class StringField extends Field
{

}