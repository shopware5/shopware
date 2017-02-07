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

namespace Shopware\Bundle\CartBundle\Infrastructure\Product;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryInformation;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItemCollection;
use Shopware\Bundle\CartBundle\Domain\Product\ProductDeliveryGatewayInterface;

class ProductDeliveryGateway implements ProductDeliveryGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param LineItemCollection $collection
     * @param CartContextInterface $context
     * @return DeliveryInformation[] indexed by number
     */
    public function get(LineItemCollection $collection, CartContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'variant.ordernumber',
            'variant.instock',
            'variant.weight',
            'variant.width',
            'variant.height',
            'variant.length',
            'variant.shippingtime',
        ]);
        $query->from('s_articles_details', 'variant');
        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $collection->getIdentifiers(), Connection::PARAM_STR_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        $deliveryInformation = [];

        foreach ($data as $row) {
            $number = $row['ordernumber'];

            $earliestDeliveryInDays = 1;
            $deliveryTimeInDays = 3;
            $outOfStockDelayInDays = 10;

            $earliestInterval = new \DateInterval('P' . $earliestDeliveryInDays . 'D');
            $deliveryTimeInterval = new \DateInterval('P' . $deliveryTimeInDays . 'D');
            $delayInterval = new \DateInterval('P' . $outOfStockDelayInDays . 'D');

            $deliveryInformation[$number] = new DeliveryInformation(
                (float) $row['instock'],
                (float) $row['height'],
                (float) $row['width'],
                (float) $row['length'],
                (float) $row['weight'],
                new DeliveryDate(
                    (new \DateTime())
                        ->add($earliestInterval),
                    (new \DateTime())
                        ->add($earliestInterval)
                        ->add($deliveryTimeInterval)
                ),
                new DeliveryDate(
                    (new \DateTime())
                        ->add($delayInterval)
                        ->add($earliestInterval),
                    (new \DateTime())
                        ->add($delayInterval)
                        ->add($earliestInterval)
                        ->add($deliveryTimeInterval)
                )
            );
        }

        return $deliveryInformation;
    }
}
