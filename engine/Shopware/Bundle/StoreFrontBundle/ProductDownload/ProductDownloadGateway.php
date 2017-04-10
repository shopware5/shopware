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

namespace Shopware\Bundle\StoreFrontBundle\ProductDownload;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Common\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductDownloadGateway
{
    /**
     * @var ProductDownloadHydrator
     */
    private $downloadHydrator;

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
     * @param Connection                                           $connection
     * @param \Shopware\Bundle\StoreFrontBundle\Common\FieldHelper $fieldHelper
     * @param ProductDownloadHydrator                              $downloadHydrator
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ProductDownloadHydrator $downloadHydrator
    ) {
        $this->connection = $connection;
        $this->downloadHydrator = $downloadHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Bundle\StoreFrontBundle\Gateway\DownloadGatewayInterface::get()
     *
     * @param \Shopware\Bundle\StoreFrontBundle\Product\BaseProduct[] $products
     * @param TranslationContext                                      $context
     *
     * @return array indexed by the product number, each array element contains a \Shopware\Bundle\StoreFrontBundle\ProductDownload\Download array
     */
    public function getList($products, TranslationContext $context)
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
            ->where('download.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addDownloadTranslation($query, $context);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $downloads = [];
        foreach ($data as $row) {
            $key = $row['__download_articleID'];

            $download = $this->downloadHydrator->hydrate($row);
            $downloads[$key][] = $download;
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
