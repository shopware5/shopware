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

namespace Shopware\Bundle\StaticContentBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StaticContentBundle\Exception\EsdNotFoundException;
use Shopware\Bundle\StaticContentBundle\Gateway\EsdGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\EsdHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\Esd;

class EsdGateway implements EsdGatewayInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EsdHydrator
     */
    private $esdHydrator;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    public function __construct(
        Connection $connection,
        EsdHydrator $esdHydrator,
        FieldHelper $fieldHelper
    ) {
        $this->connection = $connection;
        $this->esdHydrator = $esdHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function loadEsdOfCustomer(int $customerId, int $esdId): Esd
    {
        $query = $this->getDefaultQuery()
            ->setParameter('customerId', $customerId)
            ->setParameter('esdId', $esdId);

        $result = $query->execute()->fetch();

        if (empty($result)) {
            $query = $this->getFallbackQuery()
                ->setParameter('customerId', $customerId)
                ->setParameter('esdId', $esdId);

            $result = $query->execute()->fetch();
        }

        if (empty($result)) {
            throw new EsdNotFoundException();
        }

        return $this->esdHydrator->hydrate($result);
    }

    protected function getDefaultQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->addSelect($this->fieldHelper->getEsdFields())
            ->from('s_articles_esd', 'esd')
            ->innerJoin('esd', 's_order_esd', 'orderEsd', 'esd.id = orderEsd.esdID')
            ->leftJoin('esd', 's_articles_esd_attributes', 'esdAttribute', 'esd.id = esdAttribute.esdID')
            ->andWhere('orderEsd.userID = :customerId')
            ->andWhere('orderEsd.orderdetailsID = :esdId')
        ;
    }

    protected function getFallbackQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select($this->fieldHelper->getEsdFields())
            ->from('s_articles_esd', 'esd')
            ->innerJoin('esd', 's_articles_details', 'articlesDetails', 'esd.articledetailsID = articlesDetails.id')
            ->innerJoin('articlesDetails', 's_order_details', 'orderDetails', 'articlesDetails.ordernumber = orderDetails.articleordernumber')
            ->innerJoin('orderDetails', 's_order', '`order`', '`order`.id = orderDetails.orderID')
            ->leftJoin('esd', 's_articles_esd_attributes', 'esdAttribute', 'esd.id = esdAttribute.esdID')
            ->andWhere('`order`.userID = :customerId')
            ->andWhere('orderDetails.id = :esdId')
        ;
    }
}
