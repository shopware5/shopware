<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Struct;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;

class Translation extends Gateway
{
    private $translationHydrator;

    function __construct(
        ModelManager $entityManager,
        Hydrator\Translation $translationHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->translationHydrator = $translationHydrator;
    }


    /**
     * Translates the passed product with the stored translations.
     *
     * This function translates only the product data, which
     * stored in the s_core_translations table under the object key
     * `article`
     *
     * The loaded translations has to be injected in the passed Struct\ListProduct object.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Shop $shop
     */
    public function translateProduct(
        Struct\ListProduct $product,
        Struct\Shop $shop
    )
    {
        $data = $this->getSingleTranslation(
            'article',
            $product->getId(),
            $shop->getId()
        );

        $this->translationHydrator->hydrateProductTranslation(
            $product,
            $data
        );
    }

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
    )
    {
        $data = $this->getSingleTranslation(
            'config_units',
            $unit->getId(),
            $shop->getId()
        );

        $data = array_values($data);
        $data = $data[0];

        $this->translationHydrator->hydrateUnitTranslation(
            $unit,
            $data
        );
    }

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
    )
    {
        $data = $this->getSingleTranslation(
            'supplier',
            $manufacturer->getId(),
            $shop->getId()
        );

        $this->translationHydrator->hydrateManufacturerTranslation(
            $manufacturer,
            $data
        );
    }

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
    ) {
        $translation = $this->getSingleTranslation(
            'propertygroup',
            $set->getId(),
            $shop->getId()
        );

        $translation['groups'] = $this->getPropertyGroupTranslations($set, $shop);
        $translation['options'] = $this->getPropertyOptionTranslations($set, $shop);

        $this->translationHydrator->hydratePropertyTranslation($set, $translation);
    }


    private function getPropertyGroupTranslations(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $query = $this->getTranslationQuery('propertyoption', $shop->getId())
            ->select(array('translation.objectkey', 'translation.objectdata'))
            ->innerJoin('translation', 's_filter_options', 'options', 'options.id = translation.objectkey')
            ->innerJoin(
                'options',
                's_filter_relations',
                'relations',
                'relations.optionID = options.id AND relations.groupID = :setId'
            )
            ->setParameter(':setId', $set->getId());

        $data = $this->fetchAssociatedArray($query);
        $data = array_combine(
            array_keys($data),
            array_column($data, 'objectdata')
        );

        if (empty($data)) {
            return array();
        }

        return $data;
    }

    private function getPropertyOptionTranslations(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $query = $this->getTranslationQuery('propertyvalue', $shop->getId())
            ->select(array('translation.objectkey', 'translation.objectdata'))
            ->innerJoin('translation', 's_filter_values', 'value', 'value.id = translation.objectkey')
            ->innerJoin('value', 's_filter_options', 'options', 'options.id = value.optionID')
            ->innerJoin(
                'options',
                's_filter_relations',
                'relations',
                'relations.optionID = options.id AND relations.groupID = :setId'
            )
            ->setParameter(':setId', $set->getId());

        $data = $this->fetchAssociatedArray($query);
        $data = array_combine(
            array_keys($data),
            array_column($data, 'objectdata')
        );

        if (empty($data)) {
            return array();
        }

        return $data;
    }

    /**
     * Helper function which returns a single translation.
     *
     * @param $type
     * @param $key
     * @param $shopId
     * @return mixed
     */
    private function getSingleTranslation($type, $key, $shopId)
    {
        $query = $this->getTranslationQuery($type, $shopId)
            ->andWhere('translation.objectkey = :key')
            ->setParameter(':key', $key)
            ->setFirstResult(0)
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!empty($data)) {
            return unserialize($data['objectdata']);
        } else {
            return array();
        }
    }

    /**
     * Helper function which builds a base query to select resource translations.
     * @param $type
     * @param $shopId
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getTranslationQuery($type, $shopId)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select('*')
            ->from('s_core_translations', 'translation')
            ->andWhere('translation.objectlanguage = :localeId')
            ->andWhere('translation.objecttype = :type')
            ->setParameter(':type', $type)
            ->setParameter(':localeId', $shopId);

        return $query;
    }
}