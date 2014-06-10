<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class PriceGroupDiscount
{
    /**
     * @var Hydrator\Price
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
     * @param Hydrator\Price $priceHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Price $priceHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @return array
     */
    public function getProductsDiscounts(
        array $products,
        Struct\Customer\Group $customerGroup
    ) {
        $ids = array();
        foreach($products as $product) {
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
        foreach($data as $priceDiscount) {
            $id = $priceDiscount['__priceGroupDiscount_groupID'];
            $discounts[$id][] = $this->priceHydrator->hydratePriceDiscount($priceDiscount);
        }
        
        $result = array();
        foreach($products as $product) {
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
     * Returns the highest percentage discount for the
     * customer group of the passed price group and quantity.
     *
     * @param Struct\Product\PriceGroup $priceGroup
     * @param Struct\Customer\Group $customerGroup
     * @param $quantity
     * @return Struct\Product\PriceDiscount
     */
    public function getHighestQuantityDiscount(
        Struct\Product\PriceGroup $priceGroup,
        Struct\Customer\Group $customerGroup,
        $quantity
    ) {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('discounts.discount'))
            ->from('s_core_pricegroups_discounts', 'discounts')
            ->andWhere('discounts.groupID = :priceGroup')
            ->andWhere('discounts.customergroupID = :customerGroup')
            ->andWhere('discounts.discountstart <= :quantity')
            ->orderBy('discounts.discount', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $query->setParameter(':priceGroup', $priceGroup->getId())
            ->setParameter(':customerGroup', $customerGroup->getId())
            ->setParameter(':quantity', $quantity);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetch(\PDO::FETCH_COLUMN);

        return $this->priceHydrator->hydratePriceDiscount(
            $data
        );
    }
}
