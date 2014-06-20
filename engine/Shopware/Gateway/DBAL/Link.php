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

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

/**
 * @package Shopware\Gateway\DBAL
 */
class Link implements \Shopware\Gateway\Link
{
    /**
     * @var Hydrator\Link
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
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Link $linkHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Link $linkHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->fieldHelper = $fieldHelper;
        $this->linkHydrator = $linkHydrator;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $links = $this->getList(array($product), $context);
        return array_shift($links);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getLinkFields());

        $query->from('s_articles_information', 'link')
            ->leftJoin(
                'link',
                's_articles_information_attributes',
                'linkAttribute',
                'linkAttribute.informationID = link.id'
            );

        $query->where('link.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $links = array();
        foreach ($data as $row) {
            $key = $row['articleID'];

            $link = $this->linkHydrator->hydrate($row);

            $links[$key][] = $link;
        }

        $result = array();
        foreach ($products as $product) {
            $result[$product->getNumber()] = $links[$product->getId()];
        }

        return $result;
    }
}
