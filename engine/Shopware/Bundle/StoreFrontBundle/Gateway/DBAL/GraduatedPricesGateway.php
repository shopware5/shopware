<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PriceHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\GraduatedPricesGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class GraduatedPricesGateway implements GraduatedPricesGatewayInterface
{
    private PriceHydrator $priceHydrator;

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
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        PriceHydrator $priceHydrator
    ) {
        $this->connection = $connection;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(
        ListProduct $product,
        ShopContextInterface $context,
        Group $customerGroup
    ) {
        $prices = $this->getList([$product], $context, $customerGroup);

        return array_shift($prices);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context, Group $customerGroup)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getPriceFields());
        $query->addSelect('variants.ordernumber as number');
        $query->addSelect('GREATEST(price.from, variants.minpurchase) __price_from');

        $query->from('s_articles_prices', 'price')
            ->innerJoin('price', 's_articles_details', 'variants', 'variants.id = price.articledetailsID')
            ->leftJoin('price', 's_articles_prices_attributes', 'priceAttribute', 'priceAttribute.priceID = price.id')
            ->where('price.articledetailsID IN (:products)')
            ->andWhere('price.pricegroup = :customerGroup')
            ->andWhere($query->expr()->orX('price.to >= variants.minpurchase', 'price.to = "beliebig"'))
            ->orderBy('price.articledetailsID', 'ASC')
            ->addOrderBy('price.from', 'ASC')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':customerGroup', $customerGroup->getKey());

        $this->fieldHelper->addPriceTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $prices = [];
        foreach ($data as $row) {
            $productNumber = (string) $row['number'];
            $prices[$productNumber][] = $this->priceHydrator->hydratePriceRule($row);
        }

        return $prices;
    }
}
