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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\BooleanFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RadioFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListItem;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductAttributeFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var CrudService
     */
    private $crudService;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory,
        CrudService $crudService
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->crudService = $crudService;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof ProductAttributeFacet;
    }

    /**
     * @param FacetInterface|ProductAttributeFacet $facet
     *
     * @return FacetResultInterface|null
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $sqlField = 'productAttribute.' . $facet->getField();
        $query->andWhere($sqlField . ' IS NOT NULL')
            ->andWhere($sqlField . " NOT IN ('', '0', '0000-00-00')");

        /** @var ConfigurationStruct|null $attribute */
        $attribute = $this->crudService->get('s_articles_attributes', $facet->getField());

        $type = $attribute ? $attribute->getColumnType() : null;

        /** @var ProductAttributeFacet $facet */
        switch ($facet->getMode()) {
            case ProductAttributeFacet::MODE_VALUE_LIST_RESULT:
            case ProductAttributeFacet::MODE_RADIO_LIST_RESULT:
                $result = $this->createValueListFacetResult($query, $facet, $criteria, $context);
                break;

            case ProductAttributeFacet::MODE_BOOLEAN_RESULT:
                $result = $this->createBooleanFacetResult($query, $facet, $criteria);
                break;

            case ProductAttributeFacet::MODE_RANGE_RESULT:
                $result = $this->createRangeFacetResult($query, $facet, $criteria);
                break;

            default:
                $result = null;
                break;
        }

        if ($result === null) {
            return $result;
        }

        if ($facet->getTemplate()) {
            $result->setTemplate($facet->getTemplate());

            return $result;
        }

        $result->setTemplate(
            $this->getTypeTemplate($type, $facet->getMode(), $result->getTemplate())
        );

        return $result;
    }

    /**
     * @return RadioFacetResult|ValueListFacetResult|null
     */
    private function createValueListFacetResult(
        QueryBuilder $query,
        ProductAttributeFacet $facet,
        Criteria $criteria,
        Struct\ShopContextInterface $context
    ) {
        $sqlField = 'productAttribute.' . $facet->getField();

        $query->addSelect($sqlField)
            ->orderBy($sqlField)
            ->groupBy($sqlField);

        $this->addTranslations($query, $context);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();
        $result = $statement->fetchAll();
        if (empty($result)) {
            return null;
        }

        $actives = [];
        /** @var ProductAttributeCondition $condition */
        if ($condition = $criteria->getCondition($facet->getName())) {
            $actives = $condition->getValue();
        }

        if (!is_array($actives)) {
            $actives = [$actives];
        }

        $items = array_map(function ($row) use ($actives, $facet) {
            $viewName = $row[$facet->getField()];
            $translation = $this->extractTranslations($row, '__attribute_' . $facet->getField());
            if ($translation !== null) {
                $viewName = $translation;
            }

            return new ValueListItem($row[$facet->getField()], $viewName, in_array($row[$facet->getField()], $actives));
        }, $result);

        if ($facet->getMode() == ProductAttributeFacet::MODE_RADIO_LIST_RESULT) {
            return new RadioFacetResult(
                $facet->getName(),
                $criteria->hasCondition($facet->getName()),
                $facet->getLabel(),
                $items,
                $facet->getFormFieldName()
            );
        }

        return new ValueListFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $facet->getLabel(),
            $items,
            $facet->getFormFieldName()
        );
    }

    /**
     * @return RangeFacetResult|null
     */
    private function createRangeFacetResult(
        QueryBuilder $query,
        ProductAttributeFacet $facet,
        Criteria $criteria
    ) {
        $sqlField = 'productAttribute.' . $facet->getField();

        $query->select([
            'MIN(' . $sqlField . ') as minValues',
            'MAX(' . $sqlField . ') as maxValues',
        ]);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (empty($result)) {
            return null;
        }

        if ($result['minValues'] === null && $result['maxValues'] === null) {
            return null;
        }
        if ($result['minValues'] === $result['maxValues']) {
            return null;
        }

        $activeMin = $result['minValues'];
        $activeMax = $result['maxValues'];

        /** @var ProductAttributeCondition $condition */
        if ($condition = $criteria->getCondition($facet->getName())) {
            $data = $condition->getValue();
            $activeMin = $data['min'];
            $activeMax = $data['max'];
        }

        return new RangeFacetResult(
            $facet->getName(),
            $criteria->hasCondition($facet->getName()),
            $facet->getLabel(),
            $result['minValues'],
            $result['maxValues'],
            $activeMin,
            $activeMax,
            'min' . $facet->getFormFieldName(),
            'max' . $facet->getFormFieldName(),
            [],
            $facet->getSuffix(),
            $facet->getDigits()
        );
    }

    /**
     * @return BooleanFacetResult|null
     */
    private function createBooleanFacetResult(
        QueryBuilder $query,
        ProductAttributeFacet $facet,
        Criteria $criteria
    ) {
        $sqlField = 'productAttribute.' . $facet->getField();

        $query->select('COUNT(' . $sqlField . ')');

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();
        $result = $statement->fetch(\PDO::FETCH_COLUMN);

        if (empty($result)) {
            return null;
        }

        return new BooleanFacetResult(
            $facet->getName(),
            $facet->getFormFieldName(),
            $criteria->hasCondition($facet->getName()),
            $facet->getLabel()
        );
    }

    /**
     * @param QueryBuilder                $query
     * @param Struct\ShopContextInterface $context
     */
    private function addTranslations($query, $context)
    {
        if ($context->getShop()->isDefault()) {
            return;
        }

        $query
            ->addSelect('attributeTranslations.objectdata as __attribute_translation')
            ->leftJoin(
                'product',
                's_core_translations',
                'attributeTranslations',
                'attributeTranslations.objectkey = product.id AND attributeTranslations.objecttype = "article" AND attributeTranslations.objectlanguage = :language'
            )
        ;
        $query->setParameter(':language', $context->getShop()->getId());

        if (!$context->getShop()->getFallbackId() || $context->getShop()->getFallbackId() === $context->getShop()->getId()) {
            return;
        }

        $query
            ->addSelect('attributeTranslations_fallback.objectdata as __attribute_translation_fallback')
            ->leftJoin(
                'product',
                's_core_translations',
                'attributeTranslations_fallback',
                'attributeTranslations_fallback.objectkey = product.id AND attributeTranslations_fallback.objecttype = "article" AND attributeTranslations_fallback.objectlanguage = :languageFallback'
            )
        ;
        $query->setParameter(':languageFallback', $context->getShop()->getFallbackId());
    }

    /**
     * @param array  $row
     * @param string $fieldName
     *
     * @return string|null
     */
    private function extractTranslations($row, $fieldName)
    {
        $translation = $this->unserializeTranslation($row, '__attribute_translation', $fieldName);
        if ($translation !== null) {
            return $translation;
        }

        $translation = $this->unserializeTranslation($row, '__attribute_translation_fallback', $fieldName);
        if ($translation !== null) {
            return $translation;
        }

        return null;
    }

    /**
     * @param array  $row
     * @param string $key
     * @param string $fieldName
     *
     * @return string|null
     */
    private function unserializeTranslation($row, $key, $fieldName)
    {
        if (!isset($row[$key])) {
            return null;
        }

        $result = @unserialize($row[$key], ['allowed_classes' => false]);
        if (!$result) {
            return null;
        }

        if (!isset($result[$fieldName]) || empty($result[$fieldName])) {
            return null;
        }

        return $result[$fieldName];
    }

    /**
     * @param string $type
     * @param string $mode
     * @param string $defaultTemplate
     *
     * @return string
     */
    private function getTypeTemplate($type, $mode, $defaultTemplate)
    {
        switch (true) {
            case $type === TypeMapping::TYPE_DATE && $mode === ProductAttributeFacet::MODE_RANGE_RESULT:

                return 'frontend/listing/filter/facet-date-range.tpl';
            case $type === TypeMapping::TYPE_DATE && $mode === ProductAttributeFacet::MODE_VALUE_LIST_RESULT:

                return 'frontend/listing/filter/facet-date-multi.tpl';
            case $type === TypeMapping::TYPE_DATE && $mode !== ProductAttributeFacet::MODE_BOOLEAN_RESULT:

                return 'frontend/listing/filter/facet-date.tpl';
            case $type === TypeMapping::TYPE_DATETIME && $mode === ProductAttributeFacet::MODE_RANGE_RESULT:

                return 'frontend/listing/filter/facet-datetime-range.tpl';
            case $type === TypeMapping::TYPE_DATETIME && $mode === ProductAttributeFacet::MODE_VALUE_LIST_RESULT:

                return 'frontend/listing/filter/facet-datetime-multi.tpl';
            case $type === TypeMapping::TYPE_DATETIME && $mode !== ProductAttributeFacet::MODE_BOOLEAN_RESULT:

                return 'frontend/listing/filter/facet-datetime.tpl';
            default:
                return $defaultTemplate;
        }
    }
}
