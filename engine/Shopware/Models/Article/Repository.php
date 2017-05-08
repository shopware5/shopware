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

use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Model\QueryBuilder;

class Repository extends ModelRepository
{
    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleBaseDataQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'mainDetail', 'tax', 'attribute']);
        $builder->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.mainDetail', 'mainDetail')
                ->leftJoin('article.tax', 'tax')
                ->leftJoin('mainDetail.attribute', 'attribute')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleCategoriesQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'categories'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.categories', 'categories', null, null, 'categories.id')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleSimilarsQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'similar', 'similarDetail'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.similar', 'similar')
                ->leftJoin('similar.mainDetail', 'similarDetail')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleRelatedProductStreamsQuery($articleId)
    {
        return $this->getArticleRelatedProductStreamsQueryBuilder($articleId)->getQuery();
    }

    /**
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleRelatedProductStreamsQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article.id', 'relatedProductStreams.id', 'relatedProductStreams.name', 'relatedProductStreams.description'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.relatedProductStreams', 'relatedProductStreams')
                ->where('article.id = :articleId')
                ->andWhere('relatedProductStreams.id IS NOT NULL')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleRelatedQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'accessories', 'accessoryDetail'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.related', 'accessories')
                ->leftJoin('accessories.mainDetail', 'accessoryDetail')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleWithImagesQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'images', 'imageAttribute', 'imageMapping', 'mappingRule', 'ruleOption'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.images', 'images')
                ->leftJoin('images.attribute', 'imageAttribute')
                ->leftJoin('images.mappings', 'imageMapping')
                ->leftJoin('imageMapping.rules', 'mappingRule')
                ->leftJoin('mappingRule.option', 'ruleOption')
                ->where('article.id = :articleId')
                ->andWhere('images.parentId IS NULL')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleLinksQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'links', 'linkAttribute'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.links', 'links')
                ->leftJoin('links.attribute', 'linkAttribute')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleDownloadsQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'downloads', 'downloadAttribute'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.downloads', 'downloads')
                ->leftJoin('downloads.attribute', 'downloadAttribute')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleCustomerGroupsQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'customerGroups'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.customerGroups', 'customerGroups')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Used for the article backend module to load the article data into
     * the module. This function selects only some fragments for the whole article
     * data. The full article data stack is defined in the
     * Shopware_Controller_Backend_Article::getArticle function
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleConfiguratorSetQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'configuratorSet', 'groups', 'options'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->innerJoin('article.configuratorSet', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->addOrderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC')
                ->where('article.id = :articleId')
                ->setParameters(['articleId' => $articleId]);

        return $builder;
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects all data about a single article.
     * The query selects the article, main detail of the article, assigned categories, assigned similar and related articles,
     * links and downloads of the article, selected tax, associated article images and the attributes for the different models.
     * The query is used for the article detail page of the article backend module to load the article data into the view.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleQuery($articleId)
    {
        $builder = $this->getArticleQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select([
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
                ->from('Shopware\Models\Article\Article', 'article')
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
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $articleId
     * @param $options
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailsForOptionIdsQuery($articleId, $options)
    {
        $builder = $this->getDetailsForOptionIdsQueryBuilder($articleId, $options);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailsForOptionIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     * @param $options
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailsForOptionIdsQueryBuilder($articleId, $options)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details'])
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.articleId = :articleId')
                ->setParameter('articleId', $articleId);

        foreach ($options as $key => $option) {
            $alias = 'o' . $key;
            $builder->innerJoin('details.configuratorOptions', $alias, \Doctrine\ORM\Query\Expr\Join::WITH, $alias . '.id = :' . $alias);

            //in some cases the options parameter can contains an array of option models, an two dimensional array with option data, or an one dimensional array with ids.
            if ($option instanceof \Shopware\Models\Article\Configurator\Option) {
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
     * @param $imageId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImageDataQuery($imageId)
    {
        $builder = $this->getArticleImageDataQueryBuilder($imageId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImageDataQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $imageId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImageDataQueryBuilder($imageId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['image'])
                ->from('Shopware\Models\Article\Image', 'image')
                ->where('image.id = ?1')
                ->setParameter(1, $imageId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $imageId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDeleteImageChildrenQuery($imageId)
    {
        $builder = $this->getDeleteImageChildrenQueryBuilder($imageId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDeleteImageChildrenQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $imageId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDeleteImageChildrenQueryBuilder($imageId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Image', 'images')
                ->andWhere('images.parentId = ?1')
                ->setParameter(1, $imageId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $imageId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImageQuery($imageId)
    {
        $builder = $this->getArticleImageQueryBuilder($imageId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $imageId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImageQueryBuilder($imageId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['image', 'mappings', 'rules', 'option'])
                ->from('Shopware\Models\Article\Image', 'image')
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleConfiguratorSetByArticleIdQuery($articleId)
    {
        $builder = $this->getArticleConfiguratorSetByArticleIdQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleConfiguratorSetByArticleIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleConfiguratorSetByArticleIdQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['PARTIAL article.{id}', 'configuratorSet', 'groups', 'options'])
                ->from('Shopware\Models\Article\Article', 'article')
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleConfiguratorSetByArticleIdIndexedByIdsQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'configuratorSet', 'groups', 'options'])
                ->from('Shopware\Models\Article\Article', 'article')
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
     * @param $articleId
     * @param $optionId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     * @param $optionId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleDetailByConfiguratorOptionIdQueryBuilder($articleId, $optionId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'prices'])
                ->from('Shopware\Models\Article\Detail', 'details')
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
     * @param $optionsIds
     *
     * @return \Doctrine\ORM\Query
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
     * @param $optionsIds
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorGroupsAndOptionsByOptionsIdsIndexedByOptionIdsQueryBuilder($optionsIds)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups', 'options'])
                ->from('Shopware\Models\Article\Configurator\Group', 'groups')
                ->innerJoin('groups.options', 'options', \Doctrine\ORM\Query\Expr\Join::WITH, 'options.id IN (?1)', 'options.id')
                ->setParameter(1, $optionsIds);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $articleId
     * @param $firstOptionId
     * @param $secondOptionId
     * @param $article
     * @param $customerGroupKey
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleDetailForTableConfiguratorOptionCombinationQuery($articleId, $firstOptionId, $secondOptionId, $article, $customerGroupKey)
    {
        $builder = $this->getArticleDetailForTableConfiguratorOptionCombinationQueryBuilder($articleId, $firstOptionId, $secondOptionId, $article, $customerGroupKey);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleDetailForTableConfiguratorOptionCombinationQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     * @param $firstOptionId
     * @param $secondOptionId
     * @param $article Article
     * @param $customerGroupKey
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleDetailForTableConfiguratorOptionCombinationQueryBuilder($articleId, $firstOptionId, $secondOptionId, $article, $customerGroupKey)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'prices'])
                ->from('Shopware\Models\Article\Detail', 'details')
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleWithVariantsAndOptionsQuery($articleId)
    {
        $builder = $this->getArticleWithVariantsAndOptionsQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleWithVariantsAndOptionsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleWithVariantsAndOptionsQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['article', 'details', 'options'])
                ->from('Shopware\Models\Article\Article', 'article')
                ->leftJoin('article.details', 'details')
                ->leftJoin('details.configuratorOptions', 'options')
                ->where('article.id = ?1')
                ->setParameter(1, $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $articleId
     * @param $customerGroupKey
     * @param $article
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     * @param $customerGroupKey
     * @param $article
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorTablePreSelectionItemQueryBuilder($articleId, $customerGroupKey, $article)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'prices', 'options'])
                ->from('Shopware\Models\Article\Detail', 'details')
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
     * @param $ids
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorSetsWithExcludedIdsQuery($ids)
    {
        $builder = $this->getConfiguratorSetsWithExcludedIdsQueryBuilder($ids);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorSetsWithExcludedIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $ids
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorSetsWithExcludedIdsQueryBuilder($ids)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups', 'options'])
                ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'groups')
                ->leftJoin('configuratorSet.options', 'options')
                ->where('configuratorSet.public = ?1')
                ->setParameter(1, 1)
                ->orderBy('groups.position', 'ASC')
                ->addOrderBy('options.groupId', 'ASC')
                ->addOrderBy('options.position', 'ASC');

        if (!empty($ids)) {
            $builder->andWhere($builder->expr()->notIn('configuratorSet.id', $ids));
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorSetQuery($configuratorSetId)
    {
        $builder = $this->getConfiguratorSetQueryBuilder($configuratorSetId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorSetQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorSetQueryBuilder($configuratorSetId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['configuratorSet', 'groups', 'options'])
                ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
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
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorDependenciesQuery($configuratorSetId)
    {
        $builder = $this->getConfiguratorDependenciesQueryBuilder($configuratorSetId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorDependenciesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorDependenciesQueryBuilder($configuratorSetId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder($configuratorSetId);
        $builder->select(['dependencies', 'dependencyParent', 'dependencyChild'])
                ->from('Shopware\Models\Article\Configurator\Dependency', 'dependencies')
                ->leftJoin('dependencies.parentOption', 'dependencyParent')
                ->leftJoin('dependencies.childOption', 'dependencyChild')
                ->where('dependencies.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\Query
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
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorDependenciesIndexedByParentIdQueryBuilder($configuratorSetId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['dependencies'])
                ->from('Shopware\Models\Article\Configurator\Dependency', 'dependencies', 'dependencies.parentId')
                ->where('dependencies.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId)
                ->orderBy('dependencies.parentId', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorPriceVariationsQuery($configuratorSetId)
    {
        $builder = $this->getConfiguratorPriceVariationsQueryBuilder($configuratorSetId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorPriceVariationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $configuratorSetId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorPriceVariationsQueryBuilder($configuratorSetId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['priceVariations'])
                ->from('Shopware\Models\Article\Configurator\PriceVariation', 'priceVariations')
                ->where('priceVariations.configuratorSetId = ?1')
                ->setParameter(1, $configuratorSetId);

        return $builder;
    }

    /**
     * Internal helper function to optimize the performance of the "getArticleConfiguratorSetQueryBuilder"  function.
     * Without this function, the getArticleConfiguratorSetQueryBuilder needs to join the s_articles_details
     * to filter the articleId.
     *
     * @param $articleId
     *
     * @return array
     */
    public function getArticleConfiguratorSetOptionIds($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $ids = $builder->select('DISTINCT options.id')
                ->from('Shopware\Models\Article\Detail', 'detail')
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
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorGroupsQuery()
    {
        $builder = $this->getConfiguratorGroupsQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorGroupsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorGroupsQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['groups', 'options'])
                ->from('Shopware\Models\Article\Configurator\Group', 'groups')
                ->leftJoin('groups.options', 'options')
                ->orderBy('groups.position');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getFirstArticleDetailWithKindTwoQuery($articleId)
    {
        $builder = $this->getFirstArticleDetailWithKindTwoQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFirstArticleDetailWithKindTwoQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFirstArticleDetailWithKindTwoQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details'])
                ->from('Shopware\Models\Article\Detail', 'details')
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
     * @return \Doctrine\ORM\Query
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllConfiguratorOptionsIndexedByIdQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['options'])
                ->from('Shopware\Models\Article\Configurator\Option', 'options', 'options.id');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the id for the listing query
     * of the configurator. To display the options in the listing the configurator listing needs
     * an id query to allow an store paging.
     *
     * @param      $articleId
     * @param null $filter
     * @param null $sort
     * @param null $offset
     * @param null $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorListIdsQuery($articleId, $filter = null, $sort = null, $offset = null, $limit = null)
    {
        $builder = $this->getConfiguratorListIdsQueryBuilder($articleId, $filter, $sort);
        if ($limit != null) {
            $builder->setFirstResult($offset)
                    ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getConfiguratorListIdsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param      $articleId
     * @param null $filter
     * @param null $sort
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorListIdsQueryBuilder($articleId, $filter = null, $sort = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select('details.id')
                ->from('Shopware\Models\Article\Detail', 'details')
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
     * @param      $ids
     * @param null $sort
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDetailsByIdsQuery($ids, $sort = null)
    {
        $builder = $this->getDetailsByIdsQueryBuilder($ids, $sort);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDetailsByArticleIdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param      $ids
     * @param null $sort
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDetailsByIdsQueryBuilder($ids, $sort = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['details', 'attribute', 'prices', 'customerGroup', 'configuratorOptions'])
                ->from('Shopware\Models\Article\Detail', 'details')
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
     * @return \Doctrine\ORM\Query
     */
    public function getPriceGroupQuery()
    {
        $builder = $this->getPriceGroupQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPriceGroupQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPriceGroupQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['price_groups'])
                ->from('Shopware\Models\Price\Group', 'price_groups')
                ->orderBy('price_groups.name', 'ASC');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined taxes. Used for the tax combo box on the article detail page in the article backend module.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getTaxesQuery()
    {
        $builder = $this->getTaxesQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getTaxesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getTaxesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['taxes'])
                ->from('Shopware\Models\Tax\Tax', 'taxes')
                ->orderBy('taxes.name', 'ASC');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all defined pack units. Used for the unit combo box on the article detail page in the article backend module.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUnitsQuery()
    {
        $builder = $this->getUnitsQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUnitsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUnitsQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['units'])
                ->from('Shopware\Models\Article\Unit', 'units')
                ->orderBy('units.name', 'ASC');
    }

    /**
     * Returns an instance of \Doctrine\ORM\Query object which selects a list of
     * all article property groups. Used for the property combo box on the article detail page in the article backend module.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPropertiesQuery()
    {
        $builder = $this->getPropertiesQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPropertiesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPropertiesQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['properties'])
                ->from('Shopware\Models\Property\Group', 'properties')
                ->orderBy('properties.name', 'ASC');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects
     * the defined configurator template for the passed article id.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getConfiguratorTemplateByArticleIdQuery($articleId)
    {
        $builder = $this->getConfiguratorTemplateByArticleIdQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function which creates the query builder object to select
     * all configurator template data for the passed article id.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getConfiguratorTemplateByArticleIdQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['template', 'prices', 'customerGroup', 'attribute', 'priceAttribute'])
                ->from('Shopware\Models\Article\Configurator\Template\Template', 'template')
                ->leftJoin('template.prices', 'prices')
                ->leftJoin('prices.customerGroup', 'customerGroup')
                ->leftJoin('template.attribute', 'attribute')
                ->leftJoin('prices.attribute', 'priceAttribute')
                ->where('template.articleId = :articleId')
                ->setParameters(['articleId' => $articleId]);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects the prices for the passed article detail id.
     * Used for the article detail page in the article backend module.
     *
     * @param $articleDetailId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPricesQuery($articleDetailId)
    {
        $builder = $this->getPricesQueryBuilder($articleDetailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPricesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleDetailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPricesQueryBuilder($articleDetailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['prices', 'customerGroup', 'attribute'])
                ->from('Shopware\Models\Article\Price', 'prices')
                ->join('prices.customerGroup', 'customerGroup')
                ->leftJoin('prices.attribute', 'attribute')
                ->where('prices.articleDetailsId = ?1')
                ->setParameter(1, $articleDetailId)
                ->orderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the image attributes for the passed
     * image id. Used for the article backend module in the save article function.
     *
     * @param $imageId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getImageAttributesQuery($imageId)
    {
        $builder = $this->getImageAttributesQueryBuilder($imageId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getImageAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $imageId
     *
     * @internal param $articleDetailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getImageAttributesQueryBuilder($imageId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                ->from('Shopware\Models\Attribute\ArticleImage', 'attribute')
                ->where('attribute.articleImageId = ?1')
                ->setParameter(1, $imageId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article link id. Used for the article backend module in the save article function.
     *
     * @param $linkId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getLinkAttributesQuery($linkId)
    {
        $builder = $this->getLinkAttributesQueryBuilder($linkId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getLinkAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $linkId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLinkAttributesQueryBuilder($linkId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                ->from('Shopware\Models\Attribute\ArticleLink', 'attribute')
                ->where('attribute.articleLinkId = ?1')
                ->setParameter(1, $linkId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article download id. Used for the article backend module in the save article function.
     *
     * @param $downloadId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getDownloadAttributesQuery($downloadId)
    {
        $builder = $this->getDownloadAttributesQueryBuilder($downloadId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getDownloadAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $downloadId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDownloadAttributesQueryBuilder($downloadId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                ->from('Shopware\Models\Attribute\ArticleDownload', 'attribute')
                ->where('attribute.articleDownloadId = ?1')
                ->setParameter(1, $downloadId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article detail id. Used for the article backend module in the save article function.
     *
     * @param $articleDetailId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getAttributesQuery($articleDetailId)
    {
        $builder = $this->getAttributesQueryBuilder($articleDetailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleDetailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAttributesQueryBuilder($articleDetailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                ->from('Shopware\Models\Attribute\Article', 'attribute')
                ->where('attribute.articleDetailId = ?1')
                ->setParameter(1, $articleDetailId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search the attributes for the passed
     * article price id. Used for the article backend module in the save article function.
     *
     * @param $priceId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getPriceAttributesQuery($priceId)
    {
        $builder = $this->getPriceAttributesQueryBuilder($priceId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getPriceAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $priceId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getPriceAttributesQueryBuilder($priceId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['attribute'])
                ->from('Shopware\Models\Attribute\ArticlePrice', 'attribute')
                ->where('attribute.articlePriceId = ?1')
                ->setParameter(1, $priceId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which search for article details with the same
     * article oder number like the passed number.
     *
     * @param $number
     * @param $articleDetailId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getValidateNumberQuery($number, $articleDetailId)
    {
        $builder = $this->getValidateNumberQueryBuilder($number, $articleDetailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getValidateNumberQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $number
     * @param $articleDetailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getValidateNumberQueryBuilder($number, $articleDetailId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['details'])
                ->from('Shopware\Models\Article\Detail', 'details')
                ->where('details.number = ?1')
                ->andWhere('details.id != ?2')
                ->setParameter(1, $number)
                ->setParameter(2, $articleDetailId);
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select article ids and names.
     * The passed article ids are excluded.
     *
     * @param null $ids
     * @param null $filter
     * @param null $offset
     * @param null $limit
     *
     * @return \Doctrine\ORM\Query
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
     * @param null $ids
     * @param null $filter
     *
     * @internal param null $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticlesWithExcludedIdsQueryBuilder($ids = null, $filter = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['articles', 'mainDetail', 'supplier']);
        $builder->from($this->getEntityName(), 'articles');
        $builder->leftJoin('articles.mainDetail', 'mainDetail')
                ->leftJoin('articles.supplier', 'supplier');

        if (!empty($ids)) {
            $ids = implode(',', $ids);
            $builder->where($builder->expr()->notIn('articles.id', $ids));
        }

        if (!empty($filter) && $filter[0]['property'] == 'filter' && !empty($filter[0]['value'])) {
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
     * @param $supplierId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSupplierQuery($supplierId)
    {
        $builder = $this->getSupplierQueryBuilder($supplierId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getMailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $supplierId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSupplierQueryBuilder($supplierId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['supplier'])
                ->from('Shopware\Models\Article\Supplier', 'supplier')
                ->where('supplier.id = ?1')
                ->setParameter(1, $supplierId);

        return $builder;
    }

    /**
     * Returns a list of all defined article suppliers as array, ordered by the supplier name.
     * Used for the article detail page in the backend module.
     *
     * @return \Doctrine\ORM\Query
     */
    public function getSuppliersQuery()
    {
        $builder = $this->getSuppliersQueryBuilder();

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getSupplierQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSuppliersQueryBuilder()
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        return $builder->select(['suppliers'])
                ->from('Shopware\Models\Article\Supplier', 'suppliers')
                ->orderBy('suppliers.name', 'ASC');
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects supplier ids, names and
     * description. The passed supplier ids are excluded.
     *
     * @param $ids
     * @param $filter
     * @param $offset
     * @param $limit
     *
     * @return \Doctrine\ORM\Query
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
     * @param $ids
     * @param $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSuppliersWithExcludedIdsQueryBuilder($ids = null, $filter = null)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'suppliers.id as id',
            'suppliers.name as name',
            'suppliers.description as description',
        ]);
        $builder->from('Shopware\Models\Article\Supplier', 'suppliers');
        if (!empty($ids)) {
            $ids = implode(',', $ids);
            $builder->where($builder->expr()->notIn('suppliers.id', $ids));
        }
        if (!empty($filter) && $filter[0]['property'] == 'filter' && !empty($filter[0]['value'])) {
            $builder->andWhere('suppliers.name LIKE ?1')
                    ->setParameter(1, '%' . $filter[0]['value'] . '%');
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which select a list of supplier.
     *
     * @param array $filter
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return \Doctrine\ORM\Query
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
     * @param array $orderBy
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSupplierListQueryBuilder($filter, array $orderBy)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
                'supplier.id as id',
                'supplier.name as name',
                'supplier.image as image',
                'supplier.link as link',
                'supplier.description as description',
                $builder->expr()->count('articles.id') . ' as articleCounter', ]
        );
        $builder->from('Shopware\Models\Article\Supplier', 'supplier');
        $builder->leftJoin('supplier.articles', 'articles');
        $builder->groupBy('supplier.id');

        if (is_array($filter) && ('name' == $filter[0]['property'])) {
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
     * @param null $filter
     * @param null $offset
     * @param null $limit
     * @param null $order
     *
     * @return \Doctrine\ORM\Query
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
     * @param $filter
     * @param $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVoteListQueryBuilder($filter, $order)
    {
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
        $builder->from('Shopware\Models\Article\Vote', 'vote');
        $builder->join('vote.article', 'article');
        if (!empty($filter)) {
            $builder->where('article.name LIKE ?1')
                    ->setParameter(1, '%' . $filter . '%');
        }
        if ($order == null) {
            $builder->addOrderBy('vote.datum', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all articles with registered notifications
     *
     * @param null $filter
     * @param null $offset
     * @param null $limit
     * @param null $order
     * @param null $summarize
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticlesWithRegisteredNotificationsQuery($filter = null, $offset = null, $limit = null, $order = null, $summarize = null)
    {
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
     * @param $filter
     * @param $order
     * @param $summarize
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticlesWithRegisteredNotificationsBuilder($filter, $order, $summarize)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'notification.articleNumber as number',
            'COUNT(notification.articleNumber) as registered',
            '( COUNT(notification.articleNumber) ) - ( SUM(notification.send) ) as notNotified',
            'article.name as name',
        ])
                ->from('Shopware\Models\Article\Notification', 'notification')
                ->leftJoin('notification.articleDetail', 'articleDetail')
                ->leftJoin('articleDetail.article', 'article');

        if (!empty($summarize)) {
            return $builder;
        }

        $builder->groupBy('notification.articleNumber');

        //search part
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'search') {
            $builder->where('notification.articleNumber LIKE :search')
                    ->orWhere('article.name LIKE :search')
                    ->setParameter('search', $filter[0]['value']);
        }

        if ($order == null) {
            $builder->addOrderBy('registered', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all notification customers by the given articleOrderNumber
     *
     * @param $articleOrderNumber
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     *
     * @internal param $articleOrderNumber
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleOrderNumber
     * @param $filter
     * @param $order
     *
     * @internal param $articleOrderNumber
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getNotificationCustomerByArticleBuilder($articleOrderNumber, $filter, $order)
    {
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
                ->from('Shopware\Models\Article\Notification', 'notification')
                ->leftJoin('notification.customer', 'customer', 'with', 'customer.accountMode = 0 AND customer.languageId = notification.language')
                ->where('notification.articleNumber = :orderNumber')
                ->setParameter('orderNumber', $articleOrderNumber);

        //search part
        if (isset($filter[0]['property']) && $filter[0]['property'] == 'search') {
            $builder->andWhere('(
                        notification.mail LIKE :search
                        OR notification.articleNumber LIKE :search
                        OR customer.lastname LIKE :search
                        OR customer.firstname LIKE :search
                    )')
                    ->setParameter('search', $filter[0]['value']);
        }

        if ($order == null) {
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all ESD by the given articleId
     *
     * @param $articleId
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     *
     * @return \Doctrine\ORM\Query
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
     * @param $articleId
     * @param $filter
     * @param $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getEsdByArticleQueryBuilder($articleId, $filter, $order)
    {
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
        $builder->from('Shopware\Models\Article\Esd', 'esd')
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

        if ($order == null) {
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all serials by the given esdId
     *
     * @param $esdId
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     *
     * @return \Doctrine\ORM\Query
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
     * @param $esdId
     * @param $filter
     * @param $order
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSerialsByEsdQueryBuilder($esdId, $filter, $order)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select([
            'serial.id as id',
            'serial.serialnumber as serialnumber',
            'esdOrder.date as date',
            'customer.id as customerId',
            'customer.accountMode as accountMode',
            'customer.email as customerEmail',
        ])
            ->from('Shopware\Models\Article\EsdSerial', 'serial')
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

        if ($order == null) {
            $builder->addOrderBy('date', 'DESC');
        } else {
            $builder->addOrderBy($order);
        }

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all free serials by the given esdId
     *
     * @param $esdId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getFreeSerialsCountByEsdQuery($esdId)
    {
        $builder = $this->getFreeSerialsCountByEsdQueryBuilder($esdId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getFreeSerialsCountByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $esdId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFreeSerialsCountByEsdQueryBuilder($esdId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
                ->select('COUNT(serials) - COUNT(esdOrder.id) as serialsFree')
                ->from('Shopware\Models\Article\Esd', 'esd')
                ->leftJoin('esd.serials', 'serials')
                ->leftJoin('serials.esdOrder', 'esdOrder')
                ->where('esd.id = :esdId')
                ->setParameter('esdId', $esdId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects all unused serials by the given esdId
     *
     * @param $esdId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getUnusedSerialsByEsdQuery($esdId)
    {
        $builder = $this->getUnusedSerialsByEsdQueryBuilder($esdId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getUnusedSerialsByEsdQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $esdId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUnusedSerialsByEsdQueryBuilder($esdId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder()
                ->select('serial')
                ->from('Shopware\Models\Article\EsdSerial', 'serial')
                ->leftJoin('serial.esdOrder', 'esdOrder')
                ->where('serial.esd = :esdId')
                ->andWhere('esdOrder IS NULL')
                ->setParameter('esdId', $esdId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $articleId
     *
     * @internal param int $main
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleCoverImageQuery($articleId)
    {
        $builder = $this->getArticleCoverImageQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleCoverImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleCoverImageQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['images', 'attribute'])
                ->from('Shopware\Models\Article\Image', 'images')
                ->leftJoin('images.attribute', 'attribute')
                ->leftJoin('images.children', 'children')
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
     * @param $articleId
     *
     * @internal param $article
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleFallbackCoverQuery($articleId)
    {
        $builder = $this->getArticleFallbackCoverQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleFallbackCoverQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @internal param $article
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleFallbackCoverQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['images', 'attribute'])
                ->from('Shopware\Models\Article\Image', 'images')
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
     * @param $number
     * @param $offset
     * @param $limit
     *
     * @return \Doctrine\ORM\Query
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
     * @param $number
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getVariantImagesByArticleNumberQueryBuilder($number)
    {
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
            'attribute.attribute1',
            'attribute.attribute2',
            'attribute.attribute3',
        ])
            ->from('Shopware\Models\Article\Image', 'images')
            ->innerJoin('images.articleDetail', 'articleDetail')
            ->innerJoin('images.parent', 'imageParent')
            ->leftJoin('imageParent.attribute', 'attribute')
            ->where('articleDetail.number = ?1')
            ->setParameter(1, $number)
            ->orderBy('imageParent.main', 'ASC')
            ->addOrderBy('imageParent.position', 'ASC');

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which .....
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getArticleImagesQuery($articleId)
    {
        $builder = $this->getArticleImagesQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getArticleImagesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getArticleImagesQueryBuilder($articleId)
    {
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select(['images'])
                ->from('Shopware\Models\Article\Image', 'images')
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
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemovePricesQuery($articleId)
    {
        $builder = $this->getRemovePricesQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemovePricesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemovePricesQueryBuilder($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Price', 'prices')
                ->where('prices.articleId = :id')
                ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to delete attributes
     * associated with the given articleId
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveAttributesQuery($articleId)
    {
        $builder = $this->getRemoveAttributesQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveAttributesQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemoveAttributesQueryBuilder($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Attribute\Article', 'attribute')
                ->where('attribute.articleId = :id')
                ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to delete esd articles
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveESDQuery($articleId)
    {
        $builder = $this->getRemoveESDQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveESDQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemoveESDQueryBuilder($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Esd', 'esd')
                ->where('esd.articleId = :id')
                ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove translations associated
     * with the given articleId
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveArticleTranslationsQuery($articleId)
    {
        $builder = $this->getRemoveArticleTranslationsQueryBuilder($articleId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveArticleTranslationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $articleId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemoveArticleTranslationsQueryBuilder($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Translation\Translation', 'translation')
            ->where('translation.key = :id')
            ->andWhere('translation.type = \'article\'')
            ->setParameter('id', $articleId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove translations associated
     * with the given articleDetailId
     *
     * @param $detailId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveVariantTranslationsQuery($detailId)
    {
        $builder = $this->getRemoveVariantTranslationsQueryBuilder($detailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveVariantTranslationsQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $detailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemoveVariantTranslationsQueryBuilder($detailId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Translation\Translation', 'translation')
            ->where('translation.key = :id')
            ->andWhere('translation.type = \'variant\'')
            ->setParameter('id', $detailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove images associated
     * with the given articleDetailId
     *
     * @param $detailId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveImageQuery($detailId)
    {
        $builder = $this->getRemoveImageQueryBuilder($detailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveImageQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $detailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemoveImageQueryBuilder($detailId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Image', 'image')
                ->where('image.articleDetailId = :id')
                ->setParameter('id', $detailId);

        return $builder;
    }

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which allows you to remove a given article detail
     *
     * @param $detailId
     *
     * @return \Doctrine\ORM\Query
     */
    public function getRemoveDetailQuery($detailId)
    {
        $builder = $this->getRemoveDetailQueryBuilder($detailId);

        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getRemoveDetailQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $detailId
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getRemoveDetailQueryBuilder($detailId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->delete('Shopware\Models\Article\Detail', 'detail')
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
        $builder = $this->getEntityManager()->createQueryBuilder();
        $builder->select([
            'variants',
            'attribute',
            'prices',
            'customerGroup',
            'options',
            'images',
        ]);

        $builder->from('Shopware\Models\Article\Detail', 'variants')
            ->innerJoin('variants.article', 'article')
            ->leftJoin('variants.attribute', 'attribute')
            ->leftJoin('variants.images', 'images')
            ->leftJoin('variants.prices', 'prices')
            ->innerJoin('prices.customerGroup', 'customerGroup')
            ->leftJoin('variants.configuratorOptions', 'options');

        return $builder;
    }
}
