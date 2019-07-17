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
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Configurator\Group;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Esd;
use Shopware\Models\Article\EsdSerial;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\Article as ArticleAttributeModel;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Tax\Tax;

/**
 * Variant API Resource
 */
class Variant extends Resource implements BatchInterface
{
    /**
     * @return \Shopware\Models\Article\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(Detail::class);
    }

    /**
     * @param string $number
     *
     * @return array|Detail
     */
    public function getOneByNumber($number, array $options = [])
    {
        $id = $this->getIdFromNumber($number);

        return $this->getOne($id, $options);
    }

    /**
     * @param int $id
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return array|Detail
     */
    public function getOne($id, array $options = [])
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $builder = $this->getRepository()->getVariantDetailQuery();
        $builder->addSelect('article')
                ->andWhere('variants.id = :variantId')
                ->addOrderBy('variants.id', 'ASC')
                ->addOrderBy('customerGroup.id', 'ASC')
                ->addOrderBy('prices.from', 'ASC')
                ->setParameter('variantId', $id);

        /** @var Detail|array $variant */
        $variant = $builder->getQuery()->getOneOrNullResult($this->getResultMode());

        if (!$variant) {
            throw new ApiException\NotFoundException(sprintf('Variant by id %d not found', $id));
        }

        if (($this->getResultMode() === self::HYDRATE_ARRAY)
            && isset($options['considerTaxInput'])
            && $options['considerTaxInput']
        ) {
            $variant = $this->considerTaxInput($variant);
        }

        return $variant;
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

        /** @var QueryBuilder $builder */
        $builder = $this->getRepository()->createQueryBuilder('detail');

        $builder->addSelect(['prices', 'attribute', 'partial article.{id,name,active,taxId}', 'customerGroup'])
                ->leftJoin('detail.prices', 'prices')
                ->innerJoin('prices.customerGroup', 'customerGroup')
                ->leftJoin('detail.attribute', 'attribute')
                ->innerJoin('detail.article', 'article')
                ->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();

        $query->setHydrationMode($this->getResultMode());

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the product data
        $variants = $paginator->getIterator()->getArrayCopy();

        if (($this->getResultMode() === self::HYDRATE_ARRAY)
            && isset($options['considerTaxInput'])
            && $options['considerTaxInput']
        ) {
            foreach ($variants as &$variant) {
                $variant = $this->considerTaxInput($variant);
            }
        }

        return ['data' => $variants, 'total' => $totalResult];
    }

    /**
     * Little helper function for the ...ByNumber methods
     *
     * @param string $number
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return int
     */
    public function getIdFromNumber($number)
    {
        if (empty($number)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var Detail|null $productVariant */
        $productVariant = $this->getRepository()->findOneBy(['number' => $number]);

        if (!$productVariant) {
            throw new ApiException\NotFoundException(sprintf('Variant by number %s not found', $number));
        }

        return $productVariant->getId();
    }

    /**
     * @param string $number
     *
     * @throws ApiException\ParameterMissingException
     * @throws ApiException\NotFoundException
     *
     * @return Detail
     */
    public function deleteByNumber($number)
    {
        $id = $this->getIdFromNumber($number);

        return $this->delete($id);
    }

    /**
     * @param int $id
     *
     * @throws ApiException\ParameterMissingException
     * @throws ApiException\NotFoundException
     *
     * @return Detail
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var Detail|null $productVariant */
        $productVariant = $this->getRepository()->find($id);

        if (!$productVariant) {
            throw new ApiException\NotFoundException(sprintf('Variant by id %d not found', $id));
        }

        if ($productVariant->getKind() === 1) {
            $productVariant->getArticle()->setMainDetail(null);
        }

        $this->getManager()->remove($productVariant);
        $this->flush();

        return $productVariant;
    }

    /**
     * Convenience method to update a variant by number
     *
     * @param string $number
     *
     * @return Detail
     */
    public function updateByNumber($number, array $params)
    {
        $id = $this->getIdFromNumber($number);

        return $this->update($id, $params);
    }

    /**
     * Updates a single variant entity.
     *
     * @param int $id
     *
     * @throws ApiException\ValidationException
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return Detail
     */
    public function update($id, array $params)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var Detail|null $variant */
        $variant = $this->getRepository()->find($id);

        if (!$variant) {
            throw new ApiException\NotFoundException(sprintf('Variant by id %d not found', $id));
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
     * Creates a new variant for an product.
     * This function requires an articleId in the params parameter.
     *
     * @throws ApiException\ValidationException
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return Detail
     */
    public function create(array $params)
    {
        $productId = $params['articleId'];

        if (empty($productId)) {
            throw new ApiException\ParameterMissingException('Passed parameter array does not contain an articleId property');
        }

        /** @var ProductModel|null $product */
        $product = $this->getManager()->find(ProductModel::class, $productId);

        if (!$product) {
            throw new ApiException\NotFoundException(sprintf('Product by id %d not found', $productId));
        }

        $variant = $this->internalCreate($params, $product);

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
     * Used from the 'Article' resource. This function supports
     * to pass an updated product entity which isn't updated in the database.
     * Required for the 'Article' resource if the product data is already updated
     * in the entity but not in the database.
     *
     * @param int $id
     *
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     *
     * @return Detail
     */
    public function internalUpdate($id, array $data, ProductModel $article)
    {
        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var Detail|null $variant */
        $variant = $this->getRepository()->find($id);

        if (!$variant) {
            throw new ApiException\NotFoundException(sprintf('Variant by id %d not found', $id));
        }

        $variant->setArticle($article);

        $data = $this->prepareData($data, $article, $variant);

        $variant->fromArray($data);

        return $variant;
    }

    /**
     * Create function for the internal usage of the rest api.
     * Used from the 'Article' resource. This function supports
     * to pass an updated product entity which isn't updated in the database.
     * Required for the 'Article' resource if the product data is already updated
     * in the entity but not in the database.
     *
     * @throws ApiException\ValidationException
     *
     * @return Detail
     */
    public function internalCreate(array $data, ProductModel $article)
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
     * Interface which allows to use the data preparation in the 'Article' resource for the main variant.
     *
     * @return array|mixed
     */
    public function prepareMainVariantData(array $data, ProductModel $article, Detail $variant)
    {
        return $this->prepareData($data, $article, $variant);
    }

    /**
     * Helper function which creates a variant image for the passed product image.
     *
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
     * @param Collection|array $options
     *
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
     * Returns the primary ID of any data set.
     *
     * {@inheritdoc}
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

        $model = $this->getManager()->find(Detail::class, $id);

        if ($model) {
            return $id;
        }

        return false;
    }

    /**
     * @return Article
     */
    protected function getArticleResource()
    {
        return $this->getContainer()->get('shopware.api.article');
    }

    /**
     * @return Media
     */
    protected function getMediaResource()
    {
        return $this->getContainer()->get('shopware.api.media');
    }

    /**
     * Resolves the association data for a single variant.
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array|mixed
     */
    protected function prepareData(array $data, ProductModel $article, Detail $variant)
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
            $data['purchasePrice'] = (float) str_replace(',', '.', $data['purchasePrice']);
        }

        $data = $this->prepareAttributeAssociation($data, $article, $variant);

        if (isset($data['configuratorOptions'])) {
            $data = $this->prepareConfigurator($data, $article, $variant);
        }
        if (isset($data['images'])) {
            $data = $this->prepareImageAssociation($data, $article, $variant);
        }
        if (isset($data['esd'])) {
            $data = $this->prepareEsdAssociation($data, $variant);
        }

        if (!empty($data['number']) && $data['number'] !== $variant->getNumber()) {
            // Number changed, hence make sure it does not already exist in another variant
            $exists = $this->getContainer()
                ->get('dbal_connection')
                ->fetchColumn('SELECT id FROM s_articles_details WHERE ordernumber = ?', [$data['number']]);
            if ($exists) {
                throw new ApiException\CustomValidationException(sprintf('A variant with the given order number "%s" already exists.', $data['number']));
            }
        }

        return $data;
    }

    /**
     * Resolves the passed images array for the current variant.
     * An image can be assigned to a variant over a media id of an existing product image
     * or over the link property which can contain a image link.
     * This image will be added automatically to the product.
     *
     * @param array $data
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    protected function prepareImageAssociation($data, ProductModel $article, Detail $variant)
    {
        if (empty($data['images'])) {
            if ($variant->getImages()->count() > 0) {
                /** @var Image $image */
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
            // Check if a media id was passed.
            if (isset($imageData['mediaId'])) {
                // First check if the media object is already assigned to the product
                $image = $this->getAvailableMediaImage(
                    $article->getImages(),
                    $imageData['mediaId']
                );

                // Media image isn't assigned to the product?
                if (!$image) {
                    // Find the media object and convert it to an product image.
                    /** @var MediaModel|null $media */
                    $media = $this->getManager()->find(MediaModel::class, (int) $imageData['mediaId']);

                    if (!$media) {
                        throw new ApiException\CustomValidationException(
                            sprintf('Media by id %s not found', (int) $imageData['mediaId'])
                        );
                    }

                    $image = $this->getArticleResource()->createNewArticleImage($article, $media);
                }
            } elseif (isset($imageData['link'])) {
                // Check if an url passed and upload the passed image url and create a new product image.
                $media = $this->getMediaResource()->internalCreateMediaByFileLink(
                    $imageData['link']
                );
                $image = $this->getArticleResource()->createNewArticleImage($article, $media);
            } else {
                throw new ApiException\CustomValidationException("One of the passed variant images doesn't contain a mediaId or link property!");
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
     * Helper function which returns a single image mapping
     * for the passed variant image and variant model.
     *
     * @return Image\Mapping|null
     */
    protected function getVariantMappingOfImage(Image $image, Detail $variant)
    {
        $parent = $image->getParent();

        /** @var Image\Mapping $mapping */
        foreach ($parent->getMappings() as $mapping) {
            $match = true;

            /** @var Image\Rule $rule */
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
     * @param array $data
     *
     * @throws ApiException\CustomValidationException
     *
     * @return Collection
     */
    protected function preparePriceAssociation($data, ProductModel $article, Detail $variant, Tax $tax)
    {
        $prices = $this->checkDataReplacement($variant->getPrices(), $data, 'prices', true);

        foreach ($data['prices'] as &$priceData) {
            /** @var Price $price */
            $price = $this->getOneToManySubElement(
                $prices,
                $priceData,
                Price::class
            );

            if (empty($priceData['customerGroupKey'])) {
                $priceData['customerGroupKey'] = 'EK';
            }

            if (empty($priceData['from']) && (int) $price->getFrom() === 0) {
                $priceData['from'] = 1;
            }

            // Load the customer group of the price definition
            $customerGroup = $this->getManager()
                ->getRepository(CustomerGroup::class)
                ->findOneBy(['key' => $priceData['customerGroupKey']]);

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
     * Resolves the passed configuratorOptions parameter for a single variant.
     * Each passed configurator option, has to be configured in the product configurator set.
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    protected function prepareConfigurator(array $data, ProductModel $article, Detail $variant)
    {
        if (!$article->getConfiguratorSet()) {
            throw new ApiException\CustomValidationException('A configurator set has to be defined');
        }

        $availableGroups = $article->getConfiguratorSet()->getGroups();

        $options = new ArrayCollection();

        foreach ($data['configuratorOptions'] as $optionData) {
            $availableGroup = $this->getAvailableGroup($availableGroups, [
                'id' => $optionData['groupId'],
                'name' => $optionData['group'],
            ]);

            // Group is in the product configurator set configured?
            if (!$availableGroup) {
                continue;
            }

            // Check if the option is available in the configured product configurator set.
            $option = $this->getAvailableOption($availableGroup->getOptions(), [
                'id' => $optionData['optionId'],
                'name' => $optionData['option'],
            ]);

            if (!$option) {
                if (!$optionData['option']) {
                    throw new ApiException\CustomValidationException('A new configurator option requires a name');
                }

                $option = new Option();
                $option->setPosition(0);
                if (array_key_exists('position', $optionData)) {
                    $option->setPosition((int) $optionData['position']);
                }
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
     * @param array $data
     *
     * @return array
     */
    protected function prepareAttributeAssociation($data, ProductModel $article, Detail $variant)
    {
        if (!isset($data['attribute']) && !$variant->getAttribute()) {
            // Create empty attributes if none provided
            $data['attribute'] = new ArticleAttributeModel();
        }

        return $data;
    }

    /**
     * Prepares the base variant data to save over doctrine.
     * Resolves the foreign keys for the passed unit data.
     *
     * @param array $data
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    protected function prepareUnitAssociation($data)
    {
        // If unit id passed, assign existing unit.
        if (!empty($data['unitId'])) {
            $data['unit'] = $this->getManager()->find(Unit::class, $data['unitId']);

            if (empty($data['unit'])) {
                throw new ApiException\CustomValidationException(sprintf('Unit by id %s not found', $data['unitId']));
            }

            // New unit data send? create new unit for this variant
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
     * @param array $unitData
     *
     * @throws ApiException\CustomValidationException
     *
     * @return Unit
     */
    protected function updateUnitReference($unitData)
    {
        $unitRepository = $this->getManager()->getRepository(Unit::class);

        // Try to find an existing unit by the passed conditions "id", "name" or "unit"
        /** @var Unit|null $unit */
        $unit = $unitRepository->findOneBy(
            $this->getUnitFindCondition($unitData)
        );

        // Unit identifier send and unit not found? throw exception => Not allowed to create a new unit in this case
        if (!$unit && isset($unitData['id'])) {
            throw new ApiException\CustomValidationException(sprintf('Unit by id %s not found', $unitData['id']));
        }

        // To create a new unit, the unit name and unit is required. Otherwise we throw an exception
        if (!$unit && isset($unitData['name'], $unitData['unit'])) {
            $unit = new Unit();
        } elseif (!$unit) {
            throw new ApiException\CustomValidationException(sprintf('To create a unit you need to pass `name` and `unit`'));
        }

        $unit->fromArray($unitData);

        return $unit;
    }

    /**
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function considerTaxInput(array $variant)
    {
        $tax = Shopware()->Db()->fetchOne(
            'SELECT tax
                 FROM s_core_tax
                     INNER JOIN s_articles
                         ON s_articles.taxID = s_core_tax.id
                         AND s_articles.id = :articleId',
            [':articleId' => $variant['articleId']]
        );

        if (empty($tax)) {
            throw new ApiException\CustomValidationException(
                sprintf('No product tax configured for variant: %s', $variant['id'])
            );
        }

        $variant['prices'] = $this->getArticleResource()->getTaxPrices(
            $variant['prices'],
            (float) $tax
        );

        return $variant;
    }

    /**
     * @param Collection|array $availableImages
     * @param int              $mediaId
     *
     * @return bool|Image
     */
    private function getAvailableMediaImage($availableImages, $mediaId)
    {
        /** @var Image $image */
        foreach ($availableImages as $image) {
            if ((int) $image->getMedia()->getId() === (int) $mediaId) {
                return $image;
            }
        }

        return false;
    }

    /**
     * Calculates and merges the numeric values of the Price entity
     *
     * @throws ApiException\CustomValidationException
     */
    private function mergePriceData(array $priceData, Tax $tax)
    {
        if (array_key_exists('from', $priceData)) {
            $priceData['from'] = (int) $priceData['from'];
            if ($priceData['from'] <= 0) {
                throw new ApiException\CustomValidationException(sprintf('Invalid Price "from" value'));
            }
        }
        if (array_key_exists('to', $priceData)) {
            $priceData['to'] = (int) $priceData['to'];
            // if the "to" value isn't numeric, set the place holder "beliebig"
            if ($priceData['to'] <= 0) {
                $priceData['to'] = 'beliebig';
            }
        }

        foreach (['price', 'pseudoPrice', 'percent'] as $key) {
            if (array_key_exists($key, $priceData)) {
                $priceData[$key] = (float) str_replace(',', '.', $priceData[$key]);
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
     * Checks if the passed group data is already existing in the passed array collection.
     * The group data are checked for "id" and "name".
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Group> $availableGroups
     *
     * @return false|Group
     */
    private function getAvailableGroup($availableGroups, array $groupData)
    {
        // Convert string to lower case to avoid problems with case insensitivity in database
        // vs case sensitivity in PHP
        $groupName = mb_strtolower($groupData['name']);

        /** @var Group $availableGroup */
        foreach ($availableGroups as $availableGroup) {
            if (($groupData['id'] !== null && (int) $availableGroup->getId() === (int) $groupData['id'])
                || (mb_strtolower($availableGroup->getName()) === $groupName && $groupData['name'] !== null)) {
                return $availableGroup;
            }
        }

        return false;
    }

    /**
     * Checks if the passed option data is already existing in the passed array collection.
     * The option data are checked for "id" and "name".
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Configurator\Option> $availableOptions
     *
     * @return bool|Option
     */
    private function getAvailableOption($availableOptions, array $optionData)
    {
        // Convert string to lower case to avoid problems with case insensitivity in database
        // vs case sensitivity in PHP
        $optionName = mb_strtolower($optionData['name']);

        /** @var Option $availableOption */
        foreach ($availableOptions as $availableOption) {
            if ((mb_strtolower($availableOption->getName()) === $optionName && $optionData['name'] !== null)
                || ((int) $availableOption->getId() === (int) $optionData['id'] && $optionData['id'] !== null)) {
                return $availableOption;
            }
        }

        return false;
    }

    /**
     * Helper function returns the findOneBy condition
     * for the passed unit data.
     *
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function getUnitFindCondition(array $data)
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
     * @param array $data
     *
     * @return array
     */
    private function prepareEsdAssociation($data, Detail $variant)
    {
        if (is_array($data['esd'])) {
            $esd = $variant->getEsd();

            // Use already uploaded download file
            if (!isset($data['esd']['reuse'])) {
                $data['esd']['reuse'] = false;
            }

            if (!$esd) {
                $esd = new Esd();
                $esd->setArticleDetail($variant);
            }

            if (isset($data['esd']['file'])) {
                $file = $this->getMediaResource()->load($data['esd']['file']);
                $fileName = pathinfo($data['esd']['file'], PATHINFO_FILENAME);
                $fileExt = pathinfo($data['esd']['file'], PATHINFO_EXTENSION);

                $esdDir = Shopware()->DocPath('files_' . Shopware()->Config()->get('sESDKEY'));

                // File already exists?
                if (file_exists($esdDir . '/' . $fileName . '.' . $fileExt) && !$data['esd']['reuse']) {
                    $saveFileName = uniqid($fileName) . '.' . $fileExt;
                } else {
                    $saveFileName = $fileName . '.' . $fileExt;
                }
                $saveFile = $esdDir . '/' . $saveFileName;

                copy($file, $saveFile);
                @unlink($file);
                $data['esd']['file'] = $saveFileName;
            }

            if (isset($data['esd']['serials'])) {
                $data = $this->prepareEsdSerialsAssociation($data, $esd);
            }

            $esd->fromArray($data['esd']);
            $variant->setEsd($esd);
        } elseif ($data['esd'] === null) {
            $variant->setEsd(null);
        }

        unset($data['esd']);

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function prepareEsdSerialsAssociation($data, Esd $esd)
    {
        // Remove old serials
        /** @var EsdSerial $serial */
        foreach ($esd->getSerials() as $serial) {
            $found = false;
            foreach ($data['esd']['serials'] as $newSerial) {
                if ($newSerial['serialnumber'] === $serial->getSerialnumber()) {
                    $serial->fromArray($newSerial);
                    $found = true;
                    break;
                }
            }

            if ($found === false) {
                $this->manager->remove($serial);
                $esd->getSerials()->removeElement($serial);
            }
        }

        // Add new items
        foreach ($data['esd']['serials'] as $newSerial) {
            $found = false;

            /** @var EsdSerial $serial */
            foreach ($esd->getSerials() as $serial) {
                if ($newSerial['serialnumber'] === $serial->getSerialnumber()) {
                    $found = true;
                    break;
                }
            }

            if ($found === false) {
                $newSerialModel = new EsdSerial();
                $newSerialModel->fromArray($newSerial);
                $newSerialModel->setEsd($esd);
                $this->getManager()->persist($newSerialModel);
                $esd->getSerials()->add($newSerialModel);
            }
        }

        unset($data['esd']['serials']);

        return $data;
    }
}
