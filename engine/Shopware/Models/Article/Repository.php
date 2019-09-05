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

namespace Shopware\Models\Article;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Expr\OrderBy;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Configurator\Dependency as ConfiguratorDependency;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Configurator\PriceVariation as ConfiguratorPriceVariation;
use Shopware\Models\Article\Configurator\Set as ConfiguratorSet;
use Shopware\Models\Article\Configurator\Template\Template;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Attribute\ArticleDownload as ProductDownloadAttribute;
use Shopware\Models\Attribute\ArticleImage as ProductImageAttribute;
use Shopware\Models\Attribute\ArticleLink as ProductLinkAttribute;
use Shopware\Models\Attribute\ArticlePrice as ProductPriceAttribute;
use Shopware\Models\Price\Group as PriceGroup;
use Shopware\Models\Property\Group as PropertyGroup;
use Shopware\Models\Tax\Tax;
use Shopware\Models\Translation\Translation;

class Repository extends ModelRepository
{
    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleBaseDataQuery($articleId)
    {
        return $this->getArticleBaseDataQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleBaseDataQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'mainDetail', 'tax', 'attribute']);
        $builder->from(Product::class, 'article')
                ->leftJoin('article.mainDetail', 'mainDetail')
                ->leftJoin('article.tax', 'tax')
                ->leftJoin('mainDetail.attribute', 'attribute')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleCategoriesQuery($articleId)
    {
        return $this->getArticleCategoriesQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleCategoriesQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'categories'])
                ->from(Product::class, 'article')
                ->leftJoin('article.categories', 'categories', null, null, 'categories.id')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleSimilarsQuery($articleId)
    {
        return $this->getArticleSimilarsQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleSimilarsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'similar', 'similarDetail'])
                ->from(Product::class, 'article')
                ->leftJoin('article.similar', 'similar')
                ->leftJoin('similar.mainDetail', 'similarDetail')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleRelatedProductStreamsQuery($articleId)
    {
        return $this->getArticleRelatedProductStreamsQueryBuilder($articleId)->getQuery();
    }

    /**
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleRelatedProductStreamsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article.id', 'relatedProductStreams.id', 'relatedProductStreams.name', 'relatedProductStreams.description'])
                ->from(Product::class, 'article')
                ->leftJoin('article.relatedProductStreams', 'relatedProductStreams')
                ->where('article.id = :articleId')
                ->andWhere('relatedProductStreams.id IS NOT NULL')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleRelatedQuery($articleId)
    {
        return $this->getArticleRelatedQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleRelatedQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'accessories', 'accessoryDetail'])
                ->from(Product::class, 'article')
                ->leftJoin('article.related', 'accessories')
                ->leftJoin('accessories.mainDetail', 'accessoryDetail')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleWithImagesQuery($articleId)
    {
        return $this->getArticleWithImagesQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleWithImagesQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'images', 'imageAttribute', 'imageMapping', 'mappingRule', 'ruleOption'])
                ->from(Product::class, 'article')
                ->leftJoin('article.images', 'images')
                ->leftJoin('images.attribute', 'imageAttribute')
                ->leftJoin('images.mappings', 'imageMapping')
                ->leftJoin('imageMapping.rules', 'mappingRule')
                ->leftJoin('mappingRule.option', 'ruleOption')
                ->where('article.id = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleLinksQuery($articleId)
    {
        return $this->getArticleLinksQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleLinksQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'links', 'linkAttribute'])
                ->from(Product::class, 'article')
                ->leftJoin('article.links', 'links')
                ->leftJoin('links.attribute', 'linkAttribute')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleDownloadsQuery($articleId)
    {
        return $this->getArticleDownloadsQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleDownloadsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'downloads', 'downloadAttribute'])
                ->from(Product::class, 'article')
                ->leftJoin('article.downloads', 'downloads')
                ->leftJoin('downloads.attribute', 'downloadAttribute')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleCustomerGroupsQuery($articleId)
    {
        return $this->getArticleCustomerGroupsQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleCustomerGroupsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'customerGroups'])
                ->from(Product::class, 'article')
                ->leftJoin('article.customerGroups', 'customerGroups')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleConfiguratorSetQuery($articleId)
    {
        return $this->getArticleConfiguratorSetQueryBuilder($articleId)->getQuery();
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleConfiguratorSetQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'configuratorSet', 'groups', 'options'])
                ->from(Product::class, 'article')
                ->innerJoin('article.configuratorSet', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC')
                ->where('article.id = :articleId')
                ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects all data about a single article.
     * The query selects the article, main detail of the article, assigned categories, assigned similar and related articles,
     * links and downloads of the article, selected tax, associated article images and the attributes for the different models.
     * The query is used for the article detail page of the article backend module to load the article data into the view.
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleQuery($articleId)
    {
        return $this->getArticleQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'article',
            'mainDetail',
            'tax',
            'categories',
            'similar',
            'accessories',
            'accessoryDetail',
            'similarDetail',
            'images',
            'links',
            'downloads',
            'linkAttribute',
            'customerGroups',
            'imageAttribute',
            'downloadAttribute',
            'propertyValues',
            'imageMapping',
            'mappingRule',
            'ruleOption',
        ])
            ->from(Product::class, 'article')
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('article.categories', 'categories', null, null, 'categories.id')
            ->leftJoin('article.similar', 'similar')
            ->leftJoin('article.related', 'accessories')
            ->leftJoin('accessories.mainDetail', 'accessoryDetail')
            ->leftJoin('similar.mainDetail', 'similarDetail')
            ->leftJoin('article.images', 'images')
            ->leftJoin('article.links', 'links')
            ->leftJoin('article.downloads', 'downloads')
            ->leftJoin('article.tax', 'tax')
            ->leftJoin('links.attribute', 'linkAttribute')
            ->leftJoin('article.customerGroups', 'customerGroups')
            ->leftJoin('images.attribute', 'imageAttribute')
            ->leftJoin('downloads.attribute', 'downloadAttribute')
            ->leftJoin('article.propertyValues', 'propertyValues')
            ->leftJoin('images.mappings', 'imageMapping')
            ->leftJoin('imageMapping.rules', 'mappingRule')
            ->leftJoin('mappingRule.option', 'ruleOption')
            ->where('article.id = ?1')
            ->andWhere('images.parentId IS NULL')
            ->setParameter(1, $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int   $articleId
     * @param array $options
     *
     * @return Query
     */
    public function getDetailsForOptionIdsQuery($articleId, $options)
    {
        return $this->getDetailsForOptionIdsQueryBuilder($articleId, $options)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailsForOptionIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int   $articleId
     * @param array $options
     *
     * @return QueryBuilder
     */
    public function getDetailsForOptionIdsQueryBuilder($articleId, $options)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details'])
                ->from(Detail::class, 'details')
                ->where('details.articleId = :articleId')
                ->setParameter('articleId', $articleId);

        foreach ($options as $key => $option) {
            $alias = 'o' . (int) $key;
            $builder->innerJoin('details.configuratorOptions', $alias, Join::WITH, $alias . '.id = :' . $alias);

            // in some cases the options parameter can contains an array of option models,
            // an two dimensional array with option data, or an one dimensional array with ids.
            if ($option instanceof ConfiguratorOption) {
                $builder->setParameter($alias, $option->getId());
            } elseif (is_array($option) && !empty($option['id'])) {
                $builder->setParameter($alias, $option['id']);
            } else {
                $builder->setParameter($alias, $option);
            }
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $imageId
     *
     * @return Query
     */
    public function getArticleImageDataQuery($imageId)
    {
        return $this->getArticleImageDataQueryBuilder($imageId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImageDataQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $imageId
     *
     * @return QueryBuilder
     */
    public function getArticleImageDataQueryBuilder($imageId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['image'])
                ->from(Image::class, 'image')
                ->where('image.id = ?1')
                ->setParameter(1, $imageId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $imageId
     *
     * @return Query
     */
    public function getDeleteImageChildrenQuery($imageId)
    {
        return $this->getDeleteImageChildrenQueryBuilder($imageId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDeleteImageChildrenQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $imageId
     *
     * @return QueryBuilder
     */
    public function getDeleteImageChildrenQueryBuilder($imageId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Image::class, 'images')
                ->andWhere('images.parentId = ?1')
                ->setParameter(1, $imageId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $imageId
     *
     * @return Query
     */
    public function getArticleImageQuery($imageId)
    {
        return $this->getArticleImageQueryBuilder($imageId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $imageId
     *
     * @return QueryBuilder
     */
    public function getArticleImageQueryBuilder($imageId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['image', 'mappings', 'rules', 'option'])
                ->from(Image::class, 'image')
                ->leftJoin('image.mappings', 'mappings')
                ->leftJoin('mappings.rules', 'rules')
                ->leftJoin('rules.option', 'option')
                ->where('mappings.imageId = ?1')
                ->setParameter(1, $imageId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the defined configurator set
     * with the groups and options for the passed article id.
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleConfiguratorSetByArticleIdQuery($articleId)
    {
        return $this->getArticleConfiguratorSetByArticleIdQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleConfiguratorSetByArticleIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleConfiguratorSetByArticleIdQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['PARTIAL article.{id}', 'configuratorSet', 'groups', 'options'])
                ->from(Product::class, 'article')
                ->innerJoin('article.configuratorSet', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('article.id = ?1')
                ->setParameter(1, $articleId)
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleConfiguratorSetByArticleIdIndexedByIdsQuery($articleId)
    {
        $builder = $this->getArticleConfiguratorSetByArticleIdIndexedByIdsQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleConfiguratorSetByArticleIdIndexedByIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleConfiguratorSetByArticleIdIndexedByIdsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'configuratorSet', 'groups', 'options'])
                ->from(Product::class, 'article')
                ->innerJoin('article.configuratorSet', 'configuratorSet')
                ->innerJoin('configuratorSet.groups', 'groups', null, null, 'groups.id')
                ->innerJoin('configuratorSet.options', 'options')
                ->where('article.id = ?1')
                ->setParameter(1, $articleId)
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('options.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     * @param int $optionId
     *
     * @return Query
     */
    public function getArticleDetailByConfiguratorOptionIdQuery($articleId, $optionId)
    {
        $builder = $this->getArticleDetailByConfiguratorOptionIdQueryBuilder($articleId, $optionId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleDetailByConfiguratorOptionIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     * @param int $optionId
     *
     * @return QueryBuilder
     */
    public function getArticleDetailByConfiguratorOptionIdQueryBuilder($articleId, $optionId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'prices'])
                ->from(Detail::class, 'details')
                ->innerJoin('details.prices', 'prices')
                ->innerJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('details.configuratorOptions', 'configuratorOptions')
                ->innerJoin('configuratorOptions.group', 'groups')
                ->where('details.articleId = ?1')
                ->andWhere('configuratorOptions.id = ?2')
                ->setParameter(1, $articleId)
                ->setParameter(2, $optionId)
                ->orderBy('details.kind', 'ASC')
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('configuratorOptions.position', 'ASC')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int[] $optionsIds
     *
     * @return Query
     */
    public function getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQuery($optionsIds)
    {
        $builder = $this->getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQueryBuilder($optionsIds);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[] $optionsIds
     *
     * @return QueryBuilder
     */
    public function getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQueryBuilder($optionsIds)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups', 'options'])
                ->from(ConfiguratorGroup::class, 'groups')
                ->innerJoin('groups.options', 'options', Join::WITH, 'options.id IN (?1)', 'options.id')
                ->setParameter(1, $optionsIds);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int     $articleId
     * @param int     $firstOptionId
     * @param int     $secondOptionId
     * @param Article $article
     * @param string  $customerGroupKey
     *
     * @return Query
     */
    public function getArticleDetailForTableConfiguratorOptionCombinationQuery(
        $articleId,
        $firstOptionId,
        $secondOptionId,
        $article,
        $customerGroupKey
    ) {
        $builder = $this->getArticleDetailForTableConfiguratorOptionCombinationQueryBuilder(
            $articleId,
            $firstOptionId,
            $secondOptionId,
            $article,
            $customerGroupKey
        );

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleDetailForTableConfiguratorOptionCombinationQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int           $articleId
     * @param int           $firstOptionId
     * @param int           $secondOptionId
     * @param array|Article $article
     * @param string        $customerGroupKey
     *
     * @return QueryBuilder
     */
    public function getArticleDetailForTableConfiguratorOptionCombinationQueryBuilder(
        $articleId,
        $firstOptionId,
        $secondOptionId,
        $article,
        $customerGroupKey
    ) {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'prices'])
                ->from(Detail::class, 'details')
                ->leftJoin('details.prices', 'prices', 'WITH', 'prices.customerGroupKey = :key')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('details.configuratorOptions', 'options1')
                ->innerJoin('details.configuratorOptions', 'options2')
                ->where('details.articleId = ?1')
                ->andWhere('details.active = 1')
                ->andWhere('options1.id = ?2')
                ->andWhere('options2.id = ?3')
                ->setParameter('key', $customerGroupKey)
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC')
                ->setParameter(1, $articleId)
                ->setParameter(2, $firstOptionId)
                ->setParameter(3, $secondOptionId);

        if ($article instanceof Article && $article->getLastStock()) {
            $builder->andWhere('details.inStock > 0');
        } elseif (is_array($article) && $article['lastStock']) {
            $builder->andWhere('details.inStock > 0');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleWithVariantsAndOptionsQuery($articleId)
    {
        return $this->getArticleWithVariantsAndOptionsQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleWithVariantsAndOptionsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleWithVariantsAndOptionsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'details', 'options'])
                ->from(Product::class, 'article')
                ->leftJoin('article.details', 'details')
                ->leftJoin('details.configuratorOptions', 'options')
                ->where('article.id = ?1')
                ->setParameter(1, $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int           $articleId
     * @param string        $customerGroupKey
     * @param array|Article $article
     *
     * @return Query
     */
    public function getConfiguratorTablePreSelectionItemQuery($articleId, $customerGroupKey, $article)
    {
        $builder = $this->getConfiguratorTablePreSelectionItemQueryBuilder($articleId, $customerGroupKey, $article);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorTablePreSelectionQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int           $articleId
     * @param string        $customerGroupKey
     * @param array|Article $article
     *
     * @return QueryBuilder
     */
    public function getConfiguratorTablePreSelectionItemQueryBuilder($articleId, $customerGroupKey, $article)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'prices', 'options'])
                ->from(Detail::class, 'details')
                ->leftJoin('details.prices', 'prices', 'WITH', 'prices.customerGroupKey = :key')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->innerJoin('details.configuratorOptions', 'options', null, null, 'options.groupId')
                ->where('details.articleId = ?1')
                ->addOrderBy('details.kind', 'ASC')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC')
                ->setParameter('key', $customerGroupKey)
                ->setParameter(1, $articleId);

        if ($article instanceof Article && $article->getLastStock()) {
            $builder->andWhere('details.inStock > 0');
        } elseif (is_array($article) && $article['lastStock']) {
            $builder->andWhere('details.inStock > 0');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int[] $ids
     *
     * @return Query
     */
    public function getConfiguratorSetsWithExcludedIdsQuery($ids)
    {
        return $this->getConfiguratorSetsWithExcludedIdsQueryBuilder($ids)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorSetsWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[] $ids
     *
     * @return QueryBuilder
     */
    public function getConfiguratorSetsWithExcludedIdsQueryBuilder($ids)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups', 'options'])
                ->from(ConfiguratorSet::class, 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('configuratorSet.public = ?1')
                ->setParameter(1, 1)
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');

        if (!empty($ids)) {
            if (!is_array($ids)) {
                $ids = [$ids];
            }

            $builder->andWhere('configuratorSet.id NOT IN (:ids)')
                ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $configuratorSetId
     *
     * @return Query
     */
    public function getConfiguratorSetQuery($configuratorSetId)
    {
        return $this->getConfiguratorSetQueryBuilder($configuratorSetId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorSetQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $configuratorSetId
     *
     * @return QueryBuilder
     */
    public function getConfiguratorSetQueryBuilder($configuratorSetId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups', 'options'])
                ->from(ConfiguratorSet::class, 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('configuratorSet.id = ?1')
                ->setParameter(1, $configuratorSetId)
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $configuratorSetId
     *
     * @return Query
     */
    public function getConfiguratorDependenciesQuery($configuratorSetId)
    {
        return $this->getConfiguratorDependenciesQueryBuilder($configuratorSetId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorDependenciesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $configuratorSetId
     *
     * @return QueryBuilder
     */
    public function getConfiguratorDependenciesQueryBuilder($configuratorSetId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['dependencies', 'dependencyParent', 'dependencyChild'])
                ->from(ConfiguratorDependency::class, 'dependencies')
                ->leftJoin('dependencies.parentOption', 'dependencyParent')
                ->leftJoin('dependencies.childOption', 'dependencyChild')
                ->where('dependencies.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $configuratorSetId
     *
     * @return Query
     */
    public function getConfiguratorDependenciesIndexedByParentIdQuery($configuratorSetId)
    {
        $builder = $this->getConfiguratorDependenciesIndexedByParentIdQueryBuilder($configuratorSetId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorDependenciesIndexedByParentIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $configuratorSetId
     *
     * @return QueryBuilder
     */
    public function getConfiguratorDependenciesIndexedByParentIdQueryBuilder($configuratorSetId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['dependencies'])
                ->from(ConfiguratorDependency::class, 'dependencies', 'dependencies.parentId')
                ->where('dependencies.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId)
                ->orderBy('dependencies.parentId', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $configuratorSetId
     *
     * @return Query
     */
    public function getConfiguratorPriceVariationsQuery($configuratorSetId)
    {
        return $this->getConfiguratorPriceVariationsQueryBuilder($configuratorSetId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorPriceVariationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $configuratorSetId
     *
     * @return QueryBuilder
     */
    public function getConfiguratorPriceVariationsQueryBuilder($configuratorSetId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['priceVariations'])
                ->from(ConfiguratorPriceVariation::class, 'priceVariations')
                ->where('priceVariations.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId);

        return $builder;
    }

    /**
     * Internal helper function to optimize the performance of the "getArticleConfiguratorSetQueryBuilder"  function.
     * Without this function, the getArticleConfiguratorSetQueryBuilder needs to join the s_articles_details
     * to filter the articleId.
     *
     * @param int $articleId
     *
     * @return array
     */
    public function getArticleConfiguratorSetOptionIds($articleId)
    {
        $ids = $this->getEntityManager()->createQueryBuilder()->select('DISTINCT options.id')
                ->from(Detail::class, 'detail')
                ->innerJoin('detail.configuratorOptions', 'options')
                ->where('detail.articleId = ?1')
                ->setParameter(1, $articleId)
                ->getQuery()->getArrayResult();

        $optionIds = [];
        foreach ($ids as $id) {
            $optionIds[] = $id['id'];
        }

        return $optionIds;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all defined configurator groups.
     * Used for the backend module to display all groups for the article, even the inactive groups.
     *
     * @return Query
     */
    public function getConfiguratorGroupsQuery()
    {
        return $this->getConfiguratorGroupsQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getConfiguratorGroupsQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups', 'options'])
                ->from(ConfiguratorGroup::class, 'groups')
                ->leftJoin('groups.options', 'options')
                ->orderBy('groups.position');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getFirstArticleDetailWithKindTwoQuery($articleId)
    {
        return $this->getFirstArticleDetailWithKindTwoQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFirstArticleDetailWithKindTwoQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getFirstArticleDetailWithKindTwoQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details'])
                ->from(Detail::class, 'details')
                ->where('details.kind = 2')
                ->andWhere('details.articleId = ?1')
                ->setParameter(1, $articleId)
                ->setFirstResult(0)
                ->setMaxResults(1);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object that returns a list
     * of configurator options
     *
     * @param array|null $filter
     *
     * @return Query
     */
    public function getAllConfiguratorOptionsIndexedByIdQuery($filter = null)
    {
        $builder = $this->getAllConfiguratorOptionsIndexedByIdQueryBuilder();

        if ($filter) {
            $builder->addFilter($filter);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAllConfiguratorOptionsIndexedByIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getAllConfiguratorOptionsIndexedByIdQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['options'])
                ->from(ConfiguratorOption::class, 'options', 'options.id');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the id for the listing query
     * of the configurator. To display the options in the listing the configurator listing needs
     * an id query to allow an store paging.
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getConfiguratorListIdsQuery($articleId, $filter = null, $sort = null, $offset = null, $limit = null)
    {
        $builder = $this->getConfiguratorListIdsQueryBuilder($articleId, $filter, $sort);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorListIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int                 $articleId
     * @param array|null          $filter
     * @param string|OrderBy|null $sort
     *
     * @return QueryBuilder
     */
    public function getConfiguratorListIdsQueryBuilder($articleId, $filter = null, $sort = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('details.id')
                ->from(Detail::class, 'details')
                ->where('details.articleId = ?1')
                ->setParameter(1, $articleId);

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'details.number LIKE ?2',
                'configuratorOptions.name LIKE ?2'
            ));
            $builder->setParameter(2, '%' . $filter[0]['value'] . '%');
            $builder->leftJoin('details.configuratorOptions', 'configuratorOptions');
        }

        if ($sort !== null && !empty($sort)) {
            $builder->addOrderBy($sort);
        } else {
            $builder->addOrderBy('details.id', 'ASC');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the article details
     *
     * @param int[] $ids
     *
     * @return Query
     */
    public function getDetailsByIdsQuery($ids, $sort = null)
    {
        return $this->getDetailsByIdsQueryBuilder($ids, $sort)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailsByArticleIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]               $ids
     * @param string|OrderBy|null $sort
     *
     * @return QueryBuilder
     */
    public function getDetailsByIdsQueryBuilder($ids, $sort = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'attribute', 'prices', 'customerGroup', 'configuratorOptions'])
                ->from(Detail::class, 'details')
                ->leftJoin('details.configuratorOptions', 'configuratorOptions')
                ->leftJoin('details.prices', 'prices')
                ->leftJoin('details.attribute', 'attribute')
                ->innerJoin('prices.customerGroup', 'customerGroup')
                ->where('details.id IN (?1)')
                ->setParameter(1, $ids);

        if ($sort !== null && !empty($sort)) {
            $builder->addOrderBy($sort);
        } else {
            $builder->addOrderBy('details.id', 'ASC');
        }
        $builder->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');

        return $builder;
    }

    /**
     * Returns a list of all defined price groups. Used for the article
     * detail page in the backend module to assign the article to a price group.
     *
     * @return Query
     */
    public function getPriceGroupQuery()
    {
        return $this->getPriceGroupQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPriceGroupQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getPriceGroupQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['price_groups'])
            ->from(PriceGroup::class, 'price_groups')
            ->orderBy('price_groups.name', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined taxes. Used for the tax combo box on the article detail page in the article backend module.
     *
     * @return Query
     */
    public function getTaxesQuery()
    {
        return $this->getTaxesQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTaxesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getTaxesQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()
            ->select(['taxes'])
            ->from(Tax::class, 'taxes')
            ->orderBy('taxes.tax', 'DESC');

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined pack units. Used for the unit combo box on the article detail page in the article backend module.
     *
     * @return Query
     */
    public function getUnitsQuery()
    {
        return $this->getUnitsQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUnitsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getUnitsQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['units'])
            ->from(Unit::class, 'units')
            ->orderBy('units.name', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all article property groups. Used for the property combo box on the article detail page in the article backend module.
     *
     * @return Query
     */
    public function getPropertiesQuery()
    {
        return $this->getPropertiesQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPropertiesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getPropertiesQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['properties'])
            ->from(PropertyGroup::class, 'properties')
            ->orderBy('properties.name', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects
     * the defined configurator template for the passed article id.
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getConfiguratorTemplateByArticleIdQuery($articleId)
    {
        return $this->getConfiguratorTemplateByArticleIdQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function which creates the query builder object to select
     * all configurator template data for the passed article id.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getConfiguratorTemplateByArticleIdQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select([
            'template',
            'prices',
            'customerGroup',
            'attribute',
            'priceAttribute',
        ])
            ->from(Template::class, 'template')
            ->leftJoin('template.prices', 'prices')
            ->leftJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('template.attribute', 'attribute')
            ->leftJoin('prices.attribute', 'priceAttribute')
            ->where('template.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the prices for the passed article detail id.
     * Used for the article detail page in the article backend module.
     *
     * @param int $articleDetailId
     *
     * @return Query
     */
    public function getPricesQuery($articleDetailId)
    {
        return $this->getPricesQueryBuilder($articleDetailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPricesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleDetailId
     *
     * @return QueryBuilder
     */
    public function getPricesQueryBuilder($articleDetailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['prices', 'customerGroup', 'attribute'])
            ->from(Price::class, 'prices')
            ->join('prices.customerGroup', 'customerGroup')
            ->leftJoin('prices.attribute', 'attribute')
            ->where('prices.articleDetailsId = ?1')
            ->setParameter(1, $articleDetailId)
            ->orderBy('customerGroup.id', 'ASC')
            ->addOrderBy('prices.from', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the image attributes for the passed
     * image id. Used for the article backend module in the save article function.
     *
     * @param int $imageId
     *
     * @return Query
     */
    public function getImageAttributesQuery($imageId)
    {
        return $this->getImageAttributesQueryBuilder($imageId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getImageAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $imageId
     *
     * @internal param $articleDetailId
     *
     * @return QueryBuilder
     */
    public function getImageAttributesQueryBuilder($imageId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['attribute'])
            ->from(ProductImageAttribute::class, 'attribute')
            ->where('attribute.articleImageId = ?1')
            ->setParameter(1, $imageId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article link id. Used for the article backend module in the save article function.
     *
     * @param int $linkId
     *
     * @return Query
     */
    public function getLinkAttributesQuery($linkId)
    {
        return $this->getLinkAttributesQueryBuilder($linkId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getLinkAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $linkId
     *
     * @return QueryBuilder
     */
    public function getLinkAttributesQueryBuilder($linkId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['attribute'])
            ->from(ProductLinkAttribute::class, 'attribute')
            ->where('attribute.articleLinkId = ?1')
            ->setParameter(1, $linkId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article download id. Used for the article backend module in the save article function.
     *
     * @param int $downloadId
     *
     * @return Query
     */
    public function getDownloadAttributesQuery($downloadId)
    {
        return $this->getDownloadAttributesQueryBuilder($downloadId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDownloadAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $downloadId
     *
     * @return QueryBuilder
     */
    public function getDownloadAttributesQueryBuilder($downloadId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['attribute'])
            ->from(ProductDownloadAttribute::class, 'attribute')
            ->where('attribute.articleDownloadId = ?1')
            ->setParameter(1, $downloadId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article detail id. Used for the article backend module in the save article function.
     *
     * @param int $articleDetailId
     *
     * @return Query
     */
    public function getAttributesQuery($articleDetailId)
    {
        return $this->getAttributesQueryBuilder($articleDetailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleDetailId
     *
     * @return QueryBuilder
     */
    public function getAttributesQueryBuilder($articleDetailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['attribute'])
            ->from(ProductAttribute::class, 'attribute')
            ->where('attribute.articleDetailId = ?1')
            ->setParameter(1, $articleDetailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article price id. Used for the article backend module in the save article function.
     *
     * @param int $priceId
     *
     * @return Query
     */
    public function getPriceAttributesQuery($priceId)
    {
        return $this->getPriceAttributesQueryBuilder($priceId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPriceAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $priceId
     *
     * @return QueryBuilder
     */
    public function getPriceAttributesQueryBuilder($priceId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['attribute'])
            ->from(ProductPriceAttribute::class, 'attribute')
            ->where('attribute.articlePriceId = ?1')
            ->setParameter(1, $priceId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for article details with the same
     * article oder number like the passed number.
     *
     * @param string $number
     * @param int    $articleDetailId
     *
     * @return Query
     */
    public function getValidateNumberQuery($number, $articleDetailId)
    {
        return $this->getValidateNumberQueryBuilder($number, $articleDetailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateNumberQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $number
     * @param int    $articleDetailId
     *
     * @return QueryBuilder
     */
    public function getValidateNumberQueryBuilder($number, $articleDetailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['details'])
            ->from(Detail::class, 'details')
            ->where('details.number = ?1')
            ->andWhere('details.id != ?2')
            ->setParameter(1, $number)
            ->setParameter(2, $articleDetailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select article ids and names.
     * The passed article ids are excluded.
     *
     * @param int[]|null $ids
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return Query
     */
    public function getArticlesWithExcludedIdsQuery($ids = null, $filter = null, $offset = null, $limit = null)
    {
        $builder = $this->getArticlesWithExcludedIdsQueryBuilder($ids, $filter);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticlesWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]|null $ids
     *
     * @internal param null $filter
     *
     * @return QueryBuilder
     */
    public function getArticlesWithExcludedIdsQueryBuilder($ids = null, $filter = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['articles', 'mainDetail', 'supplier']);
        $builder->from($this->getEntityName(), 'articles');
        $builder->leftJoin('articles.mainDetail', 'mainDetail')
                ->leftJoin('articles.supplier', 'supplier');

        if (!empty($ids)) {
            $builder->where('articles.id NOT IN (:articleIds)')
                ->setParameter('articleIds', $ids, Connection::PARAM_INT_ARRAY);
        }

        if (!empty($filter) && $filter[0]['property'] === 'filter' && !empty($filter[0]['value'])) {
            $builder->andWhere('(
                    articles.name LIKE ?1
                OR
                    mainDetail.number LIKE ?1
                OR
                    supplier.name LIKE ?1
            )');
            $builder->setParameter(1, '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of all mails.
     *
     * @param int $supplierId
     *
     * @return Query
     */
    public function getSupplierQuery($supplierId)
    {
        return $this->getSupplierQueryBuilder($supplierId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $supplierId
     *
     * @return QueryBuilder
     */
    public function getSupplierQueryBuilder($supplierId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['supplier'])
                ->from(Supplier::class, 'supplier')
                ->where('supplier.id = ?1')
                ->setParameter(1, $supplierId);

        return $builder;
    }

    /**
     * Returns a list of all defined article suppliers as array, ordered by the supplier name.
     * Used for the article detail page in the backend module.
     *
     * @return Query
     */
    public function getSuppliersQuery()
    {
        return $this->getSuppliersQueryBuilder()->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSupplierQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return QueryBuilder
     */
    public function getSuppliersQueryBuilder()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()->select(['suppliers'])
            ->from(Supplier::class, 'suppliers')
            ->orderBy('suppliers.name', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects supplier ids, names and
     * description. The passed supplier ids are excluded.
     *
     * @param int[]|null $ids
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return Query
     */
    public function getSuppliersWithExcludedIdsQuery($ids = null, $filter = null, $offset = null, $limit = null)
    {
        $builder = $this->getSuppliersWithExcludedIdsQueryBuilder($ids, $filter);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSuppliersWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int[]|null $ids
     * @param array|null $filter
     *
     * @return QueryBuilder
     */
    public function getSuppliersWithExcludedIdsQueryBuilder($ids = null, $filter = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'suppliers.id as id',
            'suppliers.name as name',
            'suppliers.description as description',
        ]);
        $builder->from(Supplier::class, 'suppliers');
        if (!empty($ids)) {
            $builder->where('suppliers.id NOT IN (:ids)')
                ->setParameter('ids', $ids, Connection::PARAM_INT_ARRAY);
        }
        if (!empty($filter) && $filter[0]['property'] === 'filter' && !empty($filter[0]['value'])) {
            $builder->andWhere('suppliers.name LIKE ?1')
                ->setParameter(1, '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of supplier.
     *
     * @param array    $filter
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return Query
     */
    public function getSupplierListQuery($filter, array $orderBy, $limit = null, $offset = null)
    {
        $builder = $this->getSupplierListQueryBuilder($filter, $orderBy);

        if ($limit !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSupplierListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array $filter
     *
     * @return QueryBuilder
     */
    public function getSupplierListQueryBuilder($filter, array $orderBy)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'supplier.id as id',
            'supplier.name as name',
            'supplier.image as image',
            'supplier.link as link',
            'supplier.description as description',
            $builder->expr()->count('articles.id') . ' as articleCounter',
        ]);
        $builder->from(Supplier::class, 'supplier');
        $builder->leftJoin('supplier.articles', 'articles');
        $builder->groupBy('supplier.id');

        if (is_array($filter) && ($filter[0]['property'] === 'name')) {
            //filter the displayed columns with the passed filter
            $builder
                ->where('supplier.name LIKE ?1') //Search only the beginning of the customer number.
                ->orWhere('supplier.description LIKE ?1'); //Full text search for the first name of the customer

            //set the filter parameter for the different columns.
            $builder->setParameter(1, '%' . $filter[0]['value'] . '%');
        }

        $builder->addOrderBy($orderBy);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of article votes.
     *
     * @param string|null $filter
     * @param int|null    $offset
     * @param int|null    $limit
     * @param array|null  $order
     *
     * @return Query
     */
    public function getVoteListQuery($filter = null, $offset = null, $limit = null, $order = null)
    {
        $builder = $this->getVoteListQueryBuilder($filter, $order);
        if ($limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVoteListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string     $filter
     * @param array|null $order
     *
     * @return QueryBuilder
     */
    public function getVoteListQueryBuilder($filter, $order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'vote.id as id',
            'vote.articleId as articleId',
            'vote.name as name',
            'vote.headline as headline',
            'vote.comment as comment',
            'vote.points as points',
            'vote.datum as datum',
            'vote.active as active',
            'vote.answer as answer',
            'vote.email as email',
            'article.name as articleName',
        ]);
        $builder->from(Vote::class, 'vote');
        $builder->join('vote.article', 'article');
        if (!empty($filter)) {
            $builder->where('article.name LIKE ?1')
                ->setParameter(1, '%' . $filter . '%');
        }
        if ($order === null) {
            $builder->addOrderBy('vote.datum', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all articles with registered notifications
     *
     * @param int|null  $limit
     * @param bool|null $summarize
     *
     * @return Query
     */
    public function getArticlesWithRegisteredNotificationsQuery(
        $filter = null,
        $offset = null,
        $limit = null,
        $order = null,
        $summarize = null
    ) {
        $builder = $this->getArticlesWithRegisteredNotificationsBuilder($filter, $order, $summarize);
        if (empty($summarize) && !empty($limit) && !empty($offset)) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticlesWithRegisteredNotificationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param array      $filter
     * @param array|null $order
     * @param bool       $summarize
     *
     * @return QueryBuilder
     */
    public function getArticlesWithRegisteredNotificationsBuilder($filter, $order, $summarize)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'notification.articleNumber as number',
            'COUNT(notification.articleNumber) as registered',
            '( COUNT(notification.articleNumber) ) - ( SUM(notification.send) ) as notNotified',
            'article.name as name',
        ])
            ->from(Notification::class, 'notification')
            ->leftJoin('notification.articleDetail', 'articleDetail')
            ->leftJoin('articleDetail.article', 'article');

        if (!empty($summarize)) {
            return $builder;
        }

        $builder->groupBy('notification.articleNumber');

        // Search part
        if (isset($filter[0]['property']) && $filter[0]['property'] === 'search') {
            $builder->where('notification.articleNumber LIKE :search')
                ->orWhere('article.name LIKE :search')
                ->setParameter('search', $filter[0]['value']);
        }

        if ($order === null) {
            $builder->addOrderBy('registered', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all notification customers by the given articleOrderNumber
     *
     * @param string $articleOrderNumber
     * @param array  $filter
     * @param int    $offset
     * @param int    $limit
     * @param array  $order
     *
     * @internal param $articleOrderNumber
     *
     * @return Query
     */
    public function getNotificationCustomerByArticleQuery($articleOrderNumber, $filter, $offset, $limit, $order)
    {
        $builder = $this->getNotificationCustomerByArticleBuilder($articleOrderNumber, $filter, $order);
        if (!empty($limit) && !empty($offset)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getNotificationCustomerByArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string     $articleOrderNumber
     * @param array      $filter
     * @param array|null $order
     *
     * @internal param $articleOrderNumber
     *
     * @return QueryBuilder
     */
    public function getNotificationCustomerByArticleBuilder($articleOrderNumber, $filter, $order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->setAlias('notification');
        $builder->select([
            'notification.mail as mail',
            'notification.date as date',
            'notification.send as notified',
            'notification.articleNumber as orderNumber',
            'customer.id as customerId',
            "CONCAT(CONCAT(customer.firstname, ' '), customer.lastname) as name",
        ])
            ->from(Notification::class, 'notification')
            ->leftJoin(
                'notification.customer',
                'customer',
                Join::WITH,
                'customer.accountMode = 0 AND customer.languageId = notification.language'
            )
            ->where('notification.articleNumber = :orderNumber')
            ->setParameter('orderNumber', $articleOrderNumber);

        // Search part
        if (isset($filter[0]['property']) && $filter[0]['property'] === 'search') {
            $builder->andWhere('(
                        notification.mail LIKE :search
                        OR notification.articleNumber LIKE :search
                        OR customer.lastname LIKE :search
                        OR customer.firstname LIKE :search
                    )')
                ->setParameter('search', $filter[0]['value']);
        }

        if ($order === null) {
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all ESD by the given articleId
     *
     * @param int   $articleId
     * @param array $filter
     * @param int   $offset
     * @param int   $limit
     * @param array $order
     *
     * @return Query
     */
    public function getEsdByArticleQuery($articleId, $filter, $offset, $limit, $order)
    {
        $builder = $this->getEsdByArticleQueryBuilder($articleId, $filter, $order);
        if (!empty($limit) && !empty($offset)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getEsdByArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int        $articleId
     * @param array|null $filter
     * @param array|null $order
     *
     * @return QueryBuilder
     */
    public function getEsdByArticleQueryBuilder($articleId, $filter, $order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'esd.id as id',
            'esd.date as date',
            'esd.file as file',
            'esd.hasSerials as hasSerials',
            'COUNT(serials) as serialsTotal',
            'COUNT(esdOrder.id) as serialsUsed',
            'COUNT(esdOrder.id) as downloads',
            'article.name as name',
            'articleDetail.id as articleDetailId',
            'articleDetail.number',
            'articleDetail.additionalText as additionalText',
            'article.id as articleId',
        ]);
        $builder->from(Esd::class, 'esd')
            ->leftJoin('esd.serials', 'serials')
            ->leftJoin('serials.esdOrder', 'esdOrder')
            ->leftJoin('esd.article', 'article')
            ->leftJoin('esd.articleDetail', 'articleDetail')
            ->leftJoin('esd.attribute', 'attribute')
            ->groupBy('esd.id')
            ->where('esd.article = :articleId')
            ->setParameter('articleId', $articleId);

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'article.name LIKE :search',
                'articleDetail.additionalText LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        if ($order === null) {
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all serials by the given esdId
     *
     * @param int   $esdId
     * @param array $filter
     * @param int   $offset
     * @param int   $limit
     * @param array $order
     *
     * @return Query
     */
    public function getSerialsByEsdQuery($esdId, $filter, $offset, $limit, $order)
    {
        $builder = $this->getSerialsByEsdQueryBuilder($esdId, $filter, $order);
        if (!empty($limit)) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSerialsByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int        $esdId
     * @param array|null $filter
     * @param array|null $order
     *
     * @return QueryBuilder
     */
    public function getSerialsByEsdQueryBuilder($esdId, $filter, $order)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'serial.id as id',
            'serial.serialnumber as serialnumber',
            'esdOrder.date as date',
            'customer.id as customerId',
            'customer.accountMode as accountMode',
            'customer.email as customerEmail',
        ])
            ->from(EsdSerial::class, 'serial')
            ->leftJoin('serial.esdOrder', 'esdOrder')
            ->leftJoin('esdOrder.customer', 'customer')
            ->where('serial.esd = :esdId')
            ->setParameter('esdId', $esdId);

        if ($filter !== null) {
            $builder->andWhere($builder->expr()->orX(
                'customer.email LIKE :search',
                'serial.serialnumber LIKE :search'
            ));
            $builder->setParameter('search', '%' . $filter[0]['value'] . '%');
        }

        if ($order === null) {
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all free serials by the given esdId
     *
     * @param int $esdId
     *
     * @return Query
     */
    public function getFreeSerialsCountByEsdQuery($esdId)
    {
        return $this->getFreeSerialsCountByEsdQueryBuilder($esdId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFreeSerialsCountByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $esdId
     *
     * @return QueryBuilder
     */
    public function getFreeSerialsCountByEsdQueryBuilder($esdId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()
                ->select('COUNT(serials) - COUNT(esdOrder.id) as serialsFree')
                ->from(Esd::class, 'esd')
                ->leftJoin('esd.serials', 'serials')
                ->leftJoin('serials.esdOrder', 'esdOrder')
                ->where('esd.id = :esdId')
                ->setParameter('esdId', $esdId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all unused serials by the given esdId
     *
     * @param int $esdId
     *
     * @return Query
     */
    public function getUnusedSerialsByEsdQuery($esdId)
    {
        return $this->getUnusedSerialsByEsdQueryBuilder($esdId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUnusedSerialsByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $esdId
     *
     * @return QueryBuilder
     */
    public function getUnusedSerialsByEsdQueryBuilder($esdId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder()
                ->select('serial')
                ->from(EsdSerial::class, 'serial')
                ->leftJoin('serial.esdOrder', 'esdOrder')
                ->where('serial.esd = :esdId')
                ->andWhere('esdOrder IS NULL')
                ->setParameter('esdId', $esdId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleCoverImageQuery($articleId)
    {
        return $this->getArticleCoverImageQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleCoverImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleCoverImageQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['images', 'attribute', 'media'])
                ->from(Image::class, 'images')
                ->leftJoin('images.attribute', 'attribute')
                ->leftJoin('images.children', 'children')
                ->leftJoin('images.media', 'media')
                ->where('images.articleId = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->andWhere('children.id IS NULL')
                ->setParameter('articleId', $articleId)
                ->orderBy('images.main', 'ASC')
                ->addOrderBy('images.position', 'ASC')
                ->setFirstResult(0)
                ->setMaxResults(1);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     *
     * @internal param $article
     *
     * @return Query
     */
    public function getArticleFallbackCoverQuery($articleId)
    {
        return $this->getArticleFallbackCoverQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleFallbackCoverQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @internal param $article
     *
     * @return QueryBuilder
     */
    public function getArticleFallbackCoverQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['images', 'attribute'])
                ->from(Image::class, 'images')
                ->leftJoin('images.attribute', 'attribute')
                ->where('images.articleId = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->andWhere('images.main = :main')
                ->setParameter('main', 1)
                ->setParameter('articleId', $articleId)
                ->setFirstResult(0)
                ->setMaxResults(1);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param string $number
     * @param int    $offset
     * @param int    $limit
     *
     * @return Query
     */
    public function getVariantImagesByArticleNumberQuery($number, $offset = null, $limit = null)
    {
        $builder = $this->getVariantImagesByArticleNumberQueryBuilder($number);
        if ($offset !== null && $limit !== null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getVariantImagesByArticleNumberQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param string $number
     *
     * @return QueryBuilder
     */
    public function getVariantImagesByArticleNumberQueryBuilder($number)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'imageParent.id',
            'imageParent.articleId',
            'imageParent.articleDetailId',
            'imageParent.description',
            'imageParent.path',
            'imageParent.main',
            'imageParent.position',
            'imageParent.width',
            'imageParent.height',
            'imageParent.extension',
            'imageParent.parentId',
            'media.type',
            'attribute.attribute1',
            'attribute.attribute2',
            'attribute.attribute3',
        ])
            ->from(Image::class, 'images')
            ->innerJoin('images.articleDetail', 'articleDetail')
            ->innerJoin('images.parent', 'imageParent')
            ->leftJoin('imageParent.attribute', 'attribute')
            ->leftJoin('imageParent.media', 'media')
            ->where('articleDetail.number = ?1')
            ->setParameter(1, $number)
            ->orderBy('imageParent.main', 'ASC')
            ->addOrderBy('imageParent.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getArticleImagesQuery($articleId)
    {
        return $this->getArticleImagesQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImagesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getArticleImagesQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['images'])
                ->from(Image::class, 'images')
                ->leftJoin('images.children', 'children')
                ->where('images.articleId = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->andWhere('images.articleDetailId IS NULL')
                ->andWhere('children.id IS NULL')
                ->setParameter('articleId', $articleId)
                ->addOrderBy('images.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to delete prices associated
     * with the given article
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getRemovePricesQuery($articleId)
    {
        return $this->getRemovePricesQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemovePricesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getRemovePricesQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Price::class, 'prices')
                ->where('prices.articleId = :id')
                ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to delete attributes
     * associated with the given articleDetailId
     *
     * @param int $articleDetailId
     *
     * @return Query
     */
    public function getRemoveAttributesQuery($articleDetailId)
    {
        return $this->getRemoveAttributesQueryBuilder($articleDetailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleDetailId
     *
     * @return QueryBuilder
     */
    public function getRemoveAttributesQueryBuilder($articleDetailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(ProductAttribute::class, 'attribute')
                ->where('attribute.articleDetailId = :id')
                ->setParameter('id', $articleDetailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to delete esd articles
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getRemoveESDQuery($articleId)
    {
        return $this->getRemoveESDQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveESDQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getRemoveESDQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Esd::class, 'esd')
                ->where('esd.articleId = :id')
                ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove translations associated
     * with the given articleId
     *
     * @param int $articleId
     *
     * @return Query
     */
    public function getRemoveArticleTranslationsQuery($articleId)
    {
        return $this->getRemoveArticleTranslationsQueryBuilder($articleId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveArticleTranslationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $articleId
     *
     * @return QueryBuilder
     */
    public function getRemoveArticleTranslationsQueryBuilder($articleId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Translation::class, 'translation')
            ->where('translation.key = :id')
            ->andWhere('translation.type = \'article\'')
            ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove translations associated
     * with the given articleDetailId
     *
     * @param int $detailId
     *
     * @return Query
     */
    public function getRemoveVariantTranslationsQuery($detailId)
    {
        return $this->getRemoveVariantTranslationsQueryBuilder($detailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveVariantTranslationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $detailId
     *
     * @return QueryBuilder
     */
    public function getRemoveVariantTranslationsQueryBuilder($detailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Translation::class, 'translation')
            ->where('translation.key = :id')
            ->andWhere('translation.type = \'variant\'')
            ->setParameter('id', $detailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove images associated
     * with the given articleDetailId
     *
     * @param int $detailId
     *
     * @return Query
     */
    public function getRemoveImageQuery($detailId)
    {
        return $this->getRemoveImageQueryBuilder($detailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $detailId
     *
     * @return QueryBuilder
     */
    public function getRemoveImageQueryBuilder($detailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Image::class, 'image')
                ->where('image.articleDetailId = :id')
                ->setParameter('id', $detailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove a given article detail
     *
     * @param int $detailId
     *
     * @return Query
     */
    public function getRemoveDetailQuery($detailId)
    {
        return $this->getRemoveDetailQueryBuilder($detailId)->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param int $detailId
     *
     * @return QueryBuilder
     */
    public function getRemoveDetailQueryBuilder($detailId)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete(Detail::class, 'detail')
                ->where('detail.id = :id')
                ->setParameter('id', $detailId);

        return $builder;
    }

    /**
     * Returns the detail query builder for variants.
     * This query builder should be used to read the whole variant data.
     *
     * @return QueryBuilder
     */
    public function getVariantDetailQuery()
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'variants',
            'attribute',
            'prices',
            'customerGroup',
            'options',
            'images',
        ]);

        $builder->from(Detail::class, 'variants')
            ->innerJoin('variants.article', 'article')
            ->leftJoin('variants.attribute', 'attribute')
            ->leftJoin('variants.images', 'images')
            ->leftJoin('variants.prices', 'prices')
            ->innerJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('variants.configuratorOptions', 'options');

        return $builder;
    }
}
