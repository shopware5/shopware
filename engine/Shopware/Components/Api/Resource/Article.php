<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

use Shopware\Components\Api\Exception as ApiException;
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article as ArticleModel;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Media;
use Symfony\Component\HttpFoundation\File\File;
use Shopware\Models\Article\Configurator;


/**
 * Article API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Article extends Resource
{
    /**
     * @return \Shopware\Models\Article\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Article\Article');
    }

    /**
     * @return \Shopware\Models\Article\Repository
     */
    public function getDetailRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Article\Detail');
    }

    /**
     * @return Variant
     */
    public function getVariantResource()
    {
        return $this->getResource('Variant');
    }

    /**
     * Little helper function for the ...ByNumber methods
     * @param $number
     * @return int
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $articleDetail = $this->getDetailRepository()->findOneBy(array('number' => $number));

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Article by number {$number} not found");
        }

        return $articleDetail
            ->getArticle()
            ->getId();
    }

    /**
     * Convenience method to get a article by number
     * @param string $number
     * @return array|\Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOneByNumber($number)
    {
        $id = $this->getIdFromNumber($number);
        return $this->getOne($id);
    }

    /**
     * @param int $id
     * @return array|\Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array(
            'article',
            'mainDetail',
            'mainDetailPrices',
            'tax',
            'propertyValues',
            'supplier',
            'mainDetailAttribute',
            'propertyGroup',
            'customerGroups'
        ))
            ->from('Shopware\Models\Article\Article', 'article')
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('mainDetail.prices', 'mainDetailPrices')
            ->leftJoin('article.tax', 'tax')
            ->leftJoin('article.propertyValues', 'propertyValues')
            ->leftJoin('article.supplier', 'supplier')
            ->leftJoin('mainDetail.attribute', 'mainDetailAttribute')
            ->leftJoin('article.propertyGroup', 'propertyGroup')
            ->leftJoin('article.customerGroups', 'customerGroups')
            ->where('article.id = ?1')
            ->setParameter(1, $id);

        /** @var $article \Shopware\Models\Article\Article */
        $article = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $id not found");
        }

        if ($this->getResultMode() == self::HYDRATE_ARRAY) {
            $article['images'] = $this->getArticleImages($id);
            $article['configuratorSet'] = $this->getArticleConfiguratorSet($id);
            $article['links'] = $this->getArticleLinks($id);
            $article['downloads'] = $this->getArticleDownloads($id);
            $article['categories'] = $this->getArticleCategories($id);
            $article['similar'] = $this->getArticleSimilar($id);
            $article['related'] = $this->getArticleRelated($id);
            $article['details'] = $this->getArticleVariants($id);

            $query = $this->getManager()->createQuery('SELECT shop FROM Shopware\Models\Shop\Shop as shop');
            $shops = $query->getArrayResult();

            $translationReader = new \Shopware_Components_Translation();
            foreach ($shops as $shop) {
                $translation = $translationReader->read($shop['id'], 'article', $id);
                if (!empty($translation)) {
                    $translation['shopId'] = $shop['id'];
                    $article['translations'][$shop['id']] = $translation;
                }
            }
        }

        return $article;
    }

    /**
     * Selects the configured article configurator set and the assigned
     * configurator groups of the set.
     * The groups are sorted by the position value.
     *
     * @param $articleId
     * @return mixed
     */
    protected function getArticleConfiguratorSet($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('configuratorSet', 'groups'))
            ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
            ->innerJoin('configuratorSet.articles', 'article')
            ->leftJoin('configuratorSet.groups', 'groups')
            ->addOrderBy('groups.position', 'ASC')
            ->where('article.id = :articleId')
            ->setParameters(array('articleId' => $articleId));

        return $this->getSingleResult($builder);
    }

    /**
     * Selects all images of the main variant of the passed article id.
     * The images are sorted by their position value.
     *
     * @param $articleId
     * @return array
     */
    protected function getArticleImages($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('images'))
            ->from('Shopware\Models\Article\Image', 'images')
            ->innerJoin('images.article', 'article')
            ->where('article.id = :articleId')
            ->orderBy('images.position', 'ASC')
            ->andWhere('images.parentId IS NULL')
            ->setParameters(array('articleId' => $articleId));

        return $this->getFullResult($builder);
    }

    /**
     * Selects all configured download files for the passed article id.
     *
     * @param $articleId
     * @return array
     */
    protected function getArticleDownloads($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('downloads'))
            ->from('Shopware\Models\Article\Download', 'downloads')
            ->innerJoin('downloads.article', 'article')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function which selects all configured links
     * for the passed article id.
     *
     * @param $articleId
     * @return array
     */
    protected function getArticleLinks($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('links'))
            ->from('Shopware\Models\Article\Link', 'links')
            ->innerJoin('links.article', 'article')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function which selects all categories of the passed
     * article id.
     * This function returns only the directly assigned categories.
     * To prevent a big data, this function selects only the category name and id.
     *
     * @param $articleId
     * @return array
     */
    protected function getArticleCategories($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('categories.id', 'categories.name'))
            ->from('Shopware\Models\Category\Category', 'categories', 'categories.id')
            ->innerJoin('categories.articles', 'articles')
            ->where('articles.id = :articleId')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function which selects all similar articles
     * of the passed article id.
     *
     * @param $articleId
     * @return mixed
     */
    protected function getArticleSimilar($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('article', 'PARTIAL similar.{id, name}'))
            ->from('Shopware\Models\Article\Article', 'article')
            ->innerJoin('article.similar', 'similar')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $article = $this->getSingleResult($builder);
        return $article['similar'];
    }

    /**
     * Helper function which selects all accessory articles
     * of the passed article id.
     *
     * @param $articleId
     * @return mixed
     */
    protected function getArticleRelated($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('article', 'PARTIAL related.{id, name}'))
            ->from('Shopware\Models\Article\Article', 'article')
            ->innerJoin('article.related', 'related')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $article = $this->getSingleResult($builder);
        return $article['related'];
    }

    /**
     * Helper function which loads all non main variants of
     * the passed article id.
     * Additionally the function selects the variant prices
     * and configurator options for each variant.
     *
     * @param $articleId
     * @return array
     */
    protected function getArticleVariants($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array('variants', 'prices', 'options'))
            ->from('Shopware\Models\Article\Detail', 'variants')
            ->innerJoin('variants.article', 'article')
            ->leftJoin('variants.prices', 'prices')
            ->leftJoin('variants.configuratorOptions', 'options')
            ->where('article.id = :articleId')
            ->andWhere('variants.kind = 2')
            ->setParameter('articleId', $articleId);

        return $this->getFullResult($builder);
    }

    /**
     * Helper function to prevent duplicate source code
     * to get a single row of the query builder result for the current resource result mode
     * using the query paginator.
     *
     * @param QueryBuilder $builder
     * @return array
     */
    private function getSingleResult(QueryBuilder $builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());
        $paginator = $this->getManager()->createPaginator($query);
        return $paginator->getIterator()->current();
    }

    /**
     * Helper function to prevent duplicate source code
     * to get the full query builder result for the current resource result mode
     * using the query paginator.
     *
     * @param QueryBuilder $builder
     * @return array
     */
    private function getFullResult(QueryBuilder $builder)
    {
        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());
        $paginator = $this->getManager()->createPaginator($query);
        return $paginator->getIterator()->getArrayCopy();
    }

    /**
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('article')
            ->leftJoin('article.mainDetail', 'mainDetail');

        $builder->addFilter($criteria)
            ->addOrderBy($orderBy)
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the article data
        $articles = $paginator->getIterator()->getArrayCopy();

        return array('data' => $articles, 'total' => $totalResult);
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ValidationException
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $article = new ArticleModel();

        $translations = array();
        if (!empty($params['translations'])) {
            $translations = $params['translations'];
            unset($params['translations']);
        }

        $params = $this->prepareAssociatedData($params, $article);

        $article->fromArray($params);

        $violations = $this->getManager()->validate($article);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($article);
        $this->flush();

        if (!empty($translations)) {
            $this->writeTranslations($article->getId(), $translations);
        }

        return $article;
    }

    /**
     * Convenience method to update a article by number
     * @param string $number
     * @param array $params
     * @return \Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function updateByNumber($number, array $params)
    {
        $id = $this->getIdFromNumber($number);
        return $this->update($id, $params);
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(array(
            'article',
            'mainDetail',
            'mainDetailPrices',
            'mainDetailAttribute',
            'tax',
            'supplier',
        ))
            ->from('Shopware\Models\Article\Article', 'article')
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('mainDetail.prices', 'mainDetailPrices')
            ->leftJoin('article.tax', 'tax')
            ->leftJoin('article.supplier', 'supplier')
            ->leftJoin('mainDetail.attribute', 'mainDetailAttribute')
            ->where('article.id = ?1')
            ->setParameter(1, $id);

        /** @var $article \Shopware\Models\Article\Article */
        $article = $builder->getQuery()->getOneOrNullResult(self::HYDRATE_OBJECT);


        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $id not found");
        }

        $translations = array();
        if (!empty($params['translations'])) {
            $translations = $params['translations'];
            unset($params['translations']);
        }

        $params = $this->prepareAssociatedData($params, $article);

        $article->fromArray($params);
        $violations = $this->getManager()->validate($article);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        if (!empty($translations)) {
            $this->writeTranslations($article->getId(), $translations);
        }

        return $article;
    }

    /**
     * convenience function to delete a article by number
     * @param string $number
     * @return \Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Exception
     */
    public function deleteByNumber($number)
    {
        throw new \Exception("Deleting articles by number isn't possible, yet.");
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Article\Article
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $article \Shopware\Models\Article\Article */
        $article = $this->getRepository()->find($id);

        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $id not found");
        }

        // Delete associated data
        $query = $this->getRepository()->getRemovePricesQuery($article->getId());
        $query->execute();
        $query = $this->getRepository()->getRemoveAttributesQuery($article->getId());
        $query->execute();
        $query = $this->getRepository()->getRemoveESDQuery($article->getId());
        $query->execute();
        $this->removeArticleDetails($article);


        $this->getManager()->remove($article);
        $this->flush();

        return $article;
    }

    /**
     * Helper function to remove article details for a given article
     * @param $article \Shopware\Models\Article\Article
     */
    protected function removeArticleDetails($article)
    {
        $sql = "SELECT id FROM s_articles_details WHERE articleID = ? AND kind != 1";
        $details = Shopware()->Db()->fetchAll($sql, array($article->getId()));

        foreach ($details as $detail) {
            $query = $this->getRepository()->getRemoveImageQuery($detail['id']);
            $query->execute();

            $sql = "DELETE FROM s_article_configurator_option_relations WHERE article_id = ?";
            Shopware()->Db()->query($sql, array($detail['id']));

            $query = $this->getRepository()->getRemoveDetailQuery($detail['id']);
            $query->execute();
        }
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @return array
     */
    protected function prepareAssociatedData($data, ArticleModel $article)
    {
        $data = $this->prepareArticleAssociatedData($data, $article);
        $data = $this->prepareCategoryAssociatedData($data, $article);
        $data = $this->prepareRelatedAssociatedData($data, $article);
        $data = $this->prepareSimilarAssociatedData($data, $article);
        $data = $this->prepareAvoidCustomerGroups($data, $article);
        $data = $this->preparePropertyValuesData($data, $article);
        $data = $this->prepareImageAssociatedData($data, $article);
        $data = $this->prepareDownloadsAssociatedData($data, $article);
        $data = $this->prepareConfiguratorSet($data, $article);


        //need to set the tax data directly for following price calculations which use the tax object of the article
        if (isset($data['tax'])) {
            $article->setTax($data['tax']);
        }

        if (isset($data['configuratorSet'])) {
            $article->setConfiguratorSet($data['configuratorSet']);
        }

        $data = $this->prepareAttributeAssociatedData($data, $article);
        $data = $this->prepareMainDetail($data, $article);
        $data = $this->prepareVariants($data, $article);

        return $data;
    }

    /**
     * Helper function which converts the passed data for the main variant of the article.
     * @param array $data
     * @param ArticleModel $article
     * @return array
     */
    public function prepareMainDetail(array $data, ArticleModel $article)
    {
        $detail = $article->getMainDetail();
        if (!$detail) {
            $detail = new Detail();
            $detail->setKind(1);
            $detail->setArticle($article);
            $article->setMainDetail($detail);
        }

        $mainData = $data['mainDetail'];
        $newData = $this->getVariantResource()->prepareMainVariantData($mainData, $article, $detail);
        $data['mainDetail'] = $newData;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareVariants($data, ArticleModel $article)
    {
        unset($data['details']);

        if (!isset($data['variants'])) {
            return $data;
        }

        $setFirstVariantMain = false;
        // delete old main, if it has no configurator options
        // and if non of the following variants has the mainDetail's number
        $oldMainDetail = $article->getMainDetail();

        if (isset($data['__options_variants']) && $data['__options_variants']['replace']) {
            $this->removeArticleDetails($article);
        }


        if ($oldMainDetail) {
            $mainDetailGetsConfigurator = false;
            foreach ($data['variants'] as $variantData) {
                if (isset($variantData['configuratorOptions']) && is_array($variantData['configuratorOptions'])) {
                    $mainDetailGetsConfigurator = true;
                }
            }

            if (!$mainDetailGetsConfigurator && count($oldMainDetail->getConfiguratorOptions()) === 0) {
                $this->getManager()->remove($oldMainDetail);
                $setFirstVariantMain = true;
            }
        }

        // if the mainDetail was deleted, set the first variant as mainDetail
        // if another variant has set isMain to true, this variant will become
        // a usual variant again
        if ($setFirstVariantMain) {
            $data['variants']['isMain'] = true;
        }

        $variants = array();
        if (isset($data['__options_variants']) && $data['__options_variants']['replace']) {
            $this->removeArticleDetails($article);
        }

        foreach ($data['variants'] as $variantData) {

            if (isset($variantData['id'])) {
                $variant = $this->getVariantResource()->internalUpdate($variantData['id'], $variantData, $article);
            } else {
                $variant = null;

                //the number property can be set for two reasons.
                //1. Use the number as identifier to update an existing variant
                //2. Use this number for the new variant
                if (isset($variantData['number'])) {
                    $variant = $this->getManager()->getRepository('Shopware\Models\Article\Detail')->findOneBy(array(
                        'number' => $variantData['number'],
                        'articleId' => $article->getId()
                    ));
                }

                //if the variant was found over the number, update the existing
                if ($variant) {
                    $variant = $this->getVariantResource()->internalUpdate($variant->getId(), $variantData, $article);
                } else {
                    //otherwise the number passed to use as order number for the new variant
                    $variant = $this->getVariantResource()->internalCreate($variantData, $article);
                }
            }

            if ($variantData['isMain'] || $variantData['standard']) {
                $newMain = $variant;
                $newMain->setKind(1);

                // Check for old main articles:
                // If old main article has configurator options, use it as a usual variant
                // if the old main article does not have any configurator options, delete it
                if (isset($data['mainDetail'])) {
                    $oldMain = $data['mainDetail'];


                    if ($oldMain instanceof Detail) {
                        $oldMain->setKind(2);
                        if ($oldMain->getNumber() && $oldMain->getConfiguratorOptions()) {
                            $variant = $oldMain;
                        } else {
                            $this->getManager()->remove($oldMain);
                        }
                    } else {
                        $oldMain['kind'] = 2;
                        if (!empty($oldMain['number']) && !empty($oldMain['configuratorOptions'])) {
                            $variant = $oldMain;
                        } elseif (!empty($oldMain['number'])) {
                            $oldMain = $this->getDetailRepository()->findOneBy(array('number' => $oldMain['number']));
                            if ($oldMain) {
                                $this->getManager()->remove($oldMain);
                            }
                        }
                    }

                }

                $data['mainDetail'] = $newMain;
            }

            $variants[] = $variant;
        }
        
//        echo '<pre>';
//        \Doctrine\Common\Util\Debug::dump($variants, 2);
//        exit();

        $data['details'] = $variants;
        unset($data['variants']);

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Exception
     * @return array
     */
    protected function prepareConfiguratorSet($data, ArticleModel $article)
    {
        if (!isset($data['configuratorSet'])) {
            return $data;
        }

        $configuratorSet = $article->getConfiguratorSet();
        if (!$configuratorSet) {
            $configuratorSet = new Configurator\Set();
            if (isset($data['mainDetail']['number'])) {
                $number = $data['mainDetail']['number'];
            } else {
                $number = $article->getMainDetail()->getNumber();
            }

            $configuratorSet->setName('Set-' . $number);
            $configuratorSet->setPublic(false);
        }

        if (isset($data['configuratorSet']['type'])) {
            $configuratorSet->setType($data['configuratorSet']['type']);
        }

        if (isset($data['configuratorSet']['name'])) {
            $configuratorSet->setName($data['configuratorSet']['name']);
        }

        $allOptions = array();
        $allGroups = array();

        $groupPosition = 0;

        foreach ($data['configuratorSet']['groups'] as $groupData) {
            $group = null;
            if (isset($groupData['id'])) {
                $group = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Group')->find($groupData['id']);
                if (!$group) {
                    throw new ApiException\CustomValidationException(sprintf("ConfiguratorGroup by id %s not found", $groupData['id']));
                }
            } elseif (isset($groupData['name'])) {
                $group = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Group')->findOneBy(array('name' => $groupData['name']));

                if (!$group) {
                    $group = new Configurator\Group();
                    $group->setPosition($groupPosition);
                }
            } else {
                throw new ApiException\CustomValidationException('At least the groupname is required');
            }

            $groupOptions = array();
            $optionPosition = 0;
            foreach ($groupData['options'] as $optionData) {
                $option = null;
                if ($group->getId() > 0) {
                    if (isset($optionData['id'])) {
                        $option = $this->getManager()->find('Shopware\Models\Article\Configurator\Option', $optionData['id']);
                        if (!$option) {
                            throw new ApiException\CustomValidationException(sprintf("ConfiguratorOption by id %s not found", $optionData['id']));
                        }
                    } else {
                        $option = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Option')->findOneBy(array(
                            'name' => $optionData['name'],
                            'groupId' => $group->getId()
                        ));
                    }
                }

                if (!$option) {
                    $option = new Configurator\Option();
                }

                $option->fromArray($optionData);
                $option->setGroup($group);
                $option->setPosition($optionPosition++);
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
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareArticleAssociatedData($data, ArticleModel $article)
    {
        //check if a tax id is passed and load the tax model or set the tax parameter to null.
        if (!empty($data['taxId'])) {
            $data['tax'] = $this->getManager()->find('Shopware\Models\Tax\Tax', $data['taxId']);

            if (empty($data['tax'])) {
                throw new ApiException\CustomValidationException(sprintf("Tax by id %s not found", $data['taxId']));
            }

        } elseif (!empty($data['tax'])) {
            $tax = $this->getManager()->getRepository('Shopware\Models\Tax\Tax')->findOneBy(array('tax' => $data['tax']));
            if (!$tax) {
                throw new ApiException\CustomValidationException(sprintf("Tax by taxrate %s not found", $data['tax']));
            }
            $data['tax'] = $tax;
        } else {
            unset($data['tax']);
        }

        //check if a supplier id is passed and load the supplier model or set the supplier parameter to null.
        if (!empty($data['supplierId'])) {
            $data['supplier'] = $this->getManager()->find('Shopware\Models\Article\Supplier', $data['supplierId']);
            if (empty($data['supplier'])) {
                throw new ApiException\CustomValidationException(sprintf("Supplier by id %s not found", $data['supplierId']));
            }
        } elseif (!empty($data['supplier'])) {
            $supplier = $this->getManager()->getRepository('Shopware\Models\Article\Supplier')->findOneBy(array('name' => $data['supplier']));
            if (!$supplier) {
                $supplier = new \Shopware\Models\Article\Supplier();
                $supplier->setName($data['supplier']);
            }
            $data['supplier'] = $supplier;
        } else {
            unset($data['supplier']);
        }

        //check if a priceGroup id is passed and load the priceGroup model or set the priceGroup parameter to null.
        if (isset($data['priceGroupId'])) {
            if (empty($data['priceGroupId'])) {
                $data['priceGroupId'] = null;
            } else {
                $data['priceGroup'] = $this->getManager()->find('Shopware\Models\Price\Group', $data['priceGroupId']);
                if (empty($data['priceGroup'])) {
                    throw new ApiException\CustomValidationException(sprintf("Pricegroup by id %s not found", $data['priceGroupId']));
                }
            }
        } else {
            unset($data['priceGroup']);
        }

        //check if a propertyGroup is passed and load the propertyGroup model or set the propertyGroup parameter to null.
        if (isset($data['filterGroupId'])) {
            if (empty($data['filterGroupId'])) {
                $data['propertyGroup'] = null;
            } else {
                $data['propertyGroup'] = $this->getManager()->find('\Shopware\Models\Property\Group', $data['filterGroupId']);

                if (empty($data['propertyGroup'])) {
                    throw new ApiException\CustomValidationException(sprintf("PropertyGroup by id %s not found", $data['filterGroupId']));
                }
            }
        } else {
            unset($data['propertyGroup']);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareAttributeAssociatedData($data, ArticleModel $article)
    {
        if (isset($data['attribute']) && !isset($data['mainDetail']['attribute'])) {
            $data['mainDetail']['attribute'] = $data['attribute'];
        }
        unset($data['attribute']);
        if (isset($data['mainDetail']['attribute']['articleDetailId'])) {
            unset($data['mainDetail']['attribute']['articleDetailId']);
        }
        $data['mainDetail']['attribute']['article'] = $article;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareCategoryAssociatedData($data, ArticleModel $article)
    {
        if (!isset($data['categories'])) {
            return $data;
        }

        $categories = $this->checkDataReplacement(
            $article->getCategories(),
            $data,
            'categories',
            true
        );

        foreach ($data['categories'] as $categoryData) {

            $this->getManyToManySubElement(
                $categories,
                $categoryData,
                '\Shopware\Models\Category\Category'
            );
        }

        $data['categories'] = $categories;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareAvoidCustomerGroups($data, ArticleModel $article)
    {
        if (!isset($data['customerGroups'])) {
            return $data;
        }

        $customerGroups = $this->checkDataReplacement($article->getCustomerGroups(), $data, 'customerGroups', true);

        foreach ($data['customerGroups'] as $customerGroupData) {
            $this->getManyToManySubElement(
                $customerGroups,
                $customerGroupData,
                '\Shopware\Models\Customer\Group'
            );
        }

        $data['customerGroups'] = $customerGroups;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareRelatedAssociatedData($data, ArticleModel $article)
    {
        if (!isset($data['related'])) {
            return $data;
        }

        $related = $this->checkDataReplacement($article->getRelated(), $data, 'related', true);

        foreach ($data['related'] as $relatedData) {
            if (empty($relatedData['number']) && empty($relatedData['id'])) {
                continue;
            }

            /**@var $relatedArticle Detail */
            $relatedArticle = $this->getManyToManySubElement(
                $related,
                $relatedData,
                '\Shopware\Models\Article\Detail',
                array('number')
            );

            if ($relatedArticle) {
                $relatedArticle = $relatedArticle->getArticle();
            } else {
                $relatedArticle = $this->getManyToManySubElement(
                    $related,
                    $relatedData,
                    '\Shopware\Models\Article\Article'
                );
            }

            //no valid entity found, throw exception!
            if (!$relatedArticle) {
                $property = $relatedData['number'] ? $relatedData['number'] : $relatedData['id'];
                throw new ApiException\CustomValidationException(
                    sprintf("Related Article by number/id %s not found", $property)
                );
            }

            /**@var $relatedArticle ArticleModel */
            if ($relatedData['cross']) {
                $relatedArticle->getRelated()->add($article);
            }
        }

        $data['related'] = $related;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareSimilarAssociatedData($data, ArticleModel $article)
    {
        if (!isset($data['similar'])) {
            return $data;
        }

        $similar = $this->checkDataReplacement($article->getSimilar(), $data, 'similar', true);

        foreach ($data['similar'] as $similarData) {
            if (empty($similarData['number']) && empty($similarData['id'])) {
                continue;
            }

            $similarArticle = $this->getManyToManySubElement(
                $similar,
                $similarData,
                '\Shopware\Models\Article\Detail',
                array('number')
            );

            if ($similarArticle) {
                /**@var $similarArticle Detail */
                $similarArticle = $similarArticle->getArticle();
            } else {
                $similarArticle = $this->getManyToManySubElement(
                    $similar,
                    $similarData,
                    '\Shopware\Models\Article\Article'
                );
            }

            //no valid entity found, throw exception!
            if (!$similarArticle) {
                $property = $similarData['number'] ? $similarData['number'] : $similarData['id'];
                throw new ApiException\CustomValidationException(
                    sprintf("Similar Article by number/id %s not found", $property)
                );
            }

            /**@var $similarArticle ArticleModel */
            if ($similarData['cross']) {
                $similarArticle->getSimilar()->add($article);
            }
        }

        $data['similar'] = $similar;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function preparePropertyValuesData($data, ArticleModel $article)
    {
        if (!isset($data['propertyValues'])) {
            return $data;
        }

        // remove assigned values
        if (empty($data['propertyValues'])) {
            return $data;
        }

        $propertyRepository = $this->getManager()->getRepository('Shopware\Models\Property\Group');

        /**
         *  Get group - this is required.
         */
        if (isset($data['propertyGroup'])) {
            $propertyGroup = $data['propertyGroup'];
        } else {
            $propertyGroup = $article->getPropertyGroup();
        }

        if (!$propertyGroup instanceof \Shopware\Models\Property\Group) {
            throw new ApiException\CustomValidationException(sprintf("There is no propertyGroup specified"));
        }

        $models = array();

        foreach ($data['propertyValues'] as $valueData) {
            $value = null;
            /** @var \Shopware\Models\Property\Option $option */
            $option = null;

            // Get value by id
            if (isset($valueData['id'])) {
                $value = $this->getManager()->getRepository('\Shopware\Models\Property\Value')->find($valueData['id']);
                if (!$value) {
                    throw new ApiException\CustomValidationException(sprintf("Property value by id %s not found", $valueData['id']));
                }
                // Get / create value by name
            } elseif (isset($valueData['value'])) {
                //get option
                if (isset($valueData['option'])) {
                    // get option by id
                    if (isset($valueData['option']['id'])) {
                        $option = $this->getManager()->getRepository('\Shopware\Models\Property\Option')->find($valueData['option']['id']);
                        if (!$option) {
                            throw new ApiException\CustomValidationException(sprintf("Property option by id %s not found", $valueData['option']['id']));
                        }
                        $filters = array(
                            array('property' => "options.id", 'expression' => '=', 'value' => $option->getId()),
                            array('property' => "groups.id", 'expression' => '=', 'value' => $propertyGroup->getId()),
                        );
                        $query = $propertyRepository->getPropertyRelationQuery($filters, null, 1, 0);
                        /** @var \Shopware\Models\Property\Relation $relation */
                        $relation = $query->getOneOrNullResult(self::HYDRATE_OBJECT);
                        if (!$relation) {
                            $propertyGroup->addOption($option);
                        }
                        // get/create option depending on associated filtergroups
                    } elseif (isset($valueData['option']['name'])) {
                        // if a name is passed and there is a matching option/group relation, get this option
                        // if only a name is passed, create a new option
                        $filters = array(
                            array('property' => "options.name", 'expression' => '=', 'value' => $valueData['option']['name']),
                            array('property' => "groups.name", 'expression' => '=', 'value' => $propertyGroup->getName()),
                        );
                        $query = $propertyRepository->getPropertyRelationQuery($filters, null, 1, 0);
                        /** @var \Shopware\Models\Property\Relation $relation */
                        $relation = $query->getOneOrNullResult(self::HYDRATE_OBJECT);
                        if (!$relation) {
                            $option = new \Shopware\Models\Property\Option();
                            $propertyGroup->addOption($option);
                        } else {
                            $option = $relation->getOption();
                        }
                    } else {
                        throw new ApiException\CustomValidationException("A property option need to be given for each property value");
                    }
                    $option->fromArray($valueData['option']);
                    if ($option->isFilterable() === null) {
                        $option->setFilterable(false);
                    }
                } else {
                    throw new ApiException\CustomValidationException("A property option need to be given for each property value");
                }
                // create the value
                // If there is a filter value with matching name and option, load this value, else create a new one
                $value = $this->getManager()->getRepository('\Shopware\Models\Property\Value')->findOneBy(array(
                    'value' => $valueData['value'],
                    'optionId' => $option->getId()
                ));
                if (!$value) {
                    $value = new \Shopware\Models\Property\Value($option, $valueData['value']);
                }
                if (isset($valueData['position'])) {
                    $value->setPosition($valueData['position']);
                }
                $this->getManager()->persist($value);
            } else {
                throw new ApiException\CustomValidationException("Name or id for property value required");
            }
            $models[] = $value;
        }

        $data['propertyValues'] = $models;
        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    private function prepareDownloadsAssociatedData($data, ArticleModel $article)
    {
        if (!isset($data['downloads'])) {
            return $data;
        }

        $downloads = $this->checkDataReplacement($article->getDownloads(), $data, 'downloads', true);

        foreach ($data['downloads'] as &$downloadData) {

            $download = $this->getOneToManySubElement(
                $downloads,
                $downloadData,
                '\Shopware\Models\Article\Download'
            );


            if (isset($downloadData['link'])) {
                $path = $this->load($downloadData['link']);
                $file = new File($path);

                $media = new Media();
                $media->setAlbumId(-6);
                $media->setAlbum($this->getManager()->find('Shopware\Models\Media\Album', -6));

                $media->setFile($file);
                if (isset($downloadData['name']) && !empty($downloadData['name'])) {
                    $media->setDescription($downloadData['name']);
                } else {
                    $media->setDescription('');
                }
                $media->setCreated(new \DateTime());
                $media->setUserId(0);

                try { //persist the model into the model manager
                    $this->getManager()->persist($media);
//                    $this->getManager()->flush($media);
                } catch (\Doctrine\ORM\ORMException $e) {
                    throw new ApiException\CustomValidationException(sprintf("Some error occured while loading your image"));
                }

                $download->setFile($media->getPath());
                $download->setName($media->getName());
                $download->setSize($media->getFileSize());
            }

            $download->fromArray($downloadData);
        }
        $data['downloads'] = $downloads;

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    private function prepareImageAssociatedData($data, ArticleModel $article)
    {
        if (!isset($data['images'])) {
            return $data;
        }

        // remove assigned images
        if (empty($data['images'])) {
            $images = $article->getImages();
            $images->clear();
            unset($data['images']);
            return $data;
        }

        $position = 1;
        $images = $this->checkDataReplacement($article->getImages(), $data, 'images', false);

        foreach ($data['images'] as &$imageData) {
            $image = $this->getOneToManySubElement(
                $images,
                $imageData,
                '\Shopware\Models\Article\Image'
            );

            if (isset($imageData['link'])) {
                $name = pathinfo($imageData['link'], PATHINFO_FILENAME);
                $path = $this->load($imageData['link'], $name);
                $name = pathinfo($path, PATHINFO_FILENAME);

                $file = new File($path);

                $media = new Media();
                $media->setAlbumId(-1);
                $media->setAlbum($this->getManager()->find('Shopware\Models\Media\Album', -1));

                $media->setFile($file);
                $media->setName($name);
                $media->setDescription('');
                $media->setCreated(new \DateTime());
                $media->setUserId(0);

                try {
                    //persist the model into the model manager this uploads and resizes the image
                    $this->getManager()->persist($media);
//                    $this->getManager()->flush($media);
                } catch (\Doctrine\ORM\ORMException $e) {
                    throw new ApiException\CustomValidationException(sprintf("Some error occurred while loading your image"));
                }

                $image->setMain(2);
                $image->setMedia($media);
                $image->setPosition($position);
                $image->setArticle($article);
                $position++;
                $image->setPath($media->getName());
                $image->setExtension($media->getExtension());
            } else if (!empty($imageData['mediaId'])) {
                $media = $this->getManager()->find('Shopware\Models\Media\Media', (int)$imageData['mediaId']);
                if (!($media instanceof Media)) {
                    throw new ApiException\CustomValidationException(sprintf("Media by mediaId %s not found", $imageData['mediaId']));
                }
                $image->setPath($media->getName());
                $image->setExtension($media->getExtension());
                $image->setDescription($media->getDescription());
                $image->setArticle($article);
                $image->setMedia($media);
            }

            $image->fromArray($imageData);

            // if image is set as main set other images to secondary
            if ($image->getMain() == 1) {
                /** @var $otherImage Image */
                foreach ($images as $otherImage) {
                    //only update existing images which are not the current processed image.
                    //otherwise the main flag won't be changed.
                    if ($otherImage->getId() !== $image->getId()) {
                        $otherImage->setMain(2);
                    }
                }
            }

            $images->add($image);
        }

        $hasMain = false;

        /** @var $image Image */
        foreach ($images as $image) {
            if ($image->getMain() == 1) {
                $hasMain = true;
                break;
            }
        }

        if (!$hasMain) {
            $image = $images->get(0);
            $image->setMain(1);
        }

        unset($data['images']);

        return $data;
    }

    /**
     * @param integer $articleId
     * @param array $translations
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function writeTranslations($articleId, $translations)
    {
        $whitelist = $this->getAttributeProperties();
        $whitelist = array_merge($whitelist, array(
            'name',
            'description',
            'descriptionLong',
            'keywords',
            'packUnit'
        ));

        $translationWriter = new \Shopware_Components_Translation();
        foreach ($translations as $translation) {
            $shop = $this->getManager()->find('Shopware\Models\Shop\Shop', $translation['shopId']);
            if (!$shop) {
                throw new ApiException\CustomValidationException(sprintf("Shop by id %s not found", $translation['shopId']));
            }

            $data = array_intersect_key($translation, array_flip($whitelist));
            $translationWriter->write($shop->getId(), 'article', $articleId, $data);
        }
    }

    /**
     * Returns all none association property of the article class.
     * @return array
     */
    private function getAttributeProperties()
    {
        $metaData = $this->getManager()->getClassMetadata('\Shopware\Models\Attribute\Article');
        $properties = array();

        foreach ($metaData->getReflectionProperties() as $property) {
            if ($metaData->hasAssociation($property->getName())) {
                continue;
            }
            $properties[$property->getName()] = $property->getName();
        }

        foreach ($metaData->getAssociationMappings() as $property => $mapping) {
            $name = $metaData->getSingleAssociationJoinColumnName($property);
            $field = $metaData->getFieldForColumn($name);
            unset($properties[$field]);
        }

        foreach ($metaData->getIdentifierFieldNames() as $property) {
            unset($properties[$property]);
        }

        return array_values($properties);
    }

    /**
     * @param string $url URL of the resource that should be loaded (ftp, http, file)
     * @param string $baseFilename Optional: Instead of creating a hash, create a filename based on the given one
     * @return bool|string returns the absolute path of the downloaded file
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function load($url, $baseFilename = null)
    {
        $destPath = Shopware()->DocPath('media_' . 'temp');
        if (!is_dir($destPath)) {
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);

        if (!file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not exist.", $destPath)
            );
        } elseif (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not have write permissions.", $destPath)
            );
        }

        if (strpos($url, 'data:image') !== false) {
            return $this->uploadBase64File(
                $url,
                $destPath,
                $baseFilename
            );
        }

        $urlArray = parse_url($url);
        $urlArray['path'] = explode("/", $urlArray['path']);
        switch ($urlArray['scheme']) {
            case "ftp":
            case "http":
            case "https":
            case "file":
                $filename = $this->getUniqueFileName($destPath, $baseFilename);

                if (!$put_handle = fopen("$destPath/$filename", "w+")) {
                    throw new \Exception("Could not open $destPath/$filename for writing");
                }

                if (!$get_handle = fopen($url, "r")) {
                    throw new \Exception("Could not open $url for reading");
                }
                while (!feof($get_handle)) {
                    fwrite($put_handle, fgets($get_handle, 4096));
                }
                fclose($get_handle);
                fclose($put_handle);

                return "$destPath/$filename";
        }
        throw new \InvalidArgumentException(
            sprintf("Unsupported schema '%s'.", $urlArray['scheme'])
        );
    }

    /**
     * Helper function which downloads the passed image url
     * and save the image with a unique file name in the destination path.
     * If the passed baseFilename already exists in the destination path,
     * the function creates a unique file name.
     *
     * @param $url
     * @param $destinationPath
     * @param $baseFilename
     * @return string
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Exception
     */
    protected function uploadBase64File($url, $destinationPath, $baseFilename)
    {
        if (!$get_handle = fopen($url, "r")) {
            throw new \Exception("Could not open $url for reading");
        }

        $meta = stream_get_meta_data($get_handle);
        if (!strpos($meta['mediatype'], 'image/') === false) {
            throw new ApiException\CustomValidationException('No valid media type passed for the article image : ' . $url);
        }

        $extension = str_replace('image/', '', $meta['mediatype']);
        $filename = $this->getUniqueFileName($destinationPath, $baseFilename);
        $filename .= '.' . $extension;

        if (!$put_handle = fopen("$destinationPath/$filename", "w+")) {
            throw new \Exception("Could not open $destinationPath/$filename for writing");
        }
        while (!feof($get_handle)) {
            fwrite($put_handle, fgets($get_handle, 4096));
        }
        fclose($get_handle);
        fclose($put_handle);

        return "$destinationPath/$filename";
    }

    /**
     * Helper function to get a unique file name for the passed destination path.
     * @param $destPath
     * @param null $baseFileName
     * @return null|string
     */
    private function getUniqueFileName($destPath, $baseFileName = null)
    {
        $counter = 1;
        if ($baseFileName === null) {
            $filename = md5(uniqid(rand(), true));
        } else {
            $filename = $baseFileName;
        }

        $filename = substr($filename, 0, 50);

        while (file_exists("$destPath/$filename")) {
            if ($baseFileName) {
                $filename = "$counter-$baseFileName";
                $counter++;
            } else {
                $filename = md5(uniqid(rand(), true));
            }
            $filename = substr($filename, 0, 50);
        }

        return $filename;
    }
}
