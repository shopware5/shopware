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

namespace Shopware\Components\Api\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;
use Exception;
use RuntimeException;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Configurator;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Download;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Link;
use Shopware\Models\Article\Repository;
use Shopware\Models\Article\SeoCategory;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Price\Group;
use Shopware\Models\Property\Group as PropertyGroup;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Relation;
use Shopware\Models\Property\Value;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;
use Shopware_Components_Translation;

/**
 * Article API Resource
 */
class Article extends Resource implements BatchInterface
{
    /**
     * @var Shopware_Components_Translation
     */
    private $translationComponent;

    public function __construct(Shopware_Components_Translation $translationComponent = null)
    {
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get(Shopware_Components_Translation::class);
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(ProductModel::class);
    }

    /**
     * @return Repository
     */
    public function getDetailRepository()
    {
        return $this->getManager()->getRepository(Detail::class);
    }

    /**
     * Little helper function for the ...ByNumber methods
     *
     * @param string $number
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return int
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ParameterMissingException('id');
        }

        $productVariant = $this->getDetailRepository()->findOneBy(['number' => $number]);

        if (!$productVariant instanceof Detail) {
            throw new NotFoundException(sprintf('Product by number "%s" not found', $number));
        }

        return $productVariant->getArticle()->getId();
    }

    /**
     * Convenience method to get a product by number
     *
     * @param string $number
     *
     * @return array|ProductModel
     */
    public function getOneByNumber($number, array $options = [])
    {
        $id = $this->getIdFromNumber($number);

        return $this->getOne($id, $options);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return array|ProductModel
     */
    public function getOne($id, array $options = [])
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([
            'article',
            'mainDetail',
            'mainDetailPrices',
            'tax',
            'propertyValues',
            'configuratorOptions',
            'supplier',
            'priceCustomGroup',
            'mainDetailAttribute',
            'propertyGroup',
            'customerGroups',
        ])
            ->from(ProductModel::class, 'article')
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('mainDetail.prices', 'mainDetailPrices')
            ->leftJoin('mainDetailPrices.customerGroup', 'priceCustomGroup')
            ->leftJoin('article.tax', 'tax')
            ->leftJoin('article.propertyValues', 'propertyValues')
            ->leftJoin('article.supplier', 'supplier')
            ->leftJoin('mainDetail.attribute', 'mainDetailAttribute')
            ->leftJoin('mainDetail.configuratorOptions', 'configuratorOptions')
            ->leftJoin('article.propertyGroup', 'propertyGroup')
            ->leftJoin('article.customerGroups', 'customerGroups')
            ->where('article.id = ?1')
            ->setParameter(1, $id);

        $product = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if ($product === null) {
            throw new NotFoundException(sprintf('Product by id "%d" not found', $id));
        }

        if ($this->getResultMode() === self::HYDRATE_ARRAY) {
            $product['images'] = $this->getArticleImages($id);
            $product['configuratorSet'] = $this->getArticleConfiguratorSet($id);
            $product['links'] = $this->getArticleLinks($id);
            $product['downloads'] = $this->getArticleDownloads($id);
            $product['categories'] = $this->getArticleCategories($id);
            $product['similar'] = $this->getArticleSimilar($id);
            $product['related'] = $this->getArticleRelated($id);
            $product['details'] = $this->getArticleVariants($id);
            $product['seoCategories'] = $this->getArticleSeoCategories($id);

            if (isset($options['considerTaxInput']) && $options['considerTaxInput']) {
                $product['mainDetail']['prices'] = $this->getTaxPrices(
                    $product['mainDetail']['prices'],
                    $product['tax']['tax']
                );

                foreach ($product['details'] as &$detail) {
                    $detail['prices'] = $this->getTaxPrices(
                        $detail['prices'],
                        $product['tax']['tax']
                    );
                }
                unset($detail);
            }

            $query = $this->getManager()->createQuery('SELECT shop FROM Shopware\Models\Shop\Shop as shop');

            foreach ($query->getArrayResult() as $shop) {
                $translation = $this->translationComponent->read($shop['id'], 'article', $id);
                if (!empty($translation)) {
                    $translation['shopId'] = $shop['id'];
                    $product['translations'][$shop['id']] = $translation;
                }
            }

            if (isset($options['language']) && !empty($options['language'])) {
                $shop = $this->findEntityByConditions(Shop::class, [
                    ['id' => $options['language']],
                    ['shop' => $options['language']],
                ]);
                if (!$shop instanceof Shop) {
                    throw new ModelNotFoundException(Shop::class, $options['language']);
                }

                $product = $this->translateArticle($product, $shop);
            }
        }

        return $product;
    }

    /**
     * Helper function which calculates formats the variant prices for each customer group.
     * If the customer group configured "taxInput" as true, the price will be formatted as gross.
     *
     * @param array $prices  Array of the variant prices
     * @param float $taxRate Float value of the product tax (example: 19.00)
     *
     * @return array
     */
    public function getTaxPrices(array $prices, $taxRate)
    {
        foreach ($prices as &$price) {
            $price['net'] = $price['price'];
            if ($price['customerGroup'] && $price['customerGroup']['taxInput']) {
                $price['price'] *= (($taxRate + 100) / 100);
            }
        }

        return $prices;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [], array $options = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('article')
            ->addSelect(['mainDetail', 'attribute'])
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('mainDetail.attribute', 'attribute')
            ->addFilter($criteria)
            ->addOrderBy($orderBy)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        $products = $paginator->getIterator()->getArrayCopy();

        if ($this->getResultMode() === self::HYDRATE_ARRAY
            && isset($options['language'])
            && !empty($options['language'])) {
            $shop = $this->findEntityByConditions(Shop::class, [['id' => $options['language']]]);
            if (!$shop instanceof Shop) {
                throw new ModelNotFoundException(Shop::class, $options['language']);
            }

            foreach ($products as &$product) {
                $product = $this->translateArticle($product, $shop);
            }
        }

        return ['data' => $products, 'total' => $totalResult];
    }

    /**
     * @throws CustomValidationException
     * @throws ValidationException
     *
     * @return ProductModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $product = new ProductModel();

        $translations = [];
        if (!empty($params['translations'])) {
            $translations = $params['translations'];
            unset($params['translations']);
        }

        $params = $this->prepareAssociatedData($params, $product);

        $product->fromArray($params);

        $violations = $this->getManager()->validate($product);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->getManager()->persist($product);
        $this->flush();

        if (!empty($translations)) {
            $this->writeTranslations($product->getId(), $translations);
        }

        return $product;
    }

    /**
     * Convenience method to update a product by number
     *
     * @param string $number
     *
     * @throws ValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return ProductModel
     */
    public function updateByNumber($number, array $params)
    {
        $id = $this->getIdFromNumber($number);

        return $this->update($id, $params);
    }

    /**
     * @param int $id
     *
     * @throws ValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return ProductModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([
            'product',
            'mainVariant',
            'mainVariantPrices',
            'mainVariantAttribute',
            'tax',
            'supplier',
        ])
            ->from(ProductModel::class, 'product')
            ->leftJoin('product.mainDetail', 'mainVariant')
            ->leftJoin('mainVariant.prices', 'mainVariantPrices')
            ->leftJoin('product.tax', 'tax')
            ->leftJoin('product.supplier', 'supplier')
            ->leftJoin('mainVariant.attribute', 'mainVariantAttribute')
            ->where('product.id = ?1')
            ->setParameter(1, $id);

        $product = $builder->getQuery()->getOneOrNullResult(self::HYDRATE_OBJECT);

        if (!$product) {
            throw new NotFoundException(sprintf('Product by id "%d" not found', $id));
        }

        $translations = [];
        if (!empty($params['translations'])) {
            $translations = $params['translations'];
            unset($params['translations']);
        }

        $params = $this->prepareAssociatedData($params, $product);

        $product->fromArray($params);
        $violations = $this->getManager()->validate($product);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->flush();

        if (!empty($translations)) {
            $this->writeTranslations($product->getId(), $translations);
        }

        return $product;
    }

    /**
     * Convenience function to delete a product by number
     *
     * @param string $number
     *
     * @throws Exception
     */
    public function deleteByNumber($number)
    {
        throw new RuntimeException("Deleting products by number isn't possible, yet.");
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return ProductModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $product = $this->getRepository()->find($id);

        if (!$product instanceof ProductModel) {
            throw new NotFoundException(sprintf('Product by "id" %d not found', $id));
        }

        // Delete associated data
        $query = $this->getRepository()->getRemovePricesQuery($product->getId());
        $query->execute();
        $query = $this->getRepository()->getRemoveESDQuery($product->getId());
        $query->execute();
        $query = $this->getRepository()->getRemoveArticleTranslationsQuery($product->getId());
        $query->execute();

        $sql = 'DELETE FROM s_articles_translations WHERE articleID = ?';
        $this->getManager()->getConnection()->executeQuery($sql, [$product->getId()]);

        $this->removeArticleDetails($product);

        $this->getManager()->remove($product);
        $this->flush();

        return $product;
    }

    /**
     * Helper function which converts the passed data for the main variant of the product.
     *
     * @return array
     */
    public function prepareMainDetail(array $data, ProductModel $article)
    {
        $detail = $article->getMainDetail();
        if (!$detail) {
            $detail = new Detail();
            $detail->setKind(1);
            $detail->setArticle($article);
            $article->setMainDetail($detail);
        }

        if (!$data['mainDetail']) {
            $data['mainDetail'] = [];
        }

        $data['mainDetail'] = $this->getVariantResource()->prepareMainVariantData($data['mainDetail'], $article, $detail);

        return $data;
    }

    /**
     * Short method to completely generate all images from a product, main images and variant images
     *
     * @param bool $force Force all images to be regenerated
     *
     * @see \Shopware\Components\Api\Resource\Article::generateMainThumbnails()
     * @see \Shopware\Components\Api\Resource\Article::generateVariantImages()
     */
    public function generateImages(ProductModel $article, $force = false)
    {
        $this->generateMainThumbnails($article, $force);
        $this->generateVariantImages($article, $force);
    }

    /**
     * Generate the main thumbnails of a product
     *
     * @param bool $force Force to regenerate main thumbnails
     */
    public function generateMainThumbnails(ProductModel $article, $force = false)
    {
        $generator = $this->getContainer()->get(Manager::class);

        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        foreach ($article->getImages() as $image) {
            $media = $image->getMedia();

            $projectDir = $this->getContainer()->getParameter('shopware.app.rootDir');

            if (!\is_string($projectDir)) {
                throw new RuntimeException('Parameter shopware.app.rootDir has to be a string');
            }

            if (!$force && $mediaService->has($projectDir . $media->getPath())) {
                continue;
            }

            foreach ($media->getThumbnailFilePaths() as $size => $path) {
                $generator->createMediaThumbnail($media, [$size], true);
            }
        }
    }

    /**
     * This method generates all variant image entities for a given product model instance.
     * The method expects that the variants and the mapping of the product images already exist.
     *
     * @param bool $force Force variant image regeneration
     */
    public function generateVariantImages(ProductModel $article, $force = false)
    {
        $builder = $this->getArticleImageMappingsQuery($article->getId());

        foreach ($builder->getQuery()->getResult() as $mapping) {
            $builder = $this->getArticleVariantQuery($article->getId());

            foreach ($mapping->getRules() as $rule) {
                $option = $rule->getOption();
                $alias = 'option' . $option->getId();
                $builder->innerJoin('variants.configuratorOptions', $alias, 'WITH', $alias . '.id = :' . $alias)
                    ->setParameter($alias, $option->getId());
            }

            /** @var Detail $variant */
            foreach ($builder->getQuery()->getResult() as $variant) {
                if (!$force && $this->getCollectionElementByProperty(
                    $variant->getImages(),
                    'parent',
                    $mapping->getImage()
                )) {
                    continue;
                }

                $image = $this->getVariantResource()->createVariantImage(
                    $mapping->getImage(),
                    $variant
                );

                $variant->getImages()->add($image);
            }
        }
        $this->getManager()->flush();
    }

    /**
     * Helper function which creates a new product image with the passed media object.
     *
     * @return Image
     */
    public function createNewArticleImage(ProductModel $article, MediaModel $media)
    {
        $image = new Image();
        $image = $this->updateArticleImageWithMedia(
            $article,
            $image,
            $media
        );
        $this->getManager()->persist($image);
        $article->getImages()->add($image);

        return $image;
    }

    /**
     * Helper function to map the media data into a product image
     *
     * @return Image
     */
    public function updateArticleImageWithMedia(ProductModel $article, Image $image, MediaModel $media)
    {
        $image->setMain(2);
        $image->setMedia($media);
        $image->setArticle($article);
        $image->setPath($media->getName());
        $image->setExtension($media->getExtension());
        $image->setDescription($media->getDescription());

        return $image;
    }

    /**
     * @param int   $articleId
     * @param array $translations
     *
     * @throws CustomValidationException
     */
    public function writeTranslations($articleId, $translations)
    {
        $whitelist = $this->getAttributeProperties();
        array_push(
            $whitelist,
            'metaTitle',
            'name',
            'description',
            'descriptionLong',
            'shippingTime',
            'keywords',
            'packUnit'
        );

        foreach ($translations as $translation) {
            $shop = $this->getManager()->find(Shop::class, $translation['shopId']);
            if (!$shop instanceof Shop) {
                throw new CustomValidationException(sprintf('Shop by id "%s" not found', $translation['shopId']));
            }

            // Backward compatibility for attribute translations
            foreach ($translation as $key => $value) {
                $attrKey = '__attribute_' . $key;
                if (\in_array($attrKey, $whitelist) && !isset($translation[$attrKey])) {
                    $translation[$attrKey] = $value;
                }
            }

            $data = array_intersect_key($translation, array_flip($whitelist));
            $this->translationComponent->write($shop->getId(), 'article', $articleId, $data);
        }
    }

    public function getIdByData($data)
    {
        $id = null;

        if (isset($data['id'])) {
            $id = $data['id'];
        } elseif (isset($data['mainDetail']['number'])) {
            try {
                $id = $this->getIdFromNumber($data['mainDetail']['number']);
            } catch (NotFoundException $e) {
                return false;
            }
        }

        if (!$id) {
            return false;
        }

        $model = $this->getManager()->find(ProductModel::class, $id);

        if ($model) {
            return $id;
        }

        return false;
    }

    /**
     * @return Variant
     */
    protected function getVariantResource()
    {
        return $this->getContainer()->get(Variant::class);
    }

    /**
     * @return Translation
     */
    protected function getTranslationResource()
    {
        return $this->getContainer()->get(Translation::class);
    }

    /**
     * @return Media
     */
    protected function getMediaResource()
    {
        return $this->getContainer()->get(Media::class);
    }

    /**
     * Selects the configured product configurator set and the assigned
     * configurator groups of the set.
     * The groups are sorted by the position value.
     *
     * @param int $articleId
     */
    protected function getArticleConfiguratorSet($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups'])
            ->from(Configurator\Set::class, 'configuratorSet')
            ->innerJoin('configuratorSet.articles', 'article')
            ->leftJoin('configuratorSet.groups', 'groups')
            ->addOrderBy('groups.position', 'ASC')
            ->where('article.id = :articleId')
            ->setParameter(':articleId', $articleId);

        return $this->getSingleResult($builder);
    }

    /**
     * Selects all images of the main variant of the passed product id.
     * The images are sorted by their position value.
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleImages($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['images'])
            ->from(Image::class, 'images')
            ->innerJoin('images.article', 'article')
            ->where('article.id = :articleId')
            ->orderBy('images.position', 'ASC')
            ->andWhere('images.parentId IS NULL')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Selects all configured download files for the passed product id.
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleDownloads($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['downloads'])
            ->from(Download::class, 'downloads')
            ->innerJoin('downloads.article', 'article')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function which selects all configured links
     * for the passed product id.
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleLinks($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['links'])
            ->from(Link::class, 'links')
            ->innerJoin('links.article', 'article')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function which selects all categories of the passed product id.
     * This function returns only the directly assigned categories.
     * To prevent a big data, this function selects only the category name and id.
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleCategories($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['categories.id', 'categories.name'])
            ->from(Category::class, 'categories')
            ->innerJoin('categories.articles', 'articles')
            ->where('articles.id = :articleId')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function which selects all similar products of the passed product id.
     *
     * @param int $articleId
     */
    protected function getArticleSimilar($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['article', 'PARTIAL similar.{id, name}'])
            ->from(ProductModel::class, 'article')
            ->innerJoin('article.similar', 'similar')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $product = $this->getSingleResult($builder);

        return $product['similar'];
    }

    /**
     * Helper function which selects all accessory products of the passed product id.
     *
     * @param int $articleId
     */
    protected function getArticleRelated($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['article', 'PARTIAL related.{id, name}'])
            ->from(ProductModel::class, 'article')
            ->innerJoin('article.related', 'related')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $product = $this->getSingleResult($builder);

        return $product['related'];
    }

    /**
     * Returns the configured product seo categories.
     * Those categories are used for the seo url generation.
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleSeoCategories($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['seoCategories', 'category'])
            ->from(SeoCategory::class, 'seoCategories')
            ->innerJoin('seoCategories.category', 'category')
            ->where('seoCategories.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        return $builder->getQuery()->getArrayResult();
    }

    /**
     * Helper function which loads all non-main variants of
     * the passed product id.
     * Additionally, the function selects the variant prices
     * and configurator options for each variant.
     *
     * @param int $articleId
     *
     * @return array
     */
    protected function getArticleVariants($articleId)
    {
        $builder = $this->getRepository()->getVariantDetailQuery();
        $builder->andWhere('article.id = :articleId')
            ->andWhere('variants.kind = 2')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function to remove product details for a given product
     *
     * @param ProductModel $article
     */
    protected function removeArticleDetails($article)
    {
        $sql = 'SELECT id FROM s_articles_details WHERE articleID = ? AND kind != 1';

        foreach (Shopware()->Db()->fetchAll($sql, [$article->getId()]) as $detail) {
            $query = $this->getRepository()->getRemoveAttributesQuery($detail['id']);
            $query->execute();

            $query = $this->getRepository()->getRemoveImageQuery($detail['id']);
            $query->execute();

            $sql = 'DELETE FROM s_article_configurator_option_relations WHERE article_id = ?';
            $this->getManager()->getConnection()->executeQuery($sql, [$detail['id']]);

            $sql = 'DELETE FROM s_articles_prices WHERE articledetailsID = ?';
            $this->getManager()->getConnection()->executeQuery($sql, [$detail['id']]);

            $query = $this->getRepository()->getRemoveVariantTranslationsQuery($detail['id']);
            $query->execute();

            $query = $this->getRepository()->getRemoveDetailQuery($detail['id']);
            $query->execute();
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareAssociatedData($data, ProductModel $article)
    {
        $data = $this->prepareArticleAssociatedData($data, $article);
        $data = $this->prepareCategoryAssociatedData($data, $article);
        $data = $this->prepareSeoCategoryAssociatedData($data, $article);

        $data = $this->prepareRelatedAssociatedData($data, $article);
        $data = $this->prepareSimilarAssociatedData($data, $article);
        $data = $this->prepareAvoidCustomerGroups($data, $article);
        $data = $this->preparePropertyValuesData($data, $article);
        $data = $this->prepareDownloadsAssociatedData($data, $article);
        $data = $this->prepareConfiguratorSet($data, $article);

        // Need to set the tax data directly for following price calculations which use the tax object of the article
        if (isset($data['tax'])) {
            $article->setTax($data['tax']);
        }

        if (isset($data['configuratorSet'])) {
            $article->setConfiguratorSet($data['configuratorSet']);
        }

        $data = $this->prepareImageAssociatedData($data, $article);
        $data = $this->prepareAttributeAssociatedData($data, $article);

        // The mainDetail gets its initial value for lastStock from $article, so this has to be set beforehand
        if (isset($data['lastStock'])) {
            $article->setLastStock((bool) $data['lastStock']);
        }

        $data = $this->prepareMainDetail($data, $article);
        $data = $this->prepareVariants($data, $article);

        unset($data['images']);

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareVariants($data, ProductModel $article)
    {
        unset($data['details']);

        if (!isset($data['variants'])) {
            return $data;
        }

        $setFirstVariantMain = false;
        // Delete old main, if it has no configurator options
        // and if none of the following variants has the mainDetail's number
        $oldMainDetail = $article->getMainDetail();

        if ($oldMainDetail) {
            $mainDetailGetsConfigurator = false;
            foreach ($data['variants'] as $variantData) {
                if (isset($variantData['configuratorOptions']) && \is_array($variantData['configuratorOptions'])) {
                    $mainDetailGetsConfigurator = true;
                    break;
                }
            }

            if (!$mainDetailGetsConfigurator && \count($oldMainDetail->getConfiguratorOptions()) === 0) {
                $this->getManager()->remove($oldMainDetail);
                $setFirstVariantMain = true;
            }
        }

        // If the mainDetail was deleted, set the first variant as mainDetail.
        // If another variant has set isMain to true, this variant will become a usual variant again
        if ($setFirstVariantMain) {
            $data['variants'][0]['isMain'] = true;
        }

        $variants = [];
        if (isset($data['__options_variants']) && $data['__options_variants']['replace']) {
            $this->removeArticleDetails($article);
        }

        foreach ($data['variants'] as $variantData) {
            if (isset($variantData['id'])) {
                $variant = $this->getVariantResource()->internalUpdate(
                    $variantData['id'],
                    $variantData,
                    $article
                );
            } else {
                $variant = null;

                // The number property can be set for two reasons.
                // 1. Use the number as the identifier to update an existing variant
                // 2. Use this number for the new variant
                if (isset($variantData['number'])) {
                    $variant = $this->getManager()->getRepository(Detail::class)->findOneBy([
                        'number' => $variantData['number'],
                        'articleId' => $article->getId(),
                    ]);
                }

                // If the variant was found over the number, update the existing
                if ($variant) {
                    $variant = $this->getVariantResource()->internalUpdate(
                        $variant->getId(),
                        $variantData,
                        $article
                    );
                } else {
                    // Otherwise, the number passed to use as order number for the new variant
                    $variant = $this->getVariantResource()->internalCreate($variantData, $article);
                }
            }

            if ($variantData['isMain'] || $variantData['standard']) {
                $newMain = $variant;
                $newMain->setKind(1);
                $oldMainId = $article->getMainDetail()->getId();

                // Check for old main articles:
                // If old main article has configurator options, use it as a usual variant
                // if the old main article does not have any configurator options, delete it
                if (isset($data['mainDetail'])) {
                    $oldMain = $data['mainDetail'];

                    if ($oldMain instanceof Detail) {
                        $oldMain->setKind(2);
                        if ($oldMain->getNumber() && $oldMain->getConfiguratorOptions()->count() > 0) {
                            $variant = $oldMain;
                        } else {
                            $this->getManager()->remove($oldMain);
                        }
                    } else {
                        $oldMain['kind'] = 2;
                        if (!empty($oldMain['number']) && !empty($oldMain['configuratorOptions'])) {
                            $variant = $oldMain;
                        } elseif (!empty($oldMain['number'])) {
                            $oldMain = $this->getDetailRepository()->findOneBy(['number' => $oldMain['number']]);
                            $oldMainConfiguratorOptions = $oldMain instanceof Detail && $oldMain->getConfiguratorOptions() !== null ? $oldMain->getConfiguratorOptions()->toArray() : null;
                            if ($oldMain && empty($oldMainConfiguratorOptions)) {
                                $this->getManager()->remove($oldMain);
                            }
                        }
                    }
                }

                $data['mainDetail'] = $newMain;
            }

            $variants[] = $variant;
        }

        // If the main variant was changed,
        if (isset($oldMainId, $newMain) && (int) $oldMainId !== (int) $newMain->getId()) {
            $oldMainVariantProcessed = false;

            foreach ($variants as $processedVariant) {
                if ((int) $processedVariant->getId() === (int) $oldMainId) {
                    $processedVariant->setKind(2);
                    $oldMainVariantProcessed = true;
                    break;
                }
            }

            if (!$oldMainVariantProcessed) {
                $oldMain = $this->getDetailRepository()->find($oldMainId);
                if ($oldMain instanceof Detail) {
                    $oldMain->setKind(2);
                    $variants[] = $oldMain;
                }
            }
        }

        $data['details'] = $variants;
        unset($data['variants']);

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     * @throws Exception
     *
     * @return array
     */
    protected function prepareConfiguratorSet($data, ProductModel $article)
    {
        if (!isset($data['configuratorSet'])) {
            return $data;
        }

        $configuratorSet = $article->getConfiguratorSet();
        if (!$configuratorSet) {
            $configuratorSet = new Configurator\Set();
            $number = $data['mainDetail']['number'] ?? $article->getMainDetail()->getNumber();

            $configuratorSet->setName('Set-' . $number);
            $configuratorSet->setPublic(false);
        }

        if (isset($data['configuratorSet']['type'])) {
            $configuratorSet->setType($data['configuratorSet']['type']);
        }

        if (isset($data['configuratorSet']['name'])) {
            $configuratorSet->setName($data['configuratorSet']['name']);
        }

        $allOptions = [];
        $allGroups = [];

        $groupPosition = 0;

        foreach ($data['configuratorSet']['groups'] as $groupData) {
            $group = null;
            if (isset($groupData['id'])) {
                $group = $this->getManager()->getRepository(ConfiguratorGroup::class)->find($groupData['id']);
                if (!$group) {
                    throw new CustomValidationException(sprintf('ConfiguratorGroup by id "%s" not found', $groupData['id']));
                }
            } elseif (isset($groupData['name'])) {
                $group = $this->getManager()->getRepository(ConfiguratorGroup::class)->findOneBy(['name' => $groupData['name']]);

                if (!$group) {
                    $group = new ConfiguratorGroup();
                    $group->setPosition($groupPosition);
                }
            } else {
                throw new CustomValidationException('At least the groupname is required');
            }

            $groupOptions = $group->getOptions();
            $optionPosition = 0;
            foreach ($groupData['options'] as $optionData) {
                $option = null;
                if ($group->getId() > 0) {
                    if (isset($optionData['id'])) {
                        $option = $this->getManager()->find(ConfiguratorOption::class, $optionData['id']);
                        if (!$option) {
                            throw new CustomValidationException(sprintf('ConfiguratorOption by id "%s" not found', $optionData['id']));
                        }
                    } else {
                        $option = $this->getManager()->getRepository(ConfiguratorOption::class)->findOneBy([
                            'name' => $optionData['name'],
                            'groupId' => $group->getId(),
                        ]);
                    }
                }

                if (!$option) {
                    $option = new ConfiguratorOption();
                }

                $option->fromArray($optionData);
                $option->setGroup($group);

                // Only set new position if option doesn't exist yet
                // Otherwise the position might have been set manually already, and we do not want to change that
                if (!isset($optionData['position']) && !$option->getId()) {
                    $option->setPosition($optionPosition++);
                }
                $allOptions[] = $option;
                $groupOptions[] = $option;
            }

            $groupData['options'] = $groupOptions;

            $group->fromArray($groupData);
            $allGroups[] = $group;
        }

        // Clear needed in order to allow updates on configuratorSet. When removed constraints in
        // s_article_configurator_set_group_relations and s_article_configurator_set_option_relations
        // might fail.
        $configuratorSet->getOptions()->clear();
        $configuratorSet->setOptions($allOptions);
        $configuratorSet->getGroups()->clear();
        $configuratorSet->setGroups($allGroups);

        $this->getManager()->persist($configuratorSet);

        $data['configuratorSet'] = $configuratorSet;

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareArticleAssociatedData($data, ProductModel $article)
    {
        // Check if a tax id is passed and load the tax model or set the tax parameter to null.
        if (!empty($data['taxId'])) {
            $data['tax'] = $this->getManager()->find(Tax::class, $data['taxId']);

            if (empty($data['tax'])) {
                throw new CustomValidationException(sprintf('Tax by id "%s" not found', $data['taxId']));
            }
        } elseif (isset($data['tax']) && ($data['tax'] >= 0)) {
            $tax = $this->getManager()->getRepository(Tax::class)->findOneBy(['tax' => $data['tax']]);
            if (!$tax) {
                throw new CustomValidationException(sprintf('Tax by taxrate "%s" not found', $data['tax']));
            }
            $data['tax'] = $tax;
        } else {
            unset($data['tax']);
        }

        // Check if a supplier id is passed and load the supplier model or set the supplier parameter to null.
        if (!empty($data['supplierId'])) {
            $data['supplier'] = $this->getManager()->find(Supplier::class, $data['supplierId']);
            if (empty($data['supplier'])) {
                throw new CustomValidationException(sprintf('Supplier by id "%s" not found', $data['supplierId']));
            }
        } elseif (!empty($data['supplier'])) {
            $supplier = $this->getManager()->getRepository(Supplier::class)->findOneBy(['name' => $data['supplier']]);
            if (!$supplier) {
                $supplier = new Supplier();
                $supplier->setName($data['supplier']);
            }
            $data['supplier'] = $supplier;
        } else {
            unset($data['supplier']);
        }

        // Check if a priceGroup id is passed and load the priceGroup model or set the priceGroup parameter to null.
        if (isset($data['priceGroupId'])) {
            if (empty($data['priceGroupId'])) {
                $data['priceGroupId'] = null;
            } else {
                $data['priceGroup'] = $this->getManager()->find(Group::class, $data['priceGroupId']);
                if (empty($data['priceGroup'])) {
                    throw new CustomValidationException(sprintf('Pricegroup by id "%s" not found', $data['priceGroupId']));
                }
            }
        } else {
            unset($data['priceGroup']);
        }

        // Check if a propertyGroup is passed and load the propertyGroup model or set the propertyGroup parameter to null.
        if (isset($data['filterGroupId'])) {
            if (empty($data['filterGroupId'])) {
                $data['propertyGroup'] = null;
            } else {
                $data['propertyGroup'] = $this->getManager()->find(PropertyGroup::class, $data['filterGroupId']);

                if (empty($data['propertyGroup'])) {
                    throw new CustomValidationException(sprintf('PropertyGroup by id "%s" not found', $data['filterGroupId']));
                }
            }
        } else {
            unset($data['propertyGroup']);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareAttributeAssociatedData($data, ProductModel $article)
    {
        if (isset($data['attribute']) && !isset($data['mainDetail']['attribute'])) {
            $data['mainDetail']['attribute'] = $data['attribute'];
        }
        unset($data['attribute']);
        if (isset($data['mainDetail']['attribute']['articleDetailId'])) {
            unset($data['mainDetail']['attribute']['articleDetailId']);
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareCategoryAssociatedData($data, ProductModel $article)
    {
        if (!isset($data['categories'])) {
            return $data;
        }

        $this->resetProductCategoryAssignment($data, $article);

        $categories = $article->getCategories();

        $categoryIds = $categories->map(function ($category) {
            return $category->getId();
        });

        $categoryIds = array_flip($categoryIds->toArray());

        foreach ($data['categories'] as $categoryData) {
            $category = $this->getManyToManySubElement(
                $categories,
                $categoryData,
                Category::class
            );

            if (!$category) {
                if (!empty($categoryData['path'])) {
                    $categoryResource = $this->getContainer()->get(CategoryResource::class);
                    $category = $categoryResource->findCategoryByPath($categoryData['path'], true);

                    if (!$category) {
                        throw new CustomValidationException(sprintf('Could not find or create category by path: %s.', $categoryData['path']));
                    }

                    if (isset($categoryIds[$category->getId()])) {
                        continue;
                    }

                    $categories->add($category);
                }
            } else {
                $categoryIds[$category->getId()] = 1;
            }
        }

        $data['categories'] = $categories;

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareSeoCategoryAssociatedData($data, ProductModel $article)
    {
        if (!isset($data['seoCategories'])) {
            return $data;
        }

        $seoCategories = $this->checkDataReplacement(
            $article->getSeoCategories(),
            $data,
            'seoCategories',
            true
        );
        /** @var ArrayCollection<array-key, Category> $categories */
        $categories = $data['categories'];

        foreach ($data['seoCategories'] as $categoryData) {
            $seoCategory = $this->getOneToManySubElement(
                $seoCategories,
                $categoryData,
                SeoCategory::class
            );

            if (isset($categoryData['shopId'])) {
                $shop = $this->manager->find(
                    Shop::class,
                    $categoryData['shopId']
                );

                if (!$shop instanceof Shop) {
                    throw new CustomValidationException(sprintf('Could not find shop by id: %s.', $categoryData['shopId']));
                }

                $seoCategory->setShop($shop);
            }

            if (!$seoCategory->getShop()) {
                throw new CustomValidationException(sprintf('A product seo category requires a configured shop'));
            }

            if (isset($categoryData['categoryId'])) {
                $category = $this->manager->find(Category::class, $categoryData['categoryId']);

                if (!$category instanceof Category) {
                    throw new CustomValidationException(sprintf('Could not find category by id: %s.', $categoryData['categoryId']));
                }

                $seoCategory->setCategory($category);
            } elseif (isset($categoryData['categoryPath'])) {
                $category = $this->getContainer()->get(CategoryResource::class)->findCategoryByPath(
                    $categoryData['categoryPath'],
                    true
                );
                if (!$category instanceof Category) {
                    throw new CustomValidationException(sprintf('Could not find category by path: %s.', $categoryData['categoryPath']));
                }
                $seoCategory->setCategory($category);
            }

            $existing = $this->getCollectionElementByProperty(
                $categories,
                'id',
                $seoCategory->getCategory()->getId()
            );

            if (!$existing instanceof Category) {
                throw new CustomValidationException(sprintf("Seo category isn't assigned as normal product category. Only assigned categories can be used as seo category"));
            }

            $seoCategory->setArticle($article);
        }

        $data['seoCategories'] = $seoCategories;

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareAvoidCustomerGroups($data, ProductModel $article)
    {
        if (!isset($data['customerGroups'])) {
            return $data;
        }

        $customerGroups = $this->checkDataReplacement($article->getCustomerGroups(), $data, 'customerGroups', true);

        foreach ($data['customerGroups'] as $customerGroupData) {
            $this->getManyToManySubElement(
                $customerGroups,
                $customerGroupData,
                CustomerGroup::class
            );
        }

        $data['customerGroups'] = $customerGroups;

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareRelatedAssociatedData($data, ProductModel $article)
    {
        if (!isset($data['related'])) {
            return $data;
        }

        $related = $this->checkDataReplacement($article->getRelated(), $data, 'related', true);

        foreach ($data['related'] as $relatedData) {
            if (empty($relatedData['number']) && empty($relatedData['id'])) {
                continue;
            }

            $relatedProduct = null;
            if ($relatedData['number']) {
                $productId = $this->getManager()->getConnection()->fetchOne(
                    'SELECT articleID FROM s_articles_details WHERE ordernumber = :number',
                    [':number' => $relatedData['number']]
                );

                if ($productId) {
                    $relatedProduct = $this->getManyToManySubElement(
                        $related,
                        ['id' => $productId],
                        ProductModel::class
                    );
                }
            }

            if ($relatedProduct === null) {
                $relatedProduct = $this->getManyToManySubElement(
                    $related,
                    $relatedData,
                    ProductModel::class
                );
            }

            // no valid entity found, throw exception!
            if ($relatedProduct === null) {
                $property = $relatedData['number'] ?: $relatedData['id'];
                throw new CustomValidationException(sprintf('Related product by number/id "%s" not found', $property));
            }

            if ($relatedData['cross']) {
                $relatedProduct->getRelated()->add($article);
            }
        }

        $data['related'] = $related;

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function prepareSimilarAssociatedData($data, ProductModel $article)
    {
        if (!isset($data['similar'])) {
            return $data;
        }

        $similar = $this->checkDataReplacement($article->getSimilar(), $data, 'similar', true);

        foreach ($data['similar'] as $similarData) {
            if (empty($similarData['number']) && empty($similarData['id'])) {
                continue;
            }

            $similarProduct = null;
            if ($similarData['number']) {
                $productId = $this->getManager()->getConnection()->fetchOne(
                    'SELECT articleID FROM s_articles_details WHERE ordernumber = :number',
                    [':number' => $similarData['number']]
                );

                if ($productId) {
                    $similarProduct = $this->getManyToManySubElement(
                        $similar,
                        ['id' => $productId],
                        ProductModel::class
                    );
                }
            }

            if ($similarProduct === null) {
                $similarProduct = $this->getManyToManySubElement(
                    $similar,
                    $similarData,
                    ProductModel::class
                );
            }

            // No valid entity found, throw exception!
            if ($similarProduct === null) {
                $property = $similarData['number'] ?: $similarData['id'];
                throw new CustomValidationException(sprintf('Similar product by number/id "%s" not found', $property));
            }

            if ($similarData['cross']) {
                $similarProduct->getSimilar()->add($article);
            }
        }

        $data['similar'] = $similar;

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws CustomValidationException
     *
     * @return array
     */
    protected function preparePropertyValuesData($data, ProductModel $article)
    {
        if (!isset($data['propertyValues'])) {
            return $data;
        }

        // Remove assigned values
        if (empty($data['propertyValues'])) {
            return $data;
        }

        $propertyRepository = $this->getManager()->getRepository(PropertyGroup::class);

        /*
         *  Get group - this is required.
         */
        $propertyGroup = $data['propertyGroup'] ?? $article->getPropertyGroup();

        if (!$propertyGroup instanceof PropertyGroup) {
            throw new CustomValidationException('There is no propertyGroup specified');
        }

        $models = [];

        foreach ($data['propertyValues'] as $valueData) {
            $value = null;
            $option = null;

            // Get value by id
            if (isset($valueData['id'])) {
                $value = $this->getManager()->getRepository(Value::class)->find($valueData['id']);
                if (!$value instanceof Value) {
                    throw new CustomValidationException(sprintf('Property value by id "%s" not found', $valueData['id']));
                }
            // Get / create value by name
            } elseif (isset($valueData['value'])) {
                // Get option
                if (isset($valueData['option'])) {
                    // Get option by id
                    if (isset($valueData['option']['id'])) {
                        $option = $this->getManager()->getRepository(Option::class)->find($valueData['option']['id']);
                        if (!$option instanceof Option) {
                            throw new CustomValidationException(sprintf('Property option by id "%s" not found', $valueData['option']['id']));
                        }
                        $filters = [
                            ['property' => 'options.id', 'expression' => '=', 'value' => $option->getId()],
                            ['property' => 'groups.id', 'expression' => '=', 'value' => $propertyGroup->getId()],
                        ];
                        $query = $propertyRepository->getPropertyRelationQuery($filters, null, 1, 0);
                        $relation = $query->getOneOrNullResult(self::HYDRATE_OBJECT);
                        if (!$relation instanceof Relation) {
                            $propertyGroup->addOption($option);
                        }
                    // Get/create option depending on associated filter groups
                    } elseif (isset($valueData['option']['name'])) {
                        // If a name is passed and there is a matching option/group relation, get this option
                        // If only a name is passed, create a new option
                        $filters = [
                            ['property' => 'options.name', 'expression' => '=', 'value' => $valueData['option']['name']],
                            ['property' => 'groups.name', 'expression' => '=', 'value' => $propertyGroup->getName()],
                        ];
                        $query = $propertyRepository->getPropertyRelationQuery($filters, null, 1, 0);
                        $relation = $query->getOneOrNullResult(self::HYDRATE_OBJECT);
                        if (!$relation instanceof Relation) {
                            // checks if a new option was created
                            // because the new option is not written to the database at this point
                            $groupOption = $this->getCollectionElementByProperty(
                                $propertyGroup->getOptions(),
                                'name',
                                $valueData['option']['name']
                            );
                            // Creates a new option
                            if ($groupOption === null) {
                                $option = new Option();
                                $propertyGroup->addOption($option);
                            } else {
                                $option = $groupOption;
                            }
                        } else {
                            $option = $relation->getOption();
                        }
                    } else {
                        throw new CustomValidationException('A property option needs to be given for each property value');
                    }
                    if (!$option instanceof Option) {
                        throw new RuntimeException('An option should be available at this point');
                    }
                    $option->fromArray($valueData['option']);
                    if (!\is_bool($option->isFilterable())) {
                        $option->setFilterable(false);
                    }
                } else {
                    throw new CustomValidationException('A property option needs to be given for each property value');
                }
                // Create the value
                // If there is a filter value with matching name and option, load this value, else create a new one
                $value = $this->getManager()->getRepository(Value::class)->findOneBy([
                    'value' => $valueData['value'],
                    'optionId' => $option->getId(),
                ]);
                if (!$value instanceof Value) {
                    $value = new Value($option, $valueData['value']);
                }
                if (isset($valueData['position'])) {
                    $value->setPosition($valueData['position']);
                }
                $this->getManager()->persist($value);
            } else {
                throw new CustomValidationException('Name or id for property value required');
            }
            $models[] = $value;
        }

        $data['propertyValues'] = $models;

        return $data;
    }

    /**
     * Returns a query builder to select all product images with mappings and rules.
     * Used to generate the variant images.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    protected function getArticleImageMappingsQuery($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['mappings', 'image', 'rules'])
            ->from(Image\Mapping::class, 'mappings')
            ->innerJoin('mappings.image', 'image')
            ->innerJoin('mappings.rules', 'rules')
            ->where('image.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * Checks if the passed product image is already created
     * as variant image.
     *
     * @return bool
     */
    protected function isVariantImageExist(Detail $variant, Image $image)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        foreach ($variant->getImages() as $variantImage) {
            if ((int) $variantImage->getParent()->getId() === (int) $image->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Small helper function which creates a query builder to select
     * all product variants.
     *
     * @param int $id
     *
     * @return QueryBuilder
     */
    protected function getArticleVariantQuery($id)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select('variants');
        $builder->from(Detail::class, 'variants')
            ->where('variants.articleId = :articleId')
            ->setParameter('articleId', $id);

        return $builder;
    }

    /**
     * Creates the product image mappings for the passed product and image entity.
     * The "mappings" parameter contains a multidimensional array with configurator options.
     * The first level of the mappings defines the OR conditions of the image mappings.
     * The second level of the mappings defines a single rule.
     * Example:
     * $mappings = array(
     *    array(
     *       array('name' => 'red')
     *       AND
     *       array('name' => 'small')
     *    )
     *    //OR
     *    array('name' => 'blue')
     * )
     *
     * @throws CustomValidationException
     */
    protected function createImageMappings(Image $image, ProductModel $article, array $mappings)
    {
        if (!$article->getConfiguratorSet()) {
            throw new CustomValidationException('Product is no configurator product. Image mapping can only be created on configurator products');
        }

        $configuratorOptions = $article->getConfiguratorSet()->getOptions();

        foreach ($mappings as $mappingData) {
            $options = new ArrayCollection();

            foreach ($mappingData as $option) {
                $conditions = [];

                if (isset($option['id']) && $option['id']) {
                    $conditions['id'] = $option['id'];
                }

                $conditions['name'] = $option['name'];

                $available = $this->getCollectionElementByProperties($configuratorOptions, $conditions);

                if (!$available) {
                    $property = $option['id'] ?: $option['name'];
                    throw new CustomValidationException(sprintf('Passed option "%s" does not exist in the configurator set of the product', $property));
                }

                $options->add($available);
            }

            if ($options->count() === 0) {
                throw new CustomValidationException('No available option exists');
            }

            $this->getVariantResource()->createImageMappingForOptions(
                $options,
                $image
            );
        }
    }

    /**
     * Translate the whole product array.
     *
     * @return array
     */
    protected function translateArticle(array $data, Shop $shop)
    {
        $this->getTranslationResource()->setResultMode(
            self::HYDRATE_ARRAY
        );
        $translation = $this->getSingleTranslation(
            'article',
            $shop->getId(),
            $data['id']
        );

        if (!empty($translation)) {
            $data = $this->mergeTranslation($data, $translation['data']);

            if ($data['mainDetail']) {
                $data['mainDetail'] = $this->mergeTranslation($data['mainDetail'], $translation['data']);

                if ($data['mainDetail']['attribute']) {
                    $data['mainDetail']['attribute'] = $this->mergeTranslation(
                        $data['mainDetail']['attribute'],
                        $translation['data']
                    );
                }

                if ($data['mainDetail']['configuratorOptions']) {
                    $data['mainDetail']['configuratorOptions'] = $this->translateAssociation(
                        $data['mainDetail']['configuratorOptions'],
                        $shop,
                        'configuratoroption'
                    );
                }
            }
        }

        $data['details'] = $this->translateVariants(
            $data['details'],
            $shop
        );

        if (isset($data['links'])) {
            $data['links'] = $this->translateAssociation(
                $data['links'],
                $shop,
                'link'
            );
        }

        if (isset($data['downloads'])) {
            $data['downloads'] = $this->translateAssociation(
                $data['downloads'],
                $shop,
                'download'
            );
        }

        $data['supplier'] = $this->translateSupplier($data['supplier'], $shop);

        $data['propertyValues'] = $this->translatePropertyValues($data['propertyValues'], $shop);

        $data['propertyGroup'] = $this->translatePropertyGroup($data['propertyGroup'], $shop);

        if (!empty($data['configuratorSet']) && !empty($data['configuratorSet']['groups'])) {
            $data['configuratorSet']['groups'] = $this->translateAssociation(
                $data['configuratorSet']['groups'],
                $shop,
                'configuratorgroup'
            );
        }

        if (isset($data['related'])) {
            $data['related'] = $this->translateAssociation(
                $data['related'],
                $shop,
                'article'
            );
        }

        if (isset($data['similar'])) {
            $data['similar'] = $this->translateAssociation(
                $data['similar'],
                $shop,
                'article'
            );
        }

        if (isset($data['images'])) {
            $data['images'] = $this->translateAssociation(
                $data['images'],
                $shop,
                'articleimage'
            );
        }

        return $data;
    }

    /**
     * Translates the passed values array with the passed shop entity.
     *
     * @param array $values
     */
    protected function translatePropertyValues($values, Shop $shop)
    {
        if (empty($values)) {
            return $values;
        }

        foreach ($values as &$value) {
            $translation = $this->getSingleTranslation(
                'propertyvalue',
                $shop->getId(),
                $value['id']
            );
            if (empty($translation)) {
                continue;
            }

            $translation['data']['value'] = $translation['data']['optionValue'];

            $value = $this->mergeTranslation(
                $value,
                $translation['data']
            );
        }

        return $values;
    }

    /**
     * Translates the passed supplier data.
     *
     * @param array $supplier
     *
     * @return array
     */
    protected function translateSupplier($supplier, Shop $shop)
    {
        if (empty($supplier)) {
            return $supplier;
        }
        $translation = $this->getSingleTranslation(
            'supplier',
            $shop->getId(),
            $supplier['id']
        );

        if (empty($translation)) {
            return $supplier;
        }

        return $this->mergeTranslation(
            $supplier,
            $translation['data']
        );
    }

    /**
     * Translates the passed property group data.
     *
     * @param array $groupData
     *
     * @return array
     */
    protected function translatePropertyGroup($groupData, Shop $shop)
    {
        if (empty($groupData)) {
            return $groupData;
        }

        $translation = $this->getSingleTranslation(
            'propertygroup',
            $shop->getId(),
            $groupData['id']
        );

        if (empty($translation)) {
            return $groupData;
        }

        $translation['data']['name'] = $translation['data']['groupName'];

        return $this->mergeTranslation(
            $groupData,
            $translation['data']
        );
    }

    /**
     * Translates the passed variants array and all associated data.
     *
     * @param array $details
     */
    protected function translateVariants($details, Shop $shop)
    {
        if (empty($details)) {
            return $details;
        }

        foreach ($details as &$variant) {
            $translation = $this->getSingleTranslation(
                'variant',
                $shop->getId(),
                $variant['id']
            );
            if (empty($translation)) {
                continue;
            }
            $variant = $this->mergeTranslation(
                $variant,
                $translation['data']
            );
            $variant['attribute'] = $this->mergeTranslation(
                $variant['attribute'],
                $translation['data']
            );

            if ($variant['configuratorOptions']) {
                $variant['configuratorOptions'] = $this->translateAssociation(
                    $variant['configuratorOptions'],
                    $shop,
                    'configuratoroption'
                );
            }

            if ($variant['images']) {
                foreach ($variant['images'] as &$image) {
                    $translation = $this->getSingleTranslation(
                        'articleimage',
                        $shop->getId(),
                        $image['parentId']
                    );
                    if (empty($translation)) {
                        continue;
                    }
                    $image = $this->mergeTranslation($image, $translation['data']);
                }
            }
        }

        return $details;
    }

    /**
     * Helper function which merges the translated data into the already
     * existing data object. This function merges only values, which already
     * exist in the original data array.
     *
     * @param array $data
     * @param array $translation
     *
     * @return array
     */
    protected function mergeTranslation($data, $translation)
    {
        return array_merge(
            $data,
            array_intersect_key($translation, $data)
        );
    }

    /**
     * Helper function which translates associated array data.
     *
     * @param string $type
     *
     * @return array
     */
    protected function translateAssociation(array $association, Shop $shop, $type)
    {
        foreach ($association as &$item) {
            $translation = $this->getSingleTranslation(
                $type,
                $shop->getId(),
                $item['id']
            );
            if (empty($translation)) {
                continue;
            }
            $item = $this->mergeTranslation($item, $translation['data']);
        }

        return $association;
    }

    /**
     * Helper function to get a single translation.
     *
     * @param string $type
     * @param int    $shopId
     * @param string $key
     *
     * @return array
     */
    protected function getSingleTranslation($type, $shopId, $key)
    {
        $translation = $this->getTranslationResource()->getList(0, 1, [
            ['property' => 'translation.type', 'value' => $type],
            ['property' => 'translation.key', 'value' => $key],
            ['property' => 'translation.shopId', 'value' => $shopId],
        ]);

        return $translation['data'][0];
    }

    /**
     * Helper function to prevent duplicate source code
     * to get a single row of the query builder result for the current resource result mode
     * using the query paginator.
     *
     * @return array
     */
    private function getSingleResult(QueryBuilder $builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        return $this->getManager()->createPaginator($query)->getIterator()->current();
    }

    /**
     * Helper function to prevent duplicate source code
     * to get the full query builder result for the current resource result mode
     * using the query paginator.
     */
    private function getFullResult(QueryBuilder $builder): array
    {
        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        return $this->getManager()->createPaginator($query)->getIterator()->getArrayCopy();
    }

    /**
     * Helper function for the category assignment.
     * This function is used for the category configuration.
     * If the data key __options_categories => replace is set to true,
     * the function removes the assigned product categories from the
     * s_articles_categories and s_articles_categories_ro table.
     */
    private function resetProductCategoryAssignment(array $data, ProductModel $product): void
    {
        if (!$product->getId()) {
            return;
        }

        $key = '__options_categories';

        // Replacement deactivated?
        if (isset($data[$key]) && (bool) $data[$key]['replace'] === false) {
            return;
        }

        $connection = $this->manager->getConnection();
        $connection->executeStatement(
            'DELETE FROM s_articles_categories WHERE articleID = :articleId',
            [':articleId' => $product->getId()]
        );

        $connection->executeStatement(
            'DELETE FROM s_articles_categories_ro WHERE articleID = :articleId',
            [':articleId' => $product->getId()]
        );
    }

    /**
     * @throws CustomValidationException
     */
    private function prepareDownloadsAssociatedData(array $data, ProductModel $product): array
    {
        if (!isset($data['downloads'])) {
            return $data;
        }

        $downloads = $this->checkDataReplacement($product->getDownloads(), $data, 'downloads', true);

        foreach ($data['downloads'] as $downloadData) {
            $download = $this->getOneToManySubElement(
                $downloads,
                $downloadData,
                Download::class
            );

            if (isset($downloadData['link'])) {
                $media = $this->getMediaResource()->internalCreateMediaByFileLink(
                    $downloadData['link'],
                    -6
                );
                if (isset($downloadData['name']) && !empty($downloadData['name'])) {
                    $media->setDescription($downloadData['name']);
                }

                try { // persist the model into the model manager
                    $this->getManager()->persist($media);
                } catch (ORMException $e) {
                    throw new CustomValidationException(sprintf('Some error occurred while loading your image from link "%s"', $downloadData['link']));
                }

                $download->setFile($media->getPath());
                $download->setName($media->getName());
            }

            $download->fromArray($downloadData);
            $download->setArticle($product);
        }
        $data['downloads'] = $downloads;

        return $data;
    }

    /**
     * Resolves the passed images data to valid Shopware\Models\Article\Image
     * entities.
     *
     * @throws CustomValidationException
     */
    private function prepareImageAssociatedData(array $data, ProductModel $product): array
    {
        if (!isset($data['images'])) {
            return $data;
        }

        // remove assigned images
        if (empty($data['images'])) {
            $images = $product->getImages();
            $images->clear();
            unset($data['images']);

            return $data;
        }

        $position = 1;
        $images = $this->checkDataReplacement($product->getImages(), $data, 'images', false);

        foreach ($data['images'] as $imageData) {
            $image = $this->getOneToManySubElement(
                $images,
                $imageData,
                Image::class
            );

            if (isset($imageData['link'])) {
                $createImageData = [$imageData['link']];

                if (isset($imageData['albumId'])) {
                    $createImageData[] = $imageData['albumId'];
                }

                $media = $this->getMediaResource()->internalCreateMediaByFileLink(
                    ...$createImageData
                );

                $image = $this->updateArticleImageWithMedia(
                    $product,
                    $image,
                    $media
                );

                $image->setPosition($position);
                ++$position;
            } elseif (!empty($imageData['mediaId'])) {
                $media = $this->getManager()->find(
                    MediaModel::class,
                    (int) $imageData['mediaId']
                );

                if (!($media instanceof MediaModel)) {
                    throw new CustomValidationException(sprintf('Media by mediaId "%s" not found', $imageData['mediaId']));
                }

                $image = $this->updateArticleImageWithMedia(
                    $product,
                    $image,
                    $media
                );

                $image->setPosition($position);
                ++$position;
            }

            $image->fromArray($imageData);

            // if image is set as main set other images to secondary
            if ((int) $image->getMain() === 1) {
                foreach ($images as $otherImage) {
                    // Only update existing images which are not the current processed image.
                    // Otherwise, the main flag won't be changed.
                    if ($otherImage->getId() !== $image->getId()) {
                        $otherImage->setMain(2);
                    }
                }
            }

            if (isset($imageData['options'])) {
                $this->createImageMappings($image, $product, $imageData['options']);
            }
        }

        $mainImage = $this->getCollectionElementByProperty(
            $images,
            'main',
            1
        );

        if (!$mainImage instanceof Image) {
            $image = $images->get(0);
            if ($image instanceof Image) {
                $image->setMain(1);
            }
        }
        unset($data['images']);

        return $data;
    }

    /**
     * Returns all none association property of the product class.
     */
    private function getAttributeProperties(): array
    {
        $attributeNames = $this->getContainer()->get(CrudServiceInterface::class)->getList('s_articles_attributes');
        $fields = [];
        foreach ($attributeNames as $property) {
            $fields[] = '__attribute_' . $property->getColumnName();
        }

        return $fields;
    }
}
