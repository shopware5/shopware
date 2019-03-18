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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

class LinkGateway implements Gateway\LinkGatewayInterface
{
    /**
     * @var Hydrator\LinkHydrator
     */
    private $linkHydrator;

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

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\LinkHydrator $linkHydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->linkHydrator = $linkHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $links = $this->getList([$product], $context);

        return array_shift($links);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, Struct\ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getLinkFields());

        $query->from('s_articles_information', 'link')
            ->leftJoin('link', 's_articles_information_attributes', 'linkAttribute', 'linkAttribute.informationID = link.id')
            ->where('link.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addLinkTranslation($query, $context);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $links = [];
        foreach ($data as $row) {
            $key = $row['__link_articleID'];
            $link = $this->linkHydrator->hydrate($row);
            $links[$key][] = $link;
        }

        $result = [];
        foreach ($products as $product) {
            if (isset($links[$product->getId()])) {
                $result[$product->getNumber()] = $links[$product->getId()];
            }
        }

        return $result;
    }
}
