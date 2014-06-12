<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;
use Shopware\Gateway\DBAL\Hydrator;

class VariantMedia
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
     * This function returns only assigned media structs which has a configurator configuration.
     * Media structs which have a configurator configuration displayed only in the store front
     * if the customer selects the specify variant configuration.
     *
     * The returned array has to be indexed with the product number.
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white / XL)
     *  - Product.2  (black / L)
     *
     * The
     * <php>
     * array(
     *     'Product.1' => array(
     *          Shopware\Struct\Media(id=3)  (configuration: color=white / size=XL)
     *          Shopware\Struct\Media(id=4)  (configuration: color=white)
     *      ),
     *     'Product.2' => array(
     *          Shopware\Struct\Media(id=1)  (configuration: color=black)
     *          Shopware\Struct\Media(id=2)  (configuration: size=L)
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
     * Returns an array of \Shopware\Struct\Media elements for the passed products.
     * The returned array is indexed with the product order number.
     *
     * This function returns only assigned media structs which has a configurator configuration.
     * Media structs which have a configurator configuration displayed only in the store front
     * if the customer selects the specify variant configuration.
     *
     * The returned array has to be indexed with the product number.
     *
     * The passed $products array contains in some case two variations of the same product.
     * For example:
     *  - Product.1  (white / XL)
     *  - Product.2  (black / L)
     *
     * The
     * <php>
     * array(
     *     'Product.1' => Shopware\Struct\Media(id=4)  (configuration: color=white)
     *     'Product.2' => Shopware\Struct\Media(id=1)  (configuration: color=black)
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
     * @return \Shopware\Components\Model\DBAL\QueryBuilder
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
