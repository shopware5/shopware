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

use Shopware\Components\Api\Exception as ApiException;
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article as ArticleModel;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Article\Configurator;
use Shopware\Components\Api\BatchInterface;
use Shopware\Models\Shop\Shop;

/**
 * Article API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Article extends Resource implements BatchInterface
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
    protected function getVariantResource()
    {
        return $this->getResource('Variant');
    }

    /**
     * @return Translation
     */
    protected function getTranslationResource()
    {
        return $this->getResource('Translation');
    }

    /**
     * @return Media
     */
    protected function getMediaResource()
    {
        return $this->getResource('Media');
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
        $articleDetail = $this->getDetailRepository()->findOneBy(['number' => $number]);

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
     * @param array $options
     * @return array|\Shopware\Models\Article\Article
     */
    public function getOneByNumber($number, array $options = [])
    {
        $id = $this->getIdFromNumber($number);
        return $this->getOne($id, $options);
    }

    /**
     * @param int $id
     * @param array $options
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return array|\Shopware\Models\Article\Article
     */
    public function getOne($id, array $options = [])
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
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
            'customerGroups'
        ])
            ->from('Shopware\Models\Article\Article', 'article')
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

        /** @var $article \Shopware\Models\Article\Article */
        $article = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $id not found");
        }

        if ($this->getResultMode() == self::HYDRATE_ARRAY) {
            /** @var $article array */
            $article['images'] = $this->getArticleImages($id);
            $article['configuratorSet'] = $this->getArticleConfiguratorSet($id);
            $article['links'] = $this->getArticleLinks($id);
            $article['downloads'] = $this->getArticleDownloads($id);
            $article['categories'] = $this->getArticleCategories($id);
            $article['similar'] = $this->getArticleSimilar($id);
            $article['related'] = $this->getArticleRelated($id);
            $article['details'] = $this->getArticleVariants($id);
            $article['seoCategories'] = $this->getArticleSeoCategories($id);

            if (isset($options['considerTaxInput']) && $options['considerTaxInput']) {
                $article['mainDetail']['prices'] = $this->getTaxPrices(
                    $article['mainDetail']['prices'],
                    $article['tax']['tax']
                );

                foreach ($article['details'] as &$detail) {
                    $detail['prices'] = $this->getTaxPrices(
                        $detail['prices'],
                        $article['tax']['tax']
                    );
                }
            }

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

            if (isset($options['language']) && !empty($options['language'])) {
                /**@var $shop Shop */
                $shop = $this->findEntityByConditions('Shopware\Models\Shop\Shop', [
                    ['id' => $options['language']],
                    ['shop' => $options['language']]
                ]);

                $article = $this->translateArticle(
                    $article,
                    $shop
                );
            }
        }

        return $article;
    }

    /**
     * Helper function which calculates formats the variant prices for each customer group.
     * If the customer group configured "taxInput" as true, the price will be formatted as gross.
     *
     * @param array $prices Array of the variant prices
     * @param float $taxRate Float value of the article tax (example: 19.00)
     * @return array
     */
    public function getTaxPrices(array $prices, $taxRate)
    {
        foreach ($prices as &$price) {
            $price['net'] = $price['price'];
            if ($price['customerGroup'] && $price['customerGroup']['taxInput']) {
                $price['price'] = $price['price'] * (($taxRate + 100) / 100);
            }
        }
        return $prices;
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
        $builder->select(['configuratorSet', 'groups'])
            ->from('Shopware\Models\Article\Configurator\Set', 'configuratorSet')
            ->innerJoin('configuratorSet.articles', 'article')
            ->leftJoin('configuratorSet.groups', 'groups')
            ->addOrderBy('groups.position', 'ASC')
            ->where('article.id = :articleId')
            ->setParameters(['articleId' => $articleId]);

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
        $builder->select(['images'])
            ->from('Shopware\Models\Article\Image', 'images')
            ->innerJoin('images.article', 'article')
            ->where('article.id = :articleId')
            ->orderBy('images.position', 'ASC')
            ->andWhere('images.parentId IS NULL')
            ->setParameters(['articleId' => $articleId]);

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
        $builder->select(['downloads'])
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
        $builder->select(['links'])
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
        $builder->select(['categories.id', 'categories.name'])
            ->from('Shopware\Models\Category\Category', 'categories')
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
        $builder->select(['article', 'PARTIAL similar.{id, name}'])
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
        $builder->select(['article', 'PARTIAL related.{id, name}'])
            ->from('Shopware\Models\Article\Article', 'article')
            ->innerJoin('article.related', 'related')
            ->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $article = $this->getSingleResult($builder);
        return $article['related'];
    }

    /**
     * Returns the configured article seo categories.
     * This categories are used for the seo url generation.
     *
     * @param $articleId
     * @return array
     */
    protected function getArticleSeoCategories($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['seoCategories', 'category'])
            ->from('Shopware\Models\Article\SeoCategory', 'seoCategories')
            ->innerJoin('seoCategories.category', 'category')
            ->where('seoCategories.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        return $builder->getQuery()->getArrayResult();
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
        $builder = $this->getRepository()->getVariantDetailQuery();
        $builder->andWhere('article.id = :articleId')
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
     * @param array $options
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [], array $options = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('article')
            ->addSelect(['attribute'])
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('mainDetail.attribute', 'attribute');

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

        if ($this->getResultMode() === self::HYDRATE_ARRAY
            && isset($options['language'])
            && !empty($options['language'])) {

            /**@var $shop Shop */
            $shop = $this->findEntityByConditions('Shopware\Models\Shop\Shop', [
                ['id' => $options['language']]
            ]);

            foreach ($articles as &$article) {
                $article = $this->translateArticle(
                    $article,
                    $shop
                );
            }
        }

        return ['data' => $articles, 'total' => $totalResult];
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

        $translations = [];
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

        $translations = [];
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
        $query = $this->getRepository()->getRemoveESDQuery($article->getId());
        $query->execute();
        $query = $this->getRepository()->getRemoveArticleTranslationsQuery($article->getId());
        $query->execute();

        $sql= "DELETE FROM s_articles_translations WHERE articleID = ?";
        $this->getManager()->getConnection()->executeQuery($sql, [$article->getId()]);

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
            $query = $this->getRepository()->getRemoveAttributesQuery($detail['id']);
            $query->execute();

            $query = $this->getRepository()->getRemoveImageQuery($detail['id']);
            $query->execute();

            $sql = "DELETE FROM s_article_configurator_option_relations WHERE article_id = ?";
            Shopware()->Db()->query($sql, [$detail['id']]);

            $query = $this->getRepository()->getRemoveVariantTranslationsQuery($detail['id']);
            $query->execute();

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
        $data = $this->prepareSeoCategoryAssociatedData($data, $article);

        $data = $this->prepareRelatedAssociatedData($data, $article);
        $data = $this->prepareSimilarAssociatedData($data, $article);
        $data = $this->prepareAvoidCustomerGroups($data, $article);
        $data = $this->preparePropertyValuesData($data, $article);
        $data = $this->prepareDownloadsAssociatedData($data, $article);
        $data = $this->prepareConfiguratorSet($data, $article);

        //need to set the tax data directly for following price calculations which use the tax object of the article
        if (isset($data['tax'])) {
            $article->setTax($data['tax']);
        }

        if (isset($data['configuratorSet'])) {
            $article->setConfiguratorSet($data['configuratorSet']);
        }

        $data = $this->prepareImageAssociatedData($data, $article);
        $data = $this->prepareAttributeAssociatedData($data, $article);
        $data = $this->prepareMainDetail($data, $article);
        $data = $this->prepareVariants($data, $article);

        unset($data['images']);
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

        if (!$data['mainDetail']) {
            $data['mainDetail'] = [];
        }

        $data['mainDetail'] = $this->getVariantResource()->prepareMainVariantData($data['mainDetail'], $article, $detail);

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
            $data['variants'][0]['isMain'] = true;
        }

        $variants = array();
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
                    $variant = $this->getVariantResource()->internalUpdate(
                        $variant->getId(),
                        $variantData,
                        $article
                    );
                } else {
                    //otherwise the number passed to use as order number for the new variant
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
                            $oldMain = $this->getDetailRepository()->findOneBy(['number' => $oldMain['number']]);
                            $oldMainConfiguratorOptions = $oldMain ? $oldMain->getConfiguratorOptions()->toArray() : null;
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
        if ($oldMainId && $oldMainId != $newMain->getId()) {
            $oldMainVariantProcessed = false;

            foreach ($variants as &$processedVariant) {
                if ($processedVariant->getId() == $oldMainId) {
                    $processedVariant->setKind(2);
                    $oldMainVariantProcessed = true;
                    break;
                }
            }

            if (!$oldMainVariantProcessed) {
                $oldMain = $this->getDetailRepository()->find($oldMainId);
                if ($oldMain) {
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

        $allOptions = [];
        $allGroups = [];

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

            $groupOptions = [];
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
                        $option = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Option')->findOneBy([
                            'name' => $optionData['name'],
                            'groupId' => $group->getId()
                        ]);
                    }
                }

                if (!$option) {
                    $option = new Configurator\Option();
                }

                $option->fromArray($optionData);
                $option->setGroup($group);
                if (!isset($optionData['position'])) {
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
        } elseif (isset($data['tax']) && ($data['tax'] >= 0)) {
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

        return $data;
    }

    /**
     * Helper function for the category assignment.
     * This function is used for the category configuration.
     * If the data key __options_categories => replace is set to true,
     * the function removes the assigned article categories from the
     * s_articles_categories and s_articles_categories_ro table.
     *
     * @param array $data
     * @param ArticleModel $article
     */
    private function resetArticleCategoryAssignment(array $data, ArticleModel $article)
    {
        if (!$article->getId()) {
            return;
        }

        $key = '__options_categories';

        //replacement deactivated?
        if (isset($data[$key]) && $data[$key]['replace'] == false) {
            return;
        }

        $this->manager->getConnection()->executeUpdate(
            "DELETE FROM s_articles_categories WHERE articleID = :articleId",
            array(':articleId' => $article->getId())
        );

        $this->manager->getConnection()->executeUpdate(
            "DELETE FROM s_articles_categories_ro WHERE articleID = :articleId",
            array(':articleId' => $article->getId())
        );
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

        $this->resetArticleCategoryAssignment($data, $article);

        $categories = $article->getCategories();
        $categoryIds = $categories->map(function ($category) {
            return $category->getId();
        });

        $categoryIds = array_flip($categoryIds->toArray());

        foreach ($data['categories'] as $categoryData) {
            $category = $this->getManyToManySubElement(
                $categories,
                $categoryData,
                '\Shopware\Models\Category\Category'
            );

            if (!$category) {
                if (!empty($categoryData['path'])) {
                    $category = $this->getResource('Category')->findCategoryByPath($categoryData['path'], true);

                    if (!$category) {
                        throw new ApiException\CustomValidationException(sprintf("Could not find or create category by path: %s.",
                            $categoryData['path']));
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
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareSeoCategoryAssociatedData($data, ArticleModel $article)
    {
        if (!isset($data['seoCategories'])) {
            return $data;
        }

        $categories = $this->checkDataReplacement(
            $article->getSeoCategories(),
            $data,
            'seoCategories',
            true
        );

        foreach ($data['seoCategories'] as $categoryData) {

            /**@var $seoCategory \Shopware\Models\Article\SeoCategory */
            $seoCategory = $this->getOneToManySubElement(
                $categories,
                $categoryData,
                '\Shopware\Models\Article\SeoCategory'
            );

            if (isset($categoryData['shopId'])) {
                /** @var $shop \Shopware\Models\Shop\Shop */
                $shop = $this->manager->find(
                    'Shopware\Models\Shop\Shop',
                    $categoryData['shopId']
                );

                if (!$shop) {
                    throw new ApiException\CustomValidationException(
                        sprintf("Could not find shop by id: %s.", $categoryData['shopId'])
                    );
                }

                $seoCategory->setShop($shop);
            }

            if (!$seoCategory->getShop()) {
                throw new ApiException\CustomValidationException(
                    sprintf("An article seo category requires a configured shop")
                );
            }

            if (isset($categoryData['categoryId'])) {
                /** @var $category \Shopware\Models\Category\Category */
                $category = $this->manager->find(
                    'Shopware\Models\Category\Category',
                    $categoryData['categoryId']
                );

                if (!$category) {
                    throw new ApiException\CustomValidationException(
                        sprintf("Could not find category by id: %s.", $categoryData['categoryId'])
                    );
                }

                $seoCategory->setCategory($category);
            } elseif (isset($categoryData['categoryPath'])) {
                $category = $this->getResource('Category')->findCategoryByPath(
                    $categoryData['categoryPath'],
                    true
                );
                if (!$category) {
                    throw new ApiException\CustomValidationException(
                        sprintf("Could not find category by path: %s.", $categoryData['categoryPath'])
                    );
                }
                $seoCategory->setCategory($category);
            }

            $existing = $this->getCollectionElementByProperty(
                $data['categories'],
                'id',
                $seoCategory->getCategory()->getId()
            );

            if (!$existing) {
                throw new ApiException\CustomValidationException(
                    sprintf("Seo category isn't assigned as normal article category. Only assigned categories can be used as seo category")
                );
            }

            $seoCategory->setArticle($article);
        }

        $data['seoCategories'] = $categories;

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

            $relatedArticle = null;
            if ($relatedData['number']) {
                $articleId = $this->getManager()->getConnection()->fetchColumn(
                    "SELECT articleID FROM s_articles_details WHERE ordernumber = :number",
                    array(':number' => $relatedData['number'])
                );

                if ($articleId) {
                    $relatedArticle = $this->getManyToManySubElement(
                        $related,
                        array('id' => $articleId),
                        '\Shopware\Models\Article\Article'
                    );
                }
            }

            if (!$relatedArticle) {
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

            $similarArticle = null;
            if ($similarData['number']) {
                $articleId = $this->getManager()->getConnection()->fetchColumn(
                    "SELECT articleID FROM s_articles_details WHERE ordernumber = :number",
                    array(':number' => $similarData['number'])
                );

                if ($articleId) {
                    $similarArticle = $this->getManyToManySubElement(
                        $similar,
                        array('id' => $articleId),
                        '\Shopware\Models\Article\Article'
                    );
                }
            }

            if (!$similarArticle) {
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

        $models = [];

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
                        $filters = [
                            ['property' => "options.name", 'expression' => '=', 'value' => $valueData['option']['name']],
                            ['property' => "groups.name", 'expression' => '=', 'value' => $propertyGroup->getName()],
                        ];
                        $query = $propertyRepository->getPropertyRelationQuery($filters, null, 1, 0);
                        /** @var \Shopware\Models\Property\Relation $relation */
                        $relation = $query->getOneOrNullResult(self::HYDRATE_OBJECT);
                        if (!$relation) {
                            //checks if a new option was created
                            //because the new option is not written to the database at this point
                            $groupOption = $this->getCollectionElementByProperty(
                                $propertyGroup->getOptions(),
                                'name',
                                $valueData['option']['name']
                            );
                            //creates a new option
                            if ($groupOption === null) {
                                $option = new \Shopware\Models\Property\Option();
                                $propertyGroup->addOption($option);
                            } else {
                                $option = $groupOption;
                            }
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
                $value = $this->getManager()->getRepository('\Shopware\Models\Property\Value')->findOneBy([
                    'value' => $valueData['value'],
                    'optionId' => $option->getId()
                ]);
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
                $media = $this->getMediaResource()->internalCreateMediaByFileLink(
                    $downloadData['link'],
                    -6
                );
                if (isset($downloadData['name']) && !empty($downloadData['name'])) {
                    $media->setDescription($downloadData['name']);
                }

                try { //persist the model into the model manager
                    $this->getManager()->persist($media);
                } catch (\Doctrine\ORM\ORMException $e) {
                    throw new ApiException\CustomValidationException(sprintf("Some error occured while loading your image"));
                }

                $download->setFile($media->getPath());
                $download->setName($media->getName());
                $download->setSize($media->getFileSize());
            }

            $download->fromArray($downloadData);
            $download->setArticle($article);
        }
        $data['downloads'] = $downloads;

        return $data;
    }

    /**
     * Resolves the passed images data to valid Shopware\Models\Article\Image
     * entities.
     *
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
            /**@var $image Image */
            $image = $this->getOneToManySubElement(
                $images,
                $imageData,
                '\Shopware\Models\Article\Image'
            );

            if (isset($imageData['link'])) {
                /**@var $media MediaModel */
                $media = $this->getMediaResource()->internalCreateMediaByFileLink(
                    $imageData['link']
                );

                $image = $this->updateArticleImageWithMedia(
                    $article,
                    $image,
                    $media
                );

                $image->setPosition($position);
                $position++;
            } elseif (!empty($imageData['mediaId'])) {
                $media = $this->getManager()->find(
                    'Shopware\Models\Media\Media',
                    (int) $imageData['mediaId']
                );

                if (!($media instanceof MediaModel)) {
                    throw new ApiException\CustomValidationException(sprintf("Media by mediaId %s not found", $imageData['mediaId']));
                }

                $image = $this->updateArticleImageWithMedia(
                    $article,
                    $image,
                    $media
                );
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

            if (isset($imageData['options'])) {
                $this->createImageMappings($image, $article, $imageData['options']);
            }
        }

        $hasMain = $this->getCollectionElementByProperty(
            $images,
            'main',
            1
        );

        if (!$hasMain) {
            $image = $images->get(0);
            $image->setMain(1);
        }
        unset($data['images']);

        return $data;
    }

    /**
     * This function generates all variant image entities
     * for the passed article id.
     * The function expects that the variants and the mapping of the article images
     * already exists.
     *
     * @param $id
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function generateVariantImages($id)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $article \Shopware\Models\Article\Article */
        $article = $this->getRepository()->find($id);

        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $id not found");
        }

        $builder = $this->getArticleImageMappingsQuery($id);

        $mappings = $builder->getQuery()->getResult();

        /**@var $mapping Image\Mapping */
        foreach ($mappings as $mapping) {
            $builder = $this->getArticleVariantQuery($id);

            /**@var $rule Image\Rule */
            foreach ($mapping->getRules() as $rule) {
                $option = $rule->getOption();
                $alias = 'option' . $option->getId();
                $builder->innerJoin('variants.configuratorOptions', $alias, 'WITH', $alias . '.id = :' . $alias)
                    ->setParameter($alias, $option->getId());
            }

            $variants = $builder->getQuery()->getResult();

            /**@var $variant Detail */
            foreach ($variants as $variant) {
                $exist = $this->getCollectionElementByProperty(
                    $variant->getImages(),
                    'parent',
                    $mapping->getImage()
                );
                if ($exist) {
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
     * Returns a query builder to select all article images with mappings and rules.
     * Used to generate the variant images.
     *
     * @param $articleId
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilder
     */
    protected function getArticleImageMappingsQuery($articleId)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['mappings', 'image', 'rules'])
            ->from('Shopware\Models\Article\Image\Mapping', 'mappings')
            ->innerJoin('mappings.image', 'image')
            ->innerJoin('mappings.rules', 'rules')
            ->where('image.articleId = :articleId')
            ->setParameter('articleId', $articleId);

        return $builder;
    }

    /**
     * Checks if the passed article image is already created
     * as variant image.
     *
     * @param Detail $variant
     * @param Image $image
     * @return bool
     */
    protected function isVariantImageExist(Detail $variant, Image $image)
    {
        /**@var $variantImage Image */
        foreach ($variant->getImages() as $variantImage) {
            if ($variantImage->getParent()->getId() == $image->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Small helper function which creates a query builder to select
     * all article variants.
     *
     * @param $id
     * @return \Doctrine\ORM\QueryBuilder|QueryBuilder
     */
    protected function getArticleVariantQuery($id)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select('variants');
        $builder->from('Shopware\Models\Article\Detail', 'variants')
            ->where('variants.articleId = :articleId')
            ->setParameter('articleId', $id);
        return $builder;
    }

    /**
     * Creates the article image mappings for the passed article and image entity.
     * The mappings parameter contains a multi dimensional array with configurator options.
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
     * @param Image $image
     * @param ArticleModel $article
     * @param array $mappings
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function createImageMappings(Image $image, ArticleModel $article, array $mappings)
    {
        if (!$article->getConfiguratorSet()) {
            throw new ApiException\CustomValidationException(
                "Article is no configurator article. Image mapping can only be created on configurator articles"
            );
        }

        $configuratorOptions = $article->getConfiguratorSet()->getOptions();

        foreach ($mappings as $mappingData) {
            $options = new ArrayCollection();

            foreach ($mappingData as $option) {
                $available = $this->getCollectionElementByProperties($configuratorOptions, [
                    'id' => $option['id'],
                    'name' => $option['name'],
                ]);

                if (!$available) {
                    $property = $option['id'] ? $option['id'] : $option['name'];
                    throw new ApiException\CustomValidationException(
                        sprintf("Passed option %s do not exist in the configurator set of the article", $property)
                    );
                }

                $options->add($available);
            }

            if (empty($options)) {
                throw new ApiException\CustomValidationException("No available option exists");
            }

            $this->getVariantResource()->createImageMappingForOptions(
                $options,
                $image
            );
        }
    }

    /**
     * Helper function which creates a new article image with the passed media object.
     * @param ArticleModel $article
     * @param MediaModel $media
     * @return Image
     */
    public function createNewArticleImage(ArticleModel $article, MediaModel $media)
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
     * Helper function to map the media data into an article image
     *
     * @param ArticleModel $article
     * @param Image $image
     * @param MediaModel $media
     * @return Image
     */
    public function updateArticleImageWithMedia(ArticleModel $article, Image $image, MediaModel $media)
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

            //backward compatibility for attribute translations
            foreach ($translation as $key => $value) {
                $attrKey = '__attribute_' . $key;
                if (in_array($attrKey, $whitelist) && !isset($translation[$attrKey])) {
                    $translation[$attrKey] = $value;
                }
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
        $properties = [];

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

        $fields = [];
        foreach ($properties as $property) {
            $fields[] = '__attribute_' . $property;
        }
        return $fields;
    }

    /**
     * Translate the whole article array.
     *
     * @param array $data
     * @param Shop $shop
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

        $data['links'] = $this->translateAssociation(
            $data['links'],
            $shop,
            'link'
        );

        $data['downloads'] = $this->translateAssociation(
            $data['downloads'],
            $shop,
            'download'
        );

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
     * @param $values
     * @param Shop $shop
     * @return mixed
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
     * @param $supplier
     * @param Shop $shop
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
     * @param $groupData
     * @param Shop $shop
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
     * @param $details
     * @param Shop $shop
     * @return mixed
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
     * @param $data
     * @param $translation
     * @return array
     */
    protected function mergeTranslation($data, $translation)
    {
        $data = array_merge(
            $data,
            array_intersect_key($translation, $data)
        );

        return $data;
    }

    /**
     * Helper function which translates associated array data.
     *
     * @param array $association
     * @param Shop $shop
     * @param $type
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
     * @param $type
     * @param $shopId
     * @param $key
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
     * Returns the primary ID of any data set.
     *
     * {@inheritDoc}
     */
    public function getIdByData($data)
    {
        $id = null;

        if (isset($data['id'])) {
            $id = $data['id'];
        } elseif (isset($data['mainDetail']['number'])) {
            try {
                $id = $this->getIdFromNumber($data['mainDetail']['number']);
            } catch (ApiException\NotFoundException $e) {
                return false;
            }
        }

        if (!$id) {
            return false;
        }

        $model = $this->getManager()->find('Shopware\Models\Article\Article', $id);

        if ($model) {
            return $id;
        }

        return false;
    }
}
