<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;
use Shopware\Gateway\DBAL\Hydrator;

class ProductMedia
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
     * Returns an array of \Shopware\Struct\Media elements for the passed products.
     * The returned array is indexed with the product order number.
     *
     * This function returns only assigned media structs which has no configurator configuration.
     * Media structs which have a configurator configuration displayed only in the store front
     * if the customer selects the specify variant configuration.
     *
     * The returned array has to be indexed with the product number.
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white)
     *  - Product.2  (black)
     *
     * The function has to return an array which contains all product media structs for each passed product variation.
     * Product white & black shares the product media, so the function returns the following result:
     *
     * <php>
     * array(
     *     'Product.1' => array(
     *          Shopware\Struct\Media(id=1)
     *          Shopware\Struct\Media(id=2)
     *      ),
     *     'Product.2' => array(
     *          Shopware\Struct\Media(id=1)
     *          Shopware\Struct\Media(id=2)
     *      )
     * )
     * </php>
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return array
     */
    public function getList(array $products, Struct\Context $context)
    {
        $ids = array();
        foreach($products as $product) {
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
        foreach($data as $row) {
            $productId = $row['__image_articleID'];
            $imageId   = $row['__image_id'];

            $result[$productId][$imageId] = $this->hydrator->hydrateProductImage($row);
        }

        return $this->assignProductMedia($result, $products);
    }

    /**
     * Returns a list of \Shopware\Struct\Media which will be displayed as product covers.
     * This function is used for listings or sliders and called over the Shopware\Service\Media class.
     *
     * The returned array has to be indexed with the product number.
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white)
     *  - Product.2  (black)
     *
     * The function has to return an array which contains a cover for each passed product variation.
     * Product white & black shares the product cover, so the function returns the following result:
     *
     * <php>
     * array(
     *     'Product.1' => Shopware\Struct\Media(id=1)
     *     'Product.2' => Shopware\Struct\Media(id=1)
     * )
     * </php>
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     * @return Struct\Media[]
     */
    public function getCovers(array $products, Struct\Context $context)
    {
        $ids = array();
        foreach($products as $product) {
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
        foreach($data as $row) {
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
        foreach($products as $product) {
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