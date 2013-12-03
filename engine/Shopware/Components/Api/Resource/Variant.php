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

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Api\Manager;
use Shopware\Models\Article\Article as ArticleModel;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Unit;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Tax\Tax;

/**
 * Variant API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Variant extends Resource
{
    /**
     * @return \Shopware\Models\Article\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Article\Detail');
    }

    /**
     * @return Article
     */
    public function getArticleResource()
    {
        return $this->getResource('Article');
    }

    /**
     * @param string $number
     * @return array|\Shopware\Models\Article\Detail
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
     * @return array|\Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->getDetailsByIdsQuery(array($id));
        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $articleDetail = $builder->getOneOrNullResult($this->getResultMode());

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }


        return $articleDetail;
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
        $articleDetail = $this->getRepository()->findOneBy(array('number' => $number));

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Variant by number {$number} not found");
        }

        return $articleDetail->getId();
    }


    /**
     * @param string $number
     * @return \Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function deleteByNumber($number)
    {
        $id = $this->getIdFromNumber($number);
        return $this->delete($id);
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Article\Detail
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $articleDetail = $this->getRepository()->find($id);

        if (!$articleDetail) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }

        if ($articleDetail->getKind() === 1) {
            $articleDetail->getArticle()->setMainDetail(null);
        }

        $this->getManager()->remove($articleDetail);
        $this->flush();

        return $articleDetail;
    }


    /**
     * Updates a single variant entity.
     *
     * @param $id
     * @param array $params
     * @return Detail
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function update($id, array $params)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /**@var $variant Detail*/
        $variant = $this->getRepository()->find($id);

        if (!$variant) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }

        $variant = $this->internalUpdate($id, $params, $variant->getArticle());

        $violations = $this->getManager()->validate($variant);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $variant;
    }


    /**
     * Creates a new variant for an article.
     * This function requires an articleId in the params parameter.
     *
     * @param array $params
     * @return Detail
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function create(array $params)
    {
        $articleId = $params['articleId'];

        if (empty($articleId)) {
            throw new ApiException\ParameterMissingException("Passed parameter array does not contain an articleId property");
        }

        /**@var $article ArticleModel*/
        $article = $this->getManager()->find('Shopware\Models\Article\Article', $articleId);

        if (!$article) {
            throw new ApiException\NotFoundException("Article by id $articleId not found");
        }

        $variant = $this->internalCreate($params, $article);

        $violations = $this->getManager()->validate($variant);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($variant);
        $this->flush();

        return $variant;
    }


    /**
     * Update function for the internal usage of the rest api.
     * Used from the article resource. This function supports
     * to pass an updated article entity which isn't updated in the database.
     * Required for the article resource if the article data is already updated
     * in the entity but not in the database.
     *
     * @param $id
     * @param array $data
     * @param ArticleModel $article
     * @return Detail
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function internalUpdate($id, array $data, ArticleModel $article)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /**@var $variant Detail*/
        $variant = $this->getRepository()->find($id);

        if (!$variant) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }

        $variant->setArticle($article);

        $data = $this->prepareData($data, $article, $variant);

        $variant->fromArray($data);

        return $variant;
    }

    /**
     * Create function for the internal usage of the rest api.
     * Used from the article resource. This function supports
     * to pass an updated article entity which isn't updated in the database.
     * Required for the article resource if the article data is already updated
     * in the entity but not in the database.
     *
     * @param array $data
     * @param ArticleModel $article
     * @return Detail
     * @throws \Shopware\Components\Api\Exception\ValidationException
     */
    public function internalCreate(array $data, ArticleModel $article)
    {
        $variant = new Detail();
        $variant->setKind(2);
        $variant->setArticle($article);

        $data = $this->prepareData($data, $article, $variant);

        $variant->fromArray($data);

        $this->getManager()->persist($variant);
        return $variant;
    }


    /**
     * Interface which allows to use the data preparation in the article resource for the main variant.
     *
     * @param array $data
     * @param ArticleModel $article
     * @param Detail $variant
     * @return array|mixed
     */
    public function prepareMainVariantData(array $data, ArticleModel $article, Detail $variant)
    {
        return $this->prepareData($data, $article, $variant);
    }


    /**
     * Resolves the association data for a single variant.
     *
     * @param array $data
     * @param ArticleModel $article
     * @param Detail $variant
     * @return array|mixed
     */
    protected function prepareData(array $data, ArticleModel $article, Detail $variant)
    {
        $data = $this->prepareUnitAssociation($data);

        if (!empty($data['prices'])) {
            $data['prices'] = $this->preparePriceAssociation(
                $data,
                $article,
                $variant,
                $article->getTax()
            );
        }

        $data = $this->prepareAttributeAssociation($data, $article, $variant);

        if (isset($data['configuratorOptions'])) {
            $data = $this->prepareConfigurator($data, $article, $variant);
        }

        return $data;
    }


    /**
     * @param $data
     * @param \Shopware\Models\Article\Article $article
     * @param \Shopware\Models\Article\Detail $variant
     * @param \Shopware\Models\Tax\Tax $tax
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    protected function preparePriceAssociation($data, ArticleModel $article, Detail $variant, Tax $tax)
    {
        $prices = $this->checkDataReplacement($variant->getPrices(), $data, 'prices', true);

        foreach ($data['prices'] as &$priceData) {
            /**@var $price Price*/
            $price = $this->getOneToManySubElement(
                $prices,
                $priceData,
                '\Shopware\Models\Article\Price'
            );

            if (empty($priceData['customerGroupKey'])) {
                $priceData['customerGroupKey'] = 'EK';
            }

            // load the customer group of the price definition
            $customerGroup = $this->getManager()
                ->getRepository('Shopware\Models\Customer\Group')
                ->findOneBy(array('key' => $priceData['customerGroupKey']));

            /** @var CustomerGroup $customerGroup */
            if (!$customerGroup instanceof CustomerGroup) {
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
            $priceData['article'] = $article;
            $priceData['detail'] = $variant;

            $price->fromArray($priceData);
        }

        return $prices;
    }


    /**
     * Resolves the passed configuratorOptions parameter for a single variant.
     * Each passed configurator option, has to be configured in the article configurator set.
     *
     * @param array $data
     * @param ArticleModel $article
     * @param Detail $variant
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function prepareConfigurator(array $data, ArticleModel $article, Detail $variant)
    {
        if (!$article->getConfiguratorSet()) {
            throw new ApiException\CustomValidationException('A configurator set has to be defined');
        }

        $availableGroups = $article->getConfiguratorSet()->getGroups();

        $options = new ArrayCollection();

        foreach($data['configuratorOptions'] as $optionData) {
            $availableGroup = $this->getAvailableGroup($availableGroups, array(
                'id' => $optionData['groupId'],
                'name' => $optionData['group']
            ));

            //group is in the article configurator set configured?
            if (!$availableGroup) {
                continue;
            }

            //check if the option is available in the configured article configurator set.
            $option = $this->getAvailableOption($availableGroup->getOptions(), array(
                'id'   => $optionData['optionId'],
                'name' => $optionData['option']
            ));

            if (!$option) {
                $option = new Option();
                $option->setPosition(0);
                $option->setName($option);
                $option->setGroup($availableGroup);
                $this->getManager()->persist($option);
            }
            $options->add($option);
        }

        $data['configuratorOptions'] = $options;

        $variant->setConfiguratorOptions($options);

        return $data;
    }


    /**
     * Checks if the passed group data is already existing in the passed array collection.
     * The group data are checked for "id" and "name".
     *
     * @param ArrayCollection $availableGroups
     * @param array $groupData
     * @return bool|Group
     */
    private function getAvailableGroup(ArrayCollection $availableGroups, array $groupData)
    {
        /**@var $availableGroup Option */
        foreach($availableGroups as $availableGroup) {
            if ($availableGroup->getName() == $groupData['name']
                || $availableGroup->getId() == $groupData['id']) {

                return $availableGroup;
            }
        }

        return false;
    }

    /**
     * Checks if the passed option data is already existing in the passed array collection.
     * The option data are checked for "id" and "name".
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $availableOptions
     * @param array $optionData
     * @return bool
     */
    private function getAvailableOption(ArrayCollection $availableOptions, array $optionData)
    {
        /**@var $availableOption Option */
        foreach($availableOptions as $availableOption) {
            if ($availableOption->getName() == $optionData['name']
                || $availableOption->getId() == $optionData['id']) {

                return $availableOption;
            }
        }

        return false;
    }

    /**
     * @param $data
     * @param ArticleModel $article
     * @param Detail $variant
     * @return mixed
     */
    protected function prepareAttributeAssociation($data, ArticleModel $article, Detail $variant)
    {
        if (!$variant->getAttribute()) {
            $data['attribute']['article'] = $article;
        }

        if (!isset($data['attribute'])) {
            return $data;
        }

        $data['attribute']['article'] = $article;
        return $data;
    }


    /**
     * Prepares the base variant data to save over doctrine.
     * Resolves the foreign keys for the passed unit data.
     *
     * @param $data
     * @return mixed
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function prepareUnitAssociation($data)
    {
        //if unit id passed, assign existing unit.
        if (!empty($data['unitId'])) {

            $data['unit'] = $this->getManager()->find('Shopware\Models\Article\Unit', $data['unitId']);

            if (empty($data['unit'])) {
                throw new ApiException\CustomValidationException(sprintf('Unit by id %s not found', $data['unitId']));
            }

        //new unit data send? create new unit for this variant
        } elseif (!empty($data['unit'])) {
            $data['unit'] = $this->updateUnitReference($data['unit']);
        }

        return $data;
    }

    /**
     * Try to find an existing unit by the passed parameters.
     * If no unit reference found, the function creates a new Unit entity.
     * The passed unit data will be assigned to the created or found Unit entity.
     *
     * @param $unitData
     * @return Unit
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function updateUnitReference($unitData)
    {
        $unitRepository = $this->getManager()->getRepository('\Shopware\Models\Article\Unit');

        //try to find an existing unit by the passed conditions "id", "name" or "unit"
        $unit = $unitRepository->findOneBy(
            $this->getUnitFindCondition($unitData)
        );

        //unit identifier send and unit not found? throw exception => Not allowed to create a new unit in this case
        if (!$unit && isset($unitData['id'])) {
            throw new ApiException\CustomValidationException(sprintf('Unit by id %s not found', $unitData['id']));
        }

        //to create a new unit, the unit name and unit is required. Otherwise we throw an exception
        if (!$unit && isset($unitData['name']) && isset($unitData['unit'])) {
            $unit = new Unit();
            $this->getManager()->persist($unit);
        } else if (!$unit) {
            throw new ApiException\CustomValidationException(sprintf('To create a unit you need to pass `name` and `unit`'));
        }

        $unit->fromArray($unitData);
        return $unit;
    }


    /**
     * Helper function returns the findOneBy condition
     * for the passed unit data.
     *
     * @param $data
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @return array
     */
    private function getUnitFindCondition($data)
    {
        if (isset($data['id'])) {
            return array('id' => $data['id']);
        }

        if (isset($data['unit'])) {
            return array('unit' => $data['unit']);
        }

        if (isset($data['name'])) {
            return array('name' => $data['name']);
        }

        throw new ApiException\CustomValidationException(sprintf('To create a unit you need to pass `name` and `unit`'));
    }

}
