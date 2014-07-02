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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway;

/**
 * @package Shopware\Bundle\StoreFrontBundle\Gateway\DBAL
 */
class VariantMediaGateway implements Gateway\VariantMediaGatewayInterface
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
     * @var Hydrator\MediaHydrator
     */
    private $hydrator;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\MediaHydrator $hydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\MediaHydrator $hydrator
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
    public function getList($products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->getQuery($context);

        $query->andWhere('childImage.article_detail_id IN (:products)')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        $query->orderBy('image.main')
            ->addOrderBy('image.position');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $result = array();
        foreach ($data as $row) {
            $productId = $row['number'];
            $imageId   = $row['__image_id'];

            $result[$productId][$imageId] = $this->hydrator->hydrateProductImage($row);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getCovers($products, Struct\Context $context)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->getQuery($context);

        $query->andWhere('childImage.article_detail_id IN (:products)')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        $query->orderBy('image.main')
            ->addOrderBy('image.position');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = array();
        foreach ($data as $number => $row) {
            $cover = array_shift($row);

            $result[$number] = $this->hydrator->hydrateProductImage($cover);
        }

        return $result;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Context $context
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getQuery(Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect('variant.ordernumber as number')
            ->addSelect($this->fieldHelper->getMediaFields())
            ->addSelect($this->fieldHelper->getImageFields())
            ->addSelect($this->fieldHelper->getMediaSettingFields());

        $this->fieldHelper->addImageTranslation($query);
        $query->setParameter(':language', $context->getShop()->getId());

        $query->from('s_articles_img', 'image')
            ->innerJoin('image', 's_media', 'media', 'image.media_id = media.id')
            ->innerJoin('media', 's_media_album_settings', 'mediaSettings', 'mediaSettings.albumID = media.albumID')
            ->innerJoin('image', 's_articles_img', 'childImage', 'childImage.parent_id = image.id')
            ->innerJoin('image', 's_articles_details', 'variant', 'variant.id = childImage.article_detail_id')
            ->leftJoin('image', 's_media_attributes', 'mediaAttribute', 'mediaAttribute.mediaID = image.media_id')
            ->leftJoin('image', 's_articles_img_attributes', 'imageAttribute', 'imageAttribute.imageID = image.id')
        ;

        return $query;
    }

}
