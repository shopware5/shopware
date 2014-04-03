<?php

namespace Shopware\Gateway\ORM;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Hydrator\ORM as Hydrator;

class Translation
{

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var Hydrator\Translation
     */
    private $translationHydrator;

    /**
     * @param ModelManager $entityManager
     * @param \Shopware\Hydrator\ORM\Translation $translationHydrator
     */
    function __construct(ModelManager $entityManager, Hydrator\Translation $translationHydrator)
    {
        $this->entityManager = $entityManager;
        $this->translationHydrator = $translationHydrator;
    }

    /**
     * @param Struct\ProductMini $product
     * @param Struct\Shop $shop
     */
    public function translateProduct(Struct\ProductMini $product, Struct\Shop $shop)
    {
        $builder = $this->getSingleTranslationQuery(
            'article',
            $product->getId(),
            $shop->getId()
        );

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        $this->translationHydrator->hydrateProductTranslation(
            $product,
            $data
        );
    }

    /**
     * @param Struct\Unit $unit
     * @param Struct\Shop $shop
     */
    public function translateUnit(Struct\Unit $unit, Struct\Shop $shop)
    {
        $builder = $this->getSingleTranslationQuery(
            'config_units',
            $unit->getId(),
            $shop->getId()
        );

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        $this->translationHydrator->hydrateUnitTranslation(
            $unit,
            $data
        );
    }

    /**
     * Translates the passed manufacturer struct for the passed shop.
     * The manufacturer data stored in the shopware translation table
     * with the association key 'supplier'
     *
     * @param Struct\Manufacturer $manufacturer
     * @param Struct\Shop $shop
     */
    public function translateManufacturer(Struct\Manufacturer $manufacturer, Struct\Shop $shop)
    {
        $builder = $this->getSingleTranslationQuery(
            'supplier',
            $manufacturer->getId(),
            $shop->getId()
        );

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        if ($data === null) {
            return;
        }

        $this->translationHydrator->hydrateManufacturerTranslation(
            $manufacturer,
            $data
        );
    }

    public function translatePropertySet(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $translation = $this->getPropertySetTranslation($set, $shop);

        $translation['groups'] = $this->getPropertyGroupTranslations($set, $shop);

        $translation['options'] = $this->getPropertyOptionTranslations($set, $shop);

        $this->translationHydrator->hydratePropertyTranslation($set, $translation);
    }


    private function getPropertyGroupTranslations(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $connection = $this->entityManager->getConnection();

        $statement = $connection->executeQuery("
            SELECT translations.objectkey, translations.objectdata
            FROM s_core_translations translations

            INNER JOIN s_filter_options options
              ON options.id = translations.objectkey

            INNER JOIN s_filter_relations relations
              ON relations.optionID = options.id
              AND relations.groupID = :setId

            WHERE objecttype = 'propertyoption'
            AND objectlanguage = :shopId

        ", array(':setId' => $set->getId(), ':shopId' => $shop->getId()));

        $translation = $this->fetchAssociatedArray($statement);
        $translation = array_combine(
            array_keys($translation),
            array_column($translation, 'objectdata')
        );

        if (empty($translation)) {
            return array();
        }

        return $translation;
    }

    /**
     * Helper function which uses the first column as array key.
     *
     * @param \Doctrine\DBAL\Driver\Statement $statement
     * @return array
     */
    private function fetchAssociatedArray(\Doctrine\DBAL\Driver\Statement $statement)
    {
        $statement->execute();
        $data = $statement->fetchAll(\PDO::FETCH_GROUP);
        $data = array_combine(
            array_keys($data),
            array_column($data, 0)
        );
        return $data;
    }

    private function getPropertyOptionTranslations(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $connection = $this->entityManager->getConnection();

        $statement = $connection->executeQuery("
            SELECT translations.objectkey, translations.objectdata
            FROM s_core_translations translations

            INNER JOIN s_filter_values filterValues
              ON filterValues.id = translations.objectkey

            INNER JOIN s_filter_options options
              ON filterValues.optionID = options.id

            INNER JOIN s_filter_relations relations
              ON relations.optionID = options.id
              AND relations.groupID = :setId

            WHERE objecttype = 'propertyvalue'
            AND objectlanguage = :shopId
        ", array(':setId' => $set->getId(), ':shopId' => $shop->getId()));

        $translation = $this->fetchAssociatedArray($statement);
        $translation = array_combine(
            array_keys($translation),
            array_column($translation, 'objectdata')
        );

        if (empty($translation)) {
            return array();
        }

        return $translation;
    }

    private function getPropertySetTranslation(Struct\PropertySet $set, Struct\Shop $shop)
    {
        $connection = $this->entityManager->getConnection();

        $statement = $connection->executeQuery("
            SELECT translations.*
            FROM s_core_translations translations
            WHERE objecttype = 'propertygroup'
            AND translations.objectkey = :setId
            AND objectlanguage = :shopId
        ", array(':setId' => $set->getId(), ':shopId' => $shop->getId()));

        $statement->execute();
        $translation = $statement->fetch();

        if (empty($translation)) {
            return array();
        }

        return $translation;
    }

    /**
     * @param $type
     * @param $key
     * @param $shopId
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getSingleTranslationQuery($type, $key, $shopId)
    {
        $builder = $this->getTranslationQuery()
            ->where('translation.key = :productId')
            ->andWhere('translation.localeId = :localeId')
            ->andWhere('translation.type = :type')
            ->setParameter('type', $type)
            ->setParameter('productId', $key)
            ->setParameter('localeId', $shopId)
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $builder;
    }

    /**
     * Returns the base query for a translation.
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function getTranslationQuery()
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select(array('translation'))
            ->from('Shopware\Models\Translation\Translation', 'translation')
            ->innerJoin('translation.locale', 'locale');

        return $builder;
    }
}
