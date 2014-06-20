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
use Shopware\Struct;
use Shopware\Gateway\DBAL\Hydrator;

/**
 * @package Shopware\Gateway\DBAL
 */
class ProductMedia implements \Shopware\Gateway\ProductMedia
{
    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Hydrator\Media
     */
    private $hydrator;

    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Media $hydrator
    ) {
        $this->entityManager = $entityManager;
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $media = $this->getList(array($product), $context);
        return array_shift($media);
    }

    /**
     * @inheritdoc
     */
    public function getCover(Struct\ListProduct $product, Struct\Context $context)
    {
        $covers = $this->getCovers(array($product), $context);
        return array_shift($covers);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->getQuery($context);

        $query->andWhere('childImage.id IS NULL')
            ->andWhere('image.articleID IN (:products)');

        $query->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        $query->orderBy('image.main')
            ->addOrderBy('image.position');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $result = array();
        foreach ($data as $row) {
            $productId = $row['__image_articleID'];
            $imageId   = $row['__image_id'];

            $result[$productId][$imageId] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($result, $products);
    }

    /**
     * @inheritdoc
     */
    public function getCovers(array $products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->getQuery($context);

        $query->where('image.main = 1')
            ->andWhere('image.articleID IN (:products)')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $covers = array();
        foreach ($data as $row) {
            $id = $row['__image_articleID'];

            $covers[$id] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($covers, $products);
    }

    /**
     * @param array $media
     * @param Struct\ListProduct[] $products
     * @return array
     */
    private function assignProductMedia(array $media, array $products)
    {
        $result = array();
        foreach ($products as $product) {
            $number = $product->getNumber();

            $productMedia = $media[$product->getId()];

            if (!$productMedia) {
                continue;
            }

            $result[$number] = $productMedia;
        }

        return $result;
    }

    /**
     * @param \Shopware\Struct\Context $context
     * @return \Shopware\Components\Model\DBAL\QueryBuilder
     */
    private function getQuery(Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getImageFields())
            ->addSelect($this->fieldHelper->getMediaSettingFields());

        $this->fieldHelper->addImageTranslation($query);
        $query->setParameter(':language', $context->getShop()->getId());

        $query->from('s_articles_img', 'image')
            ->innerJoin('image', 's_media', 'media', 'image.media_id = media.id')
            ->innerJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->leftJoin('image', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = image.media_id')
            ->leftJoin('image', 's_articles_img_attributes', 'imageAttribute', 'imageAttribute.imageID = image.id')
            ->leftJoin('image', 's_articles_img', 'childImage', 'childImage.parent_id = image.id');

        $query->andWhere('image.parent_id IS NULL');

        return $query;
    }
}
