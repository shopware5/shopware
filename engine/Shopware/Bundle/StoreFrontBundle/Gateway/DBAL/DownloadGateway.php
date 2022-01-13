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
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\DownloadHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DownloadGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class DownloadGateway implements DownloadGatewayInterface
{
    private DownloadHydrator $downloadHydrator;

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
        DownloadHydrator $downloadHydrator
    ) {
        $this->connection = $connection;
        $this->downloadHydrator = $downloadHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ShopContextInterface $context)
    {
        $downloads = $this->getList([$product], $context);

        return array_shift($downloads);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }
        $ids = array_unique($ids);

        $query = $this->connection->createQueryBuilder();

        $query->select($this->fieldHelper->getDownloadFields());

        $query->from('s_articles_downloads', 'download')
            ->leftJoin('download', 's_articles_downloads_attributes', 'downloadAttribute', 'downloadAttribute.downloadID = download.id')
            ->innerJoin('download', 's_media', 'media', 'media.path = download.filename')
            ->where('download.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addDownloadTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $downloads = [];
        foreach ($data as $row) {
            $key = $row['__download_articleID'];

            $downloads[$key][] = $this->downloadHydrator->hydrate($row);
        }

        $result = [];
        foreach ($products as $product) {
            if (isset($downloads[$product->getId()])) {
                $result[$product->getNumber()] = $downloads[$product->getId()];
            }
        }

        return $result;
    }
}
