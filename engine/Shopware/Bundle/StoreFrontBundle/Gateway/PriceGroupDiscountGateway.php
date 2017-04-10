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

namespace Shopware\Bundle\StoreFrontBundle\Gateway;

use Doctrine\DBAL\Connection;

use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class PriceGroupDiscountGateway
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
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection             $connection
     * @param FieldHelper            $fieldHelper
     * @param Hydrator\PriceHydrator $priceHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\PriceHydrator $priceHydrator
    ) {
        $this->connection = $connection;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * The \Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup requires the following data:
     * - Price group base data
     * - Price group discounts for the provided customer group
     *
     * @param Struct\Customer\Group       $customerGroup
     *
     * @return Struct\Product\PriceGroup[] Indexed by the price group id
     */
    public function getPriceGroups(Struct\Customer\Group $customerGroup)
    {
        $query = $this->connection->createQueryBuilder();

        $query->addSelect('priceGroupDiscount.groupID')
            ->addSelect($this->fieldHelper->getPriceGroupDiscountFields())
            ->addSelect($this->fieldHelper->getPriceGroupFields());

        $query->from('s_core_pricegroups_discounts', 'priceGroupDiscount')
            ->innerJoin('priceGroupDiscount', 's_core_pricegroups', 'priceGroup', 'priceGroup.id = priceGroupDiscount.groupID')
            ->andWhere('priceGroupDiscount.customergroupID = :customerGroup')
            ->groupBy('priceGroupDiscount.id')
            ->orderBy('priceGroupDiscount.groupID')
            ->addOrderBy('priceGroupDiscount.discountstart')
            ->setParameter(':customerGroup', $customerGroup->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $priceGroups = [];

        foreach ($data as $row) {
            $priceGroup = $this->priceHydrator->hydratePriceGroup($row);

            foreach ($priceGroup->getDiscounts() as $discount) {
                $discount->setCustomerGroup($customerGroup);
            }

            $priceGroups[$priceGroup->getId()] = $priceGroup;
        }

        return $priceGroups;
    }
}
