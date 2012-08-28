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
use Shopware\Models\Article\Article as ArticleModel;


/**
 * Article API Resource
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

        /** @var $article \Shopware\Models\Article\Article */
        $builder = $this->getRepository()->getArticleQueryBuilder($id);
        $builder->addSelect('supplier', 'mainDetailAttribute', 'propertyGroup', 'configuratorSet', 'configuratorGroups', 'configuratorOptions', 'details', 'detailsAttribute')
                ->leftJoin('article.supplier', 'supplier')
                ->leftJoin('article.details', 'details')
                ->leftJoin('details.attribute', 'detailsAttribute')
                ->leftJoin('mainDetail.attribute', 'mainDetailAttribute')
                ->leftJoin('article.propertyGroup', 'propertyGroup')
                ->leftJoin('article.configuratorSet', 'configuratorSet')
                ->leftJoin('configuratorSet.groups', 'configuratorGroups')
                ->leftJoin('configuratorGroups.options', 'configuratorOptions');

        $article = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $id not found");
        }

        if ($this->getResultMode() == self::HYDRATE_ARRAY) {
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
     * @param int $offset
     * @param int $limit
     * @param array $criteria
     * @param array $orderBy
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = array(), array $orderBy = array())
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('article');

        $builder->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

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

        /** @var $article \Shopware\Models\Article\Article */
        $builder = $this->getRepository()->getArticleQueryBuilder($id);
        $builder->addSelect('supplier', 'mainDetailAttribute')
                ->leftJoin('article.supplier', 'supplier')
                ->leftJoin('mainDetail.attribute', 'mainDetailAttribute');
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

        $this->getManager()->remove($article);
        $this->flush();

        return $article;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @return array
     */
    protected function prepareAssociatedData($data, ArticleModel $article)
    {
        $data = $this->prepareArticleAssociatedData($data);
        $data = $this->prepareMainDetailAssociatedData($data);
        $data = $this->prepareMainPricesAssociatedData($data, $article);
        $data = $this->prepareCategoryAssociatedData($data);
        $data = $this->prepareRelatedAssociatedData($data, $article);
        $data = $this->prepareSimilarAssociatedData($data, $article);
        $data = $this->prepareAvoidCustomerGroups($data);
        $data = $this->prepareAttributeAssociatedData($data, $article);
        $data = $this->preparePropertyValuesData($data, $article);
        $data = $this->prepareImageAssociatedData($data, $article);
        $data = $this->prepareDownloadsAssociatedData($data, $article);
        $data = $this->prepareConfiguratorSet($data, $article);
        $data = $this->prepareVariants($data, $article);

        return $data;
    }

    /**
     * @param array $data
     * @param array $variantData
     * @param \Shopware\Models\Article\Article $article
     * @param \Shopware\Models\Article\Detail $variant
     * @return array
     */
    protected function prepareVariantPricesAssociatedData($data, $variantData, ArticleModel $article, \Shopware\Models\Article\Detail $variant)
    {
        if (empty($variantData['prices'])) {
            return $variantData;
        }

        if (isset($data['tax'])) {
            $tax = $data['tax'];
        } else {
            $tax = $article->getTax();
        }

        $variantData['prices'] = $this->preparePricesAssociatedData($variantData['prices'], $article, $variant, $tax);

        return $variantData;
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



        $variants = array();
        foreach ($data['variants'] as $variantData) {

            if (isset($variantData['configuratorOptions'])) {
                $configuratorSet = $article->getConfiguratorSet();

                if (!$configuratorSet && !isset($data['configuratorSet'])) {
                    throw new ApiException\CustomValidationException('A configuratorset has to be defined');
                }

                /** @var \Shopware\Models\Article\Configurator\Set $configuratorSet */
                if ($configuratorSet) {
                    $availableGroups = $configuratorSet->getGroups();
                } else {
                    $configuratorSet = $data['configuratorSet'];
                    $availableGroups = $configuratorSet->getGroups();
                }
            }

            if (isset($variantData['id'])) {
                $variant = $this->getManager()->getRepository('Shopware\Models\Article\Detail')->findOneBy(array(
                    'id'        => $variantData['id'],
                    'articleId' => $article->getId()
                ));

                if (!$variant) {
                    throw new ApiException\CustomValidationException(sprintf("Variant by id %s not found", $variantData['id']));
                }
            } else {
                $variant = new \Shopware\Models\Article\Detail();
                $variant->setKind(2);
            }

            $variantData = $this->prepareVariantAssociatedData($variantData);
            $variantData = $this->prepareVariantPricesAssociatedData($data, $variantData, $article, $variant);
            $variantData = $this->prepareVariantAttributeAssociatedData($variantData, $article);

            $variant->fromArray($variantData);

            if (isset($variantData['configuratorOptions'])) {
                $assignedOptions = new \Doctrine\Common\Collections\ArrayCollection();
                foreach ($variantData['configuratorOptions'] as $configuratorOption) {
                    $group  = $configuratorOption['group'];
                    $option = $configuratorOption['option'];

                    /** @var \Shopware\Models\Article\Configurator\Group $availableGroup */
                    foreach ($availableGroups as $availableGroup) {
                        if ($availableGroup->getName() == $group) {

                            /** @var \Shopware\Models\Article\Configurator\Option $availableOption */
                            foreach ($availableGroup->getOptions() as $availableOption) {
                                if ($availableOption->getName() == $option) {
                                    $assignedOptions->add($availableOption);
                                }
                            }
                        }
                    }
                }
                $variant->setConfiguratorOptions($assignedOptions);
            }

            if ($article->getId() > 0) {
                if ($variantData['isMain']) {
                    $newMain = $variant;
                    $newMain->setKind(1);

                    $oldMain = $data['mainDetail'];
                    $oldMain['kind']   = 3;
                    $oldMain['active'] = false;

                    $data['mainDetail'] = $newMain;
                    $variant = $oldMain;
                }
            }

            $variants[] = $variant;
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
            $configuratorSet = new \Shopware\Models\Article\Configurator\Set();
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
                    $group = new \Shopware\Models\Article\Configurator\Group();
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
                    $option = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Option')->findOneBy(array(
                        'name'    => $optionData['name'],
                        'groupId' => $group->getId()
                    ));
                }

                if (!$option) {
                    $option = new \Shopware\Models\Article\Configurator\Option();
                }

                $option->fromArray($optionData);
                $option->setGroup($group);
                $option->setPosition($optionPosition++);
                $allOptions[]   = $option;
                $groupOptions[] = $option;
            }

            $groupData['options'] = $groupOptions;
            $group->fromArray($groupData);
            $allGroups[] = $group;
        }

        $configuratorSet->setOptions($allOptions);
        $configuratorSet->setGroups($allGroups);

        $data['configuratorSet'] = $configuratorSet;

        return $data;
    }

    /**
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareArticleAssociatedData($data)
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
                $data['propertyGroup'] = $this->getManager()->find('Shopware\Models\Property\Group', $data['filterGroupId']);
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
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareMainDetailAssociatedData($data)
    {
        if (!empty($data['mainDetail']['unitId'])) {
            $data['mainDetail']['unit'] = $this->getManager()->find('Shopware\Models\Article\Unit', $data['mainDetail']['unitId']);
            if (empty($data['mainDetail']['unit'])) {
                throw new ApiException\CustomValidationException(sprintf('Unit by id %s not found', $data['mainDetail']['unitId']));
            }
        }

        return $data;
    }

    /**
     * @param array $data
     * @param \Shopware\Models\Article\Article $article
     * @return array
     */
    protected function prepareMainPricesAssociatedData($data, ArticleModel $article)
    {
        if (empty($data['mainDetail']['prices'])) {
            return $data;
        }

        if (isset($data['tax'])) {
            $tax = $data['tax'];
        } else {
            $tax = $article->getTax();
        }

        $data['mainDetail']['prices'] = $this->preparePricesAssociatedData($data['mainDetail']['prices'], $article, $article->getMainDetail(), $tax);
        return $data;
    }

    /**
     * @param array $prices
     * @param \Shopware\Models\Article\Article $article
     * @param \Shopware\Models\Article\Detail $articleDetail
     * @param \Shopware\Models\Tax\Tax $tax
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function preparePricesAssociatedData($prices, ArticleModel $article, $articleDetail, \Shopware\Models\Tax\Tax $tax)
    {
        foreach ($prices as &$priceData) {

            if (empty($priceData['customerGroupKey'])) {
                $priceData['customerGroupKey'] = 'EK';
            }

            // load the customer group of the price definition
            $customerGroup = $this->getManager()
                                  ->getRepository('Shopware\Models\Customer\Group')
                                  ->findOneBy(array('key' => $priceData['customerGroupKey']));

            /** @var \Shopware\Models\Customer\Group $customerGroup */
            if (!$customerGroup instanceof \Shopware\Models\Customer\Group) {
                throw new ApiException\CustomValidationException(sprintf('Customer Group by key %s not found', $priceData['customerGroupKey']));
            }

            if (!isset($priceData['from'])) {
                $priceData['from'] = 1;
            }

            $priceData['from'] = intval($priceData['from']);
            $priceData['to']   = intval($priceData['to']);

            if ($priceData['from'] <= 0) {
                throw new ApiException\CustomValidationException(sprintf('Invalid Price "from" value'));
            }

            // if the "to" value isn't numeric, set the place holder "beliebig"
            if ($priceData['to'] <= 0) {
                $priceData['to'] = 'beliebig';
            }

            $priceData['price']       = floatval(str_replace(",", ".", $priceData['price']));
            $priceData['basePrice']   = floatval(str_replace(",", ".", $priceData['basePrice']));
            $priceData['pseudoPrice'] = floatval(str_replace(",", ".", $priceData['pseudoPrice']));
            $priceData['percent']     = floatval(str_replace(",", ".", $priceData['percent']));

            if ($customerGroup->getTaxInput()) {
                $priceData['price'] = $priceData['price'] / (100 + $tax->getTax()) * 100;
                $priceData['pseudoPrice'] = $priceData['pseudoPrice'] / (100 + $tax->getTax()) * 100;
            }

            $priceData['customerGroup'] = $customerGroup;
            $priceData['article']       = $article;
            $priceData['articleDetail'] = $articleDetail;
        }

        return $prices;
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
        unset ($data['mainDetail']['attribute']['articleDetailId']);
        $data['mainDetail']['attribute']['article'] = $article;

        return $data;
    }

    /**
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareCategoryAssociatedData($data)
    {
        if (!isset($data['categories'])) {
            return $data;
        }

        $categories = array();
        foreach ($data['categories'] as $categoryData) {
            if (!empty($categoryData['id'])) {
                $model = $this->getManager()->find('Shopware\Models\Category\Category', $categoryData['id']);
                if (!$model) {
                    throw new ApiException\CustomValidationException(sprintf("Category by id %s not found", $categoryData['id']));
                }
                $categories[] = $model;
            }
        }

        $data['categories'] = $categories;

        return $data;
    }

    /**
     * @param array $data
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareAvoidCustomerGroups($data)
    {
        if (!isset($data['customerGroups'])) {
            return $data;
        }

        $customerGroups = array();
        foreach ($data['customerGroups'] as $customerGroup) {
            if (!empty($customerGroup['id'])) {
                $customerGroup = $this->getManager()->find('Shopware\Models\Customer\Group', $customerGroup['id']);
                if (!$customerGroup) {
                    throw new ApiException\CustomValidationException(sprintf("CustomerGroup by id %s not found", $customerGroup['id']));
                }

                $customerGroups[] = $customerGroup;
            }
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

        $related = array();
        foreach ($data['related'] as $relatedData) {
            if (empty($relatedData['id'])) {
                continue;
            }

            /**@var $relatedArticle \Shopware\Models\Article\Article*/
            $relatedArticle = $this->getRepository()->find($relatedData['id']);
            if (!$relatedArticle) {
                throw new ApiException\CustomValidationException(sprintf("Related Article by id %s not found", $relatedData['id']));
            }

            // if the user select the cross
            if ($relatedData['cross']) {
                $relatedArticle->getRelated()->add($article);
            }
            $related[] = $relatedArticle;
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

        $similar = array();
        foreach ($data['similar'] as $similarData) {
            if (empty($similarData['id'])) {
                continue;
            }

            /**@var $similarArticle \Shopware\Models\Article\Article*/
            $similarArticle = $this->getRepository()->find($similarData['id']);
            if (!$similarArticle) {
                throw new ApiException\CustomValidationException(sprintf("Similar Article by id %s not found", $similarData['id']));
            }

            //if the user select the cross
            if ($similarData['cross']) {
                $similarArticle->getSimilar()->add($article);
            }
            $similar[] = $similarArticle;
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

        if (isset($data['propertyGroup'])) {
            $propertyGroup = $data['propertyGroup'];
        } else {
            $propertyGroup = $article->getPropertyGroup();
        }

        if (!$propertyGroup instanceof \Shopware\Models\Property\Group) {
            throw new ApiException\CustomValidationException(sprintf("There is no filterGroup spicified"));
        }

        $models = array();
        foreach ($data['propertyValues'] as $valueData) {
            if (empty($valueData['id'])) {
                continue;
            }
            $model = $this->getManager()->find('Shopware\Models\Property\Value', $valueData['id']);
            if (!$model instanceof \Shopware\Models\Property\Value) {
                throw new ApiException\CustomValidationException(sprintf("Property Value by id %s not found", $valueData['id']));
            }
            $models[] = $model;
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

        $downloads = array();
        foreach ($data['downloads'] as &$downloadData) {
            if (isset($downloadData['id'])) {
                $download = $this->getManager()
                                 ->getRepository('Shopware\Models\Article\Download')
                                 ->find($downloadData['id']);

                if (!$download instanceof \Shopware\Models\Article\Download) {
                    throw new ApiException\CustomValidationException(sprintf("Download by id %s not found", $downloadData['id']));
                }
            } else {
                $download = new \Shopware\Models\Article\Download();
            }

            if (isset($downloadData['link'])) {
                $path = $this->load($downloadData['link']);
                $file = new \Symfony\Component\HttpFoundation\File\File($path);

                $media = new \Shopware\Models\Media\Media();
                $media->setAlbumId(-6);
                $media->setAlbum($this->getManager()->find('Shopware\Models\Media\Album', -6));

                $media->setFile($file);
                $media->setDescription('');
                $media->setCreated(new \DateTime());
                $media->setUserId(0);

                try { //persist the model into the model manager
                    $this->getManager()->persist($media);
                    $this->getManager()->flush();
                } catch (\Doctrine\ORM\ORMException $e) {
                    throw new ApiException\CustomValidationException(sprintf("Some error occured while loading your image"));
                }

                $download->setFile($media->getPath());
                $download->setName($media->getName());
                $download->setSize($media->getFileSize());
            }

            $download->fromArray($downloadData);
            $downloads[] = $download;
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

        $images = $article->getImages();

        $position = 1;

        foreach ($data['images'] as &$imageData) {
            if (isset($imageData['id'])) {
                $image = $this->getManager()
                        ->getRepository('Shopware\Models\Article\Image')
                        ->find($imageData['id']);

                if (!$image instanceof \Shopware\Models\Article\Image) {
                    throw new ApiException\CustomValidationException(sprintf("Image by id %s not found", $imageData['id']));
                }
            } else {
                $image = new \Shopware\Models\Article\Image();
            }

            if (isset($imageData['link'])) {
                $path = $this->load($imageData['link']);

                $file = new \Symfony\Component\HttpFoundation\File\File($path);

                $media = new \Shopware\Models\Media\Media();
                $media->setAlbumId(-1);
                $media->setAlbum($this->getManager()->find('Shopware\Models\Media\Album', -1));

                $media->setFile($file);
                $media->setDescription('');
                $media->setCreated(new \DateTime());
                $media->setUserId(0);

                try {
                    //persist the model into the model manager
                    $this->getManager()->persist($media);
                    $this->getManager()->flush();
                } catch (\Doctrine\ORM\ORMException $e) {
                    throw new ApiException\CustomValidationException(sprintf("Some error occured while loading your image"));
                }

                $image->setMain(2);
                $image->setMedia($media);
                $image->setPosition($position);
                $image->setArticle($article);
                $position++;
                $image->setPath($media->getName());
                $image->setExtension($media->getExtension());
            }

            $image->fromArray($imageData);
            $images->add($image);
        }

        unset($data['images']);

        return $data;
    }

    /**
     * @param array $variantData
     * @return array
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function prepareVariantAssociatedData($variantData)
    {
        if (!empty($variantData['unitId'])) {
            $variantData['unit'] = $this->getManager()->find('Shopware\Models\Article\Unit', $variantData['unitId']);
            if (empty($variantData['unit'])) {
                throw new ApiException\CustomValidationException(sprintf('Unit by id %s not found', $variantData['unitId']));
            }
        }

        return $variantData;
    }

    /**
     * @param array $variantData
     * @param \Shopware\Models\Article\Article $article
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function prepareVariantAttributeAssociatedData($variantData, ArticleModel $article)
    {
        if (!isset($variantData['attribute'])) {
            return $variantData;
        }

        $variantData['attribute']['article'] = $article;
        return $variantData;
    }

    /**
     * @param integer $articleId
     * @param array $translations
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function writeTranslations($articleId, $translations)
    {
        $whitelist = array(
            'name',
            'description',
            'descriptionLong',
            'keywords',
            'packUnit',
        );

        $translationWriter = new \Shopware_Components_Translation();
        foreach ($translations as $translation) {
            $shop = $this->getManager()->find('Shopware\Models\Shop\Shop', $translation['shopId']);
            if (!$shop) {
                throw new ApiException\CustomValidationException(sprintf("Shop by id %s not found", $translation['shopId']));
            }

            $data = array_intersect_key($translation, array_flip($whitelist));
            $translationWriter->write($shop->getId(), 'article', $articleId,  $data);
        }
    }

    /**
     * @param string $url URL of the resource that should be loaded (ftp, http, file)
     * @return bool|string returns the absolute path of the downloaded file
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function load($url)
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
        } else if (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not have write permissions.", $destPath)
            );
        }

        $urlArray = parse_url($url);
        $urlArray['path'] = explode("/",$urlArray['path']);
        switch ($urlArray['scheme']) {
            case "ftp":
            case "http":
            case "file":
                $hash = "";
                while (empty($hash)) {
                    $hash = md5(uniqid(rand(), true));
                    if (file_exists("$destPath/$hash")) {
                        $hash = "";
                    }
                }

                if (!$put_handle = fopen("$destPath/$hash", "w+")) {
                    throw new \Exception("Could not open $destPath/$hash for writing");
                }

                if (!$get_handle = fopen($url, "r")) {
                    return false;
                }
                while (!feof($get_handle)) {
                    fwrite($put_handle, fgets($get_handle, 4096));
                }
                fclose($get_handle);
                fclose($put_handle);

                return "$destPath/$hash";
        }
        throw new \InvalidArgumentException(
            sprintf("Unsupported schema '%s'.", $urlArray['scheme'])
        );
    }
}
