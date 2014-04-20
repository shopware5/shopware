<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class PriceGroupDiscount extends Gateway
{
    /**
     * @var Hydrator\Price
     */
    private $priceHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Price $priceHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Price $priceHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
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
