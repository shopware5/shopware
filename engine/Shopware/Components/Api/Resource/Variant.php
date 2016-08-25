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
use Doctrine\Common\Collections\Collection;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Article\Article as ArticleModel;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Unit;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Tax\Tax;
use Shopware\Components\Api\BatchInterface;

/**
 * Variant API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Variant extends Resource implements BatchInterface
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
    protected function getArticleResource()
    {
        return $this->getResource('Article');
    }

    /**
     * @return Media
     */
    protected function getMediaResource()
    {
        return $this->getResource('Media');
    }

    /**
     * @param string $number
     * @param array $options
     * @return array|\Shopware\Models\Article\Detail
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
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @return array|\Shopware\Models\Article\Detail
     */
    public function getOne($id, array $options = [])
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->getVariantDetailQuery();
        $builder->andWhere('variants.id = :variantId')
                ->addOrderBy('variants.id', 'ASC')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC')
                ->setParameter('variantId', $id);

        /** @var $articleDetail \Shopware\Models\Article\Detail */
        $variant = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$variant) {
            throw new ApiException\NotFoundException("Variant by id $id not found");
        }

        if ($this->getResultMode() === self::HYDRATE_ARRAY) {
            if (isset($options['considerTaxInput']) && $options['considerTaxInput']) {
                $variant = $this->considerTaxInput($variant);
            }
        }

        return $variant;
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

        /** @var \Doctrine\DBAL\Query\QueryBuilder */
        $builder = $this->getRepository()->createQueryBuilder('detail');

        $builder->addSelect(['prices', 'attribute', 'customerGroup'])
                ->leftJoin('detail.prices', 'prices')
                ->innerJoin('prices.customerGroup', 'customerGroup')
                ->leftJoin('detail.attribute', 'attribute')
                ->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the article data
        $variants = $paginator->getIterator()->getArrayCopy();

        if ($this->getResultMode() === self::HYDRATE_ARRAY) {
            if (isset($options['considerTaxInput']) && $options['considerTaxInput']) {
                foreach ($variants as &$variant) {
                    $variant = $this->considerTaxInput($variant);
                }
            }
        }

        return ['data' => $variants, 'total' => $totalResult];
    }

    private function considerTaxInput($variant)
    {
        $tax = Shopware()->Db()->fetchOne(
            "SELECT tax
                 FROM s_core_tax
                     INNER JOIN s_articles
                         ON s_articles.taxID = s_core_tax.id
                         AND s_articles.id = :articleId",
            [':articleId' => $variant['articleId']]
        );

        if (empty($tax)) {
            throw new ApiException\CustomValidationException(
                sprintf("No article tax configured for variant: %s", $variant['id'])
            );
        }

        $variant['prices'] = $this->getArticleResource()->getTaxPrices(
            $variant['prices'],
            $tax
        );

        return $variant;
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
        $articleDetail = $this->getRepository()->findOneBy(['number' => $number]);

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
     * Convenience method to update a variant by number
     * @param string $number
     * @param array $params
     * @return Detail
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
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
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

        if (isset($data['purchasePrice']) && is_string($data['purchasePrice'])) {
            $data['purchasePrice'] = floatval(str_replace(',', '.', $data['purchasePrice']));
        }

        $data = $this->prepareAttributeAssociation($data, $article, $variant);

        if (isset($data['configuratorOptions'])) {
            $data = $this->prepareConfigurator($data, $article, $variant);
        }
        if (isset($data['images'])) {
            $data = $this->prepareImageAssociation($data, $article, $variant);
        }

        if (!empty($data['number']) && $data['number'] !== $variant->getNumber()) {
            $connection = Shopware()->Container()->get('dbal_connection');

            // Number changed, hence make sure it does not already exist in another variant
            $exists = $connection->fetchColumn('SELECT id FROM s_articles_details WHERE ordernumber = ?', [$data['number']]);
            if ($exists) {
                throw new ApiException\CustomValidationException(sprintf('A variant with the given order number "%s" already exists.', $data['number']));
            }
        }

        return $data;
    }

    /**
     * Resolves the passed images array for the current variant.
     * An image can be assigned to a variant over a media id of an existing article image
     * or over the link property which can contain a image link.
     * This image will be added automatically to the article.
     *
     * @param $data
     * @param ArticleModel $article
     * @param Detail $variant
     * @return mixed
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function prepareImageAssociation($data, ArticleModel $article, Detail $variant)
    {
        if (empty($data['images'])) {
            if ($variant->getImages()->count() > 0) {
                /**@var $image Image*/
                foreach ($variant->getImages() as $image) {
                    $mapping = $this->getVariantMappingOfImage($image, $variant);

                    if ($mapping) {
                        $this->getManager()->remove($mapping);
                    }
                }
            }
            return $data;
        }

        $images = $this->checkDataReplacement(
            $variant->getImages(),
            $data,
            'images',
            true
        );
        foreach ($data['images'] as $imageData) {

            //check if a media id was passed.
            if (isset($imageData['mediaId'])) {

                //first check if the media object is already assigned to the article
                $image = $this->getAvailableMediaImage(
                    $article->getImages(),
                    $imageData['mediaId']
                );

                //media image isn't assigned to the article?
                if (!$image) {

                    //find the media object and convert it to an article image.
                    /**@var $media MediaModel*/
                    $media = $this->getManager()->find(
                        'Shopware\Models\Media\Media',
                        (int) $imageData['mediaId']
                    );

                    if (!$media) {
                        throw new ApiException\CustomValidationException(
                            sprintf('Media by id %s not found', (int) $imageData['mediaId'])
                        );
                    }

                    $image = $this->getArticleResource()->createNewArticleImage(
                        $article, $media
                    );
                }
            } elseif (isset($imageData['link'])) {

                //check if an url passed and upload the passed image url and create a new article image.
                $media = $this->getMediaResource()->internalCreateMediaByFileLink(
                    $imageData['link']
                );
                $image = $this->getArticleResource()->createNewArticleImage(
                    $article, $media
                );
            } else {
                throw new ApiException\CustomValidationException("One of the passed variant images doesn't contains a mediaId or link property!");
            }

            $variantImage = $this->createVariantImage(
                $image,
                $variant
            );

            $this->createImageMappingForOptions(
                $variant->getConfiguratorOptions(),
                $image
            );

            $images->add($variantImage);
        }

        $data['images'] = $images;
        $variant->setImages($images);
        return $data;
    }

    /**
     * Helper function which creates a variant image for the passed article image.
     * @param Image $articleImage
     * @param Detail $variant
     * @return Image
     */
    public function createVariantImage(Image $articleImage, Detail $variant)
    {
        $variantImage = new Image();
        $variantImage->setParent($articleImage);
        $variantImage->setArticleDetail($variant);
        $variantImage->setPosition($articleImage->getPosition());
        $variantImage->setMain($articleImage->getMain());
        $variantImage->setExtension($articleImage->getExtension());

        return $variantImage;
    }

    /**
     * Helper function which returns a single image mapping
     * for the passed variant image and variant model.
     *
     * @param Image $image
     * @param Detail $variant
     * @return null|Image\Mapping
     */
    protected function getVariantMappingOfImage(Image $image, Detail $variant)
    {
        $parent = $image->getParent();

        /**@var $mapping Image\Mapping*/
        foreach ($parent->getMappings() as $mapping) {
            $match = true;

            /**@var $rule Image\Rule*/
            foreach ($mapping->getRules() as $rule) {
                $option = $this->getCollectionElementByProperty(
                    $variant->getConfiguratorOptions(),
                    'id',
                    $rule->getOption()->getId()
                );
                if (!$option) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $mapping;
            }
        }

        return null;
    }

    /**
     * @param Collection|array $options
     * @param Image $image
     * @return Image\Mapping
     */
    public function createImageMappingForOptions($options, Image $image)
    {
        $mapping = new Image\Mapping();
        $mapping->setImage($image);
        foreach ($options as $option) {
            $rule = new Image\Rule();
            $rule->setMapping($mapping);
            $rule->setOption($option);
            $mapping->getRules()->add($rule);
        }
        $image->getMappings()->add($mapping);

        return $mapping;
    }

    /**
     * @param Collection|array $availableImages
     * @param $mediaId
     * @return bool|Image
     */
    private function getAvailableMediaImage($availableImages, $mediaId)
    {
        /**@var $image Image*/
        foreach ($availableImages as $image) {
            if ($image->getMedia()->getId() == $mediaId) {
                return $image;
            }
        }
        return false;
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

            if (empty($priceData['from']) && $price->getFrom() == 0) {
                $priceData['from'] = 1;
            }

            // load the customer group of the price definition
            $customerGroup = $this->getManager()
                ->getRepository('Shopware\Models\Customer\Group')
                ->findOneBy(array('key' => $priceData['customerGroupKey']));

            /** @var CustomerGroup $customerGroup */
            if (!$customerGroup instanceof CustomerGroup) {
                throw new ApiException\CustomValidationException(sprintf('Customer Group by key %s not found', $priceData['customerGroupKey']));
            }

            $priceData['customerGroup'] = $customerGroup;
            $priceData['article'] = $article;
            $priceData['detail'] = $variant;

            $priceData = $this->mergePriceData($priceData, $tax);

            $price->fromArray($priceData);
        }

        return $prices;
    }

    /**
     * Calculates and merges the numeric values of the Price entity
     *
     * @param $priceData
     * @param $tax
     * @return mixed
     * @throws ApiException\CustomValidationException
     */
    private function mergePriceData($priceData, $tax)
    {
        if (array_key_exists('from', $priceData)) {
            $priceData['from'] = intval($priceData['from']);
            if ($priceData['from'] <= 0) {
                throw new ApiException\CustomValidationException(sprintf('Invalid Price "from" value'));
            }
        }
        if (array_key_exists('to', $priceData)) {
            $priceData['to'] = intval($priceData['to']);
            // if the "to" value isn't numeric, set the place holder "beliebig"
            if ($priceData['to'] <= 0) {
                $priceData['to'] = 'beliebig';
            }
        }

        foreach (['price', 'pseudoPrice', 'percent'] as $key) {
            if (array_key_exists($key, $priceData)) {
                $priceData[$key] = floatval(str_replace(",", ".", $priceData[$key]));
            }
        }

        if ($priceData['customerGroup']->getTaxInput()) {
            if (array_key_exists('price', $priceData)) {
                $priceData['price'] = $priceData['price'] / (100 + $tax->getTax()) * 100;
            }
            if (array_key_exists('pseudoPrice', $priceData)) {
                $priceData['pseudoPrice'] = $priceData['pseudoPrice'] / (100 + $tax->getTax()) * 100;
            }
        }

        return $priceData;
    }


    /**
     * Resolves the passed configuratorOptions parameter for a single variant.
     * Each passed configurator option, has to be configured in the article configurator set.
     *
     * @param array $data
     * @param ArticleModel $article
     * @param Detail $variant
     * @return Collection
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    protected function prepareConfigurator(array $data, ArticleModel $article, Detail $variant)
    {
        if (!$article->getConfiguratorSet()) {
            throw new ApiException\CustomValidationException('A configurator set has to be defined');
        }

        $availableGroups = $article->getConfiguratorSet()->getGroups();

        $options = new ArrayCollection();

        foreach ($data['configuratorOptions'] as $optionData) {
            $availableGroup = $this->getAvailableGroup($availableGroups, [
                'id' => $optionData['groupId'],
                'name' => $optionData['group']
            ]);

            //group is in the article configurator set configured?
            if (!$availableGroup) {
                continue;
            }

            //check if the option is available in the configured article configurator set.
            $option = $this->getAvailableOption($availableGroup->getOptions(), [
                'id'   => $optionData['optionId'],
                'name' => $optionData['option']
            ]);

            if (!$option) {
                if (!$optionData['option']) {
                    throw new ApiException\CustomValidationException('A new configurator option requires a name');
                }

                $option = new Option();
                $option->setPosition(0);
                $option->setName($optionData['option']);
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
     * @param Collection|array $availableGroups
     * @param array $groupData
     * @return bool|Group
     */
    private function getAvailableGroup($availableGroups, array $groupData)
    {
        /**@var $availableGroup Option */
        foreach ($availableGroups as $availableGroup) {
            if (($availableGroup->getName() == $groupData['name'] && $groupData['name'] !== null)
                || ($availableGroup->getId() == $groupData['id']) && $groupData['id'] !== null) {
                return $availableGroup;
            }
        }

        return false;
    }

    /**
     * Checks if the passed option data is already existing in the passed array collection.
     * The option data are checked for "id" and "name".
     *
     * @param Collection|array $availableOptions
     * @param array $optionData
     * @return bool
     */
    private function getAvailableOption($availableOptions, array $optionData)
    {
        /**@var $availableOption Option */
        foreach ($availableOptions as $availableOption) {
            if (($availableOption->getName() == $optionData['name'] && $optionData['name'] !== null)
                || ($availableOption->getId() == $optionData['id'] && $optionData['id'] !== null)) {
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
        } elseif (!$unit) {
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
            return ['id' => $data['id']];
        }

        if (isset($data['unit'])) {
            return ['unit' => $data['unit']];
        }

        if (isset($data['name'])) {
            return ['name' => $data['name']];
        }

        throw new ApiException\CustomValidationException(sprintf('To create a unit you need to pass `name` and `unit`'));
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
        } elseif (isset($data['number'])) {
            try {
                $id = $this->getIdFromNumber($data['number']);
            } catch (ApiException\NotFoundException $e) {
                return false;
            }
        }

        if (!$id) {
            return false;
        }

        $model = $this->getManager()->find('Shopware\Models\Article\Detail', $id);

        if ($model) {
            return $id;
        }

        return false;
    }
}
