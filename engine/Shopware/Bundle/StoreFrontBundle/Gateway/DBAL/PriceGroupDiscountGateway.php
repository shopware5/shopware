<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @package Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 */
class PriceGroupDiscountGateway implements Gateway\PriceGroupDiscountGatewayInterface
{
    /**
     * @var Hydrator\PriceHydrator
     */
    private $priceHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\PriceHydrator $priceHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\PriceHydrator $priceHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function getProductDiscount(
        Struct\ListProduct $product,
        Struct\Customer\Group $customerGroup,
        Struct\Context $context
    ) {
        $discounts = $this->getProductsDiscounts(array($product), $customerGroup, $context);
        return array_shift($discounts);
    }

    /**
     * @inheritdoc
     */
    public function getProductsDiscounts(
        $products,
        Struct\Customer\Group $customerGroup,
        Struct\Context $context
    ) {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();
        $query->addSelect($this->fieldHelper->getPriceGroupDiscountFields())
            ->addSelect($this->fieldHelper->getPriceGroupFields());

        $query->from('s_core_pricegroups_discounts', 'priceGroupDiscount')
            ->innerJoin(
                'priceGroupDiscount',
                's_core_pricegroups',
                'priceGroup',
                'priceGroup.id = priceGroupDiscount.groupID'
            )
            ->innerJoin(
                'priceGroupDiscount',
                's_articles',
                'products',
                'products.pricegroupID = priceGroupDiscount.groupID'
            );

        $query->andWhere('priceGroupDiscount.customergroupID = :customerGroup')
            ->andWhere('products.id IN (:products)');

        $query->groupBy('priceGroupDiscount.id');

        $query->orderBy('priceGroupDiscount.groupID')
            ->addOrderBy('priceGroupDiscount.discountstart');

        $query->setParameter(':customerGroup', $customerGroup->getId())
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $discounts = array();
        foreach ($data as $priceDiscount) {
            $id = $priceDiscount['__priceGroupDiscount_groupID'];
            $discounts[$id][] = $this->priceHydrator->hydratePriceDiscount($priceDiscount);
        }

        $result = array();
        foreach ($products as $product) {
            if (!$product->getPriceGroup()) {
                continue;
            }

            $number = $product->getNumber();
            $groupId = $product->getPriceGroup()->getId();

            $result[$number] = $discounts[$groupId];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getHighestQuantityDiscount(
        Struct\Product\PriceGroup $priceGroup,
        Struct\Customer\Group $customerGroup,
        $quantity
    ) {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getPriceGroupDiscountFields())
            ->from('s_core_pricegroups_discounts', 'priceGroupDiscount')
            ->andWhere('priceGroupDiscount.groupID = :priceGroup')
            ->andWhere('priceGroupDiscount.customergroupID = :customerGroup')
            ->andWhere('priceGroupDiscount.discountstart <= :quantity')
            ->orderBy('priceGroupDiscount.discount', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $query->setParameter(':priceGroup', $priceGroup->getId())
            ->setParameter(':customerGroup', $customerGroup->getId())
            ->setParameter(':quantity', $quantity);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            return null;
        }

        return $this->priceHydrator->hydratePriceDiscount(
            $data
        );
    }
}
