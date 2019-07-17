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

use Doctrine\ORM\Mapping\ClassMetadata;
use RuntimeException;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Category\ManualSorting;
use Shopware\Models\Media\Media as MediaModel;
use Shopware_Components_Translation as TranslationComponent;

/**
 * Category API Resource
 */
class Category extends Resource
{
    /**
     * @var TranslationComponent
     */
    private $translationComponent;

    public function __construct(TranslationComponent $translationComponent = null)
    {
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get('translation');
    }

    /**
     * @return \Shopware\Models\Category\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(CategoryModel::class);
    }

    /**
     * @param int $id
     *
     * @throws ApiException\ParameterMissingException
     * @throws ApiException\NotFoundException
     *
     * @return CategoryModel|array
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        $query = $this->getRepository()->getDetailQueryWithoutArticles($id);

        /** @var array $categoryResult */
        $categoryResult = $query->getOneOrNullResult($this->getResultMode());

        if (!$categoryResult) {
            throw new ApiException\NotFoundException(sprintf('Category by id %d not found', $id));
        }

        if ($this->getResultMode() === Resource::HYDRATE_ARRAY) {
            $category = $categoryResult[0] + $categoryResult;

            $query = $this->getManager()->createQuery('SELECT shop FROM Shopware\Models\Shop\Shop as shop');

            foreach ($query->getArrayResult() as $shop) {
                $translation = $this->translationComponent->read($shop['id'], 'category', $id);
                if (!empty($translation)) {
                    $translation['shopId'] = $shop['id'];
                    $category['translations'][$shop['id']] = $translation;
                }

                $attributeTranslation = $this->translationComponent->read($shop['id'], 's_categories_attributes', $id);
                if (!empty($attributeTranslation)) {
                    $attributeTranslation['shopId'] = $shop['id'];

                    if (!is_array($category['translations'][$shop['id']])) {
                        $category['translations'][$shop['id']] = [];
                    }

                    $category['translations'][$shop['id']] += $attributeTranslation;
                }
            }
        } else {
            /** @var CategoryModel $category */
            $category = $categoryResult[0];
        }

        return $category;
    }

    /**
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $query = $this->getRepository()->getListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        // Returns the total count of the query
        $totalResult = $paginator->count();

        // Returns the category data
        $categories = $paginator->getIterator()->getArrayCopy();

        return ['data' => $categories, 'total' => $totalResult];
    }

    /**
     * @throws ApiException\ValidationException
     * @throws \Exception
     *
     * @return CategoryModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $category = new CategoryModel();

        $params = $this->prepareCategoryData($params);
        $params = $this->prepareMediaData($params, $category);
        $params = $this->prepareManualSorting($params, $category);

        $category->fromArray($params);

        if (isset($params['id'])) {
            /** @var ClassMetaData $metaData */
            $metaData = $this->getManager()->getMetadataFactory()->getMetadataFor(CategoryModel::class);
            $metaData->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $category->setPrimaryIdentifier($params['id']);
        }

        $violations = $this->getManager()->validate($category);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($category);
        $this->flush();

        if (!empty($params['translations'])) {
            $this->writeTranslations($category->getId(), $params['translations']);
        }

        return $category;
    }

    /**
     * @param int $id
     *
     * @throws ApiException\ValidationException
     * @throws ApiException\NotFoundException
     * @throws ApiException\ParameterMissingException
     * @throws ApiException\CustomValidationException
     *
     * @return CategoryModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var CategoryModel|null $category */
        $category = $this->getRepository()->find($id);

        if (!$category) {
            throw new ApiException\NotFoundException(sprintf('Category by id %d not found', $id));
        }

        $params = $this->prepareCategoryData($params);
        $params = $this->prepareMediaData($params, $category);
        $params = $this->prepareManualSorting($params, $category);
        $category->fromArray($params);

        $violations = $this->getManager()->validate($category);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        if (!empty($params['translations'])) {
            $this->writeTranslations($category->getId(), $params['translations']);
        }

        return $category;
    }

    /**
     * @param int $id
     *
     * @throws ApiException\ParameterMissingException
     * @throws ApiException\NotFoundException
     *
     * @return CategoryModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException('id');
        }

        /** @var CategoryModel|null $category */
        $category = $this->getRepository()->find($id);

        if (!$category) {
            throw new ApiException\NotFoundException(sprintf('Category by id %d not found', $id));
        }

        $this->getManager()->remove($category);
        $this->flush();

        $connection = $this->getManager()->getConnection();

        $connection->delete('s_core_translations', [
            'objecttype' => 'category',
            'objectkey' => $id,
        ]);

        $connection->delete('s_core_translations', [
            'objecttype' => 's_categories_attributes',
            'objectkey' => $id,
        ]);

        return $category;
    }

    /**
     * Find a category by a given human readable path.
     * This will step through all categories from top to bottom and return the matching category.
     *
     * @param string $path   Path of the category to search separated by pipe. Eg. Deutsch|Foo|Bar
     * @param bool   $create Should categories be created?
     *
     * @throws RuntimeException
     *
     * @return CategoryModel|null
     */
    public function findCategoryByPath($path, $create = false)
    {
        if (empty($path)) {
            return null;
        }

        $categoryModel = null;
        $categoryNames = explode('|', $path);

        $parentId = 1; // The root node
        $parent = null;

        foreach ($categoryNames as $categoryName) {
            if (empty($categoryName)) {
                break;
            }

            /** @var CategoryModel|null $categoryModel */
            $categoryModel = $this->getRepository()->findOneBy(['name' => $categoryName, 'parentId' => $parentId]);
            if (!$categoryModel) {
                if (!$create) {
                    return null;
                }

                if ($parent === null) {
                    /** @var CategoryModel|null $parent */
                    $parent = $this->getRepository()->find($parentId);
                    if (!$parent) {
                        throw new RuntimeException(sprintf('Could not find parent %s', $parentId));
                    }
                }

                $categoryModel = new CategoryModel();
                $this->getManager()->persist($categoryModel);
                $categoryModel->setParent($parent);
                $categoryModel->setName($categoryName);
            }

            $parentId = $categoryModel->getId();
            $parent = $categoryModel;
        }

        return $categoryModel;
    }

    /**
     * @param int   $categoryId
     * @param array $translations
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws ApiException\CustomValidationException
     */
    public function writeTranslations($categoryId, $translations)
    {
        $attributes = $this->getAttributeProperties();

        foreach ($translations as $translation) {
            $shop = $this->getManager()->find(\Shopware\Models\Shop\Shop::class, $translation['shopId']);
            if (!$shop) {
                throw new ApiException\CustomValidationException(sprintf('Shop by id %s not found', $translation['shopId']));
            }

            $attributeTranslation = array_intersect_key($translation, array_flip($attributes));

            $this->translationComponent->write($shop->getId(), 'category', $categoryId, array_diff_key($translation, array_flip($attributes)));

            if (!empty($attributeTranslation)) {
                $this->translationComponent->write($shop->getId(), 's_categories_attributes', $categoryId, $attributeTranslation);
            }
        }
    }

    /**
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function prepareCategoryData(array $params)
    {
        if (!isset($params['name'])) {
            throw new ApiException\CustomValidationException('A name is required');
        }

        // In order to have a consistent interface within the REST Api, one might want
        // to set the parent category by using 'parentId' instead of 'parent'
        if (isset($params['parentId']) && !isset($params['parent'])) {
            $params['parent'] = $params['parentId'];
        }

        if (!empty($params['parent'])) {
            $params['parent'] = Shopware()->Models()->getRepository(CategoryModel::class)->find($params['parent']);
            if (!$params['parent']) {
                throw new ApiException\CustomValidationException(sprintf('Parent by id %s not found', $params['parent']));
            }
        } else {
            unset($params['parent']);
        }

        if (!empty($params['attribute'])) {
            foreach ($params['attribute'] as $key => $value) {
                if (is_numeric($key)) {
                    $params['attribute']['attribute' . $key] = $value;
                    unset($params[$key]);
                }
            }
        }

        return $params;
    }

    /**
     * @throws ApiException\CustomValidationException
     *
     * @return array
     */
    private function prepareMediaData(array $data, CategoryModel $categoryModel)
    {
        if (!isset($data['media'])) {
            return $data;
        }

        $media = null;

        if (isset($data['media']['link'])) {
            /** @var Media $mediaResource */
            $mediaResource = $this->getContainer()->get('shopware.api.media');
            /** @var MediaModel $media */
            $media = $mediaResource->internalCreateMediaByFileLink($data['media']['link']);
        } elseif (!empty($data['media']['mediaId'])) {
            $media = $this->getManager()->find(MediaModel::class, (int) $data['media']['mediaId']);

            if (!($media instanceof MediaModel)) {
                throw new ApiException\CustomValidationException(sprintf('Media by mediaId %s not found', $data['media']['mediaId']));
            }
        }

        $categoryModel->setMedia($media);
        unset($data['media']);

        return $data;
    }

    private function prepareManualSorting(array $data, CategoryModel $category): array
    {
        if (!array_key_exists('manualSorting', $data)) {
            return $data;
        }

        if ($category->getId() === null) {
            throw new ApiException\CustomValidationException(sprintf('Property manualSorting is only allowed on update'));
        }

        $collection = [];
        $connection = $this->getManager()->getConnection();

        foreach ($data['manualSorting'] as $sorting) {
            if (!isset($sorting['product_id'])) {
                throw new ApiException\CustomValidationException(sprintf('Field product_id is missing in manualSorting array'));
            }

            if (!isset($sorting['position'])) {
                throw new ApiException\CustomValidationException(sprintf('Field position is missing in manualSorting array'));
            }

            if (!$connection->fetchColumn('SELECT 1 FROM s_articles_categories_ro WHERE categoryID = ? AND articleID = ?', [
                $category->getId(),
                $sorting['product_id'],
            ])) {
                throw new ApiException\CustomValidationException(sprintf('Product with id %d is not assigned to the category', $sorting['product_id']));
            }

            $sortingObj = new ManualSorting();
            $sortingObj->setCategory($category);

            /** @var Product $product */
            $product = $this->getManager()->find(Product::class, $sorting['product_id']);

            $sortingObj->setProduct($product);
            $sortingObj->setPosition((int) $sorting['position']);

            $collection[] = $sortingObj;
        }

        $category->setManualSorting(null);
        $this->getManager()->flush($category);
        $category->setManualSorting($collection);

        unset($data['manualSorting']);

        return $data;
    }

    /**
     * Returns all none association property of the category class.
     *
     * @return array
     */
    private function getAttributeProperties()
    {
        /** @var \Shopware\Bundle\AttributeBundle\Service\CrudService $crud */
        $crud = $this->getContainer()->get('shopware_attribute.crud_service');
        $list = $crud->getList('s_categories_attributes');
        $fields = [];
        foreach ($list as $property) {
            $fields[] = '__attribute_' . $property->getColumnName();
        }

        return $fields;
    }
}
