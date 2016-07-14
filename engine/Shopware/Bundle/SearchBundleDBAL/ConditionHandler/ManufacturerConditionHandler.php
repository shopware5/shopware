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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\ManufacturerCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\ConditionHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ManufacturerConditionHandler implements ConditionHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof ManufacturerCondition);
    }

    /**
     * {@inheritdoc}
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        $query->innerJoin(
            'product',
            's_articles_supplier',
            'manufacturer',
            'manufacturer.id = product.supplierID
             AND product.supplierID IN (:manufacturer)'
        );

        /** @var ManufacturerCondition $condition */
        $query->setParameter(
            ':manufacturer',
            $condition->getManufacturerIds(),
            Connection::PARAM_INT_ARRAY
        );
    }
}
