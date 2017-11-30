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

namespace Shopware\Bundle\SearchBundleDBAL\ConditionHandler;

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\PriceHelper;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class VariantConditionHandler implements ConditionHandlerInterface
{
    /** @var PriceHelper */
    private $helper;

    public function __construct(PriceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return $condition instanceof VariantCondition;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $tableKey = $condition->getName();

        $suffix = md5(json_encode($condition));

        if ($query->hasState('option_' . $tableKey)) {
            return;
        }
        $query->addState('option_' . $tableKey);

        $where = [];
        /** @var VariantCondition $condition */
        foreach ($condition->getOptionIds() as $valueId) {
            $valueKey = ':' . $tableKey . '_' . $valueId . '_' . $suffix;
            $where[] = $tableKey . '.option_id = ' . $valueKey;
            $query->setParameter($valueKey, $valueId);
        }

        $where = implode(' OR ', $where);

        //        $this->helper->joinAvailableVariant($query);

        $query->innerJoin(
            'variant',
            's_article_configurator_option_relations',
            $tableKey,
            'variant.id = ' . $tableKey . '.article_id
             AND (' . $where . ')'
        );

        if (!$query->hasState('variant_group_by')) {
            $query->resetQueryPart('groupBy');
        }

        $query->addState('variant_group_by');
        $query->addGroupBy($tableKey . '.option_id');
    }
}
