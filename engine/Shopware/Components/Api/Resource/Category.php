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

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMInvalidArgumentException;
use RuntimeException;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Category\Category as CategoryModel;
use Shopware\Models\Category\ManualSorting;
use Shopware\Models\Category\Repository;
use Shopware\Models\Media\Media as MediaModel;
use Shopware\Models\Shop\Shop as ShopModel;
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
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get(TranslationComponent::class);
    }

    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(CategoryModel::class);
    }

    /**
     * @param int $id
     *
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return CategoryModel|array
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $query = $this->getRepository()->getDetailQueryWithoutArticles($id);

        $categoryResult = $query->getOneOrNullResult($this->getResultMode());

        if (!$categoryResult) {
            throw new NotFoundException(sprintf('Category by id %d not found', $id));
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

                    if (!\is_array($category['translations'][$shop['id']])) {
                        $category['translations'][$shop['id']] = [];
                    }

                    $category['translations'][$shop['id']] += $attributeTranslation;
                }
            }
        } else {
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
     * @throws ValidationException
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
            $metaData = $this->getManager()->getMetadataFactory()->getMetadataFor(CategoryModel::class);
            $metaData->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
            $category->setPrimaryIdentifier($params['id']);
        }

        $violations = $this->getManager()->validate($category);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
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
     * @throws ValidationException
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws CustomValidationException
     *
     * @return CategoryModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $category = $this->getRepository()->find($id);

        if (!$category) {
            throw new NotFoundException(sprintf('Category by id %d not found', $id));
        }

        $params = $this->prepareCategoryData($params);
        $params = $this->prepareMediaData($params, $category);
        $params = $this->prepareManualSorting($params, $category);
        $category->fromArray($params);

        $violations = $this->getManager()->validate($category);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
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
     * @throws ParameterMissingException
     * @throws NotFoundException
     *
     * @return CategoryModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $category = $this->getRepository()->find($id);

        if (!$category) {
            throw new NotFoundException(sprintf('Category by id %d not found', $id));
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
     * Find a category by a given human-readable path.
     * This will step through all categories from top to bottom and return the matching category.
     *
     * @param string $path   Path of the category to search separated by pipe. E.g. Deutsch|Foo|Bar
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

            $categoryModel = $this->getRepository()->findOneBy(['name' => $categoryName, 'parentId' => $parentId]);
            if (!$categoryModel) {
                if (!$create) {
                    return null;
                }

                if ($parent === null) {
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
     * @throws ORMException
     * @throws ORMInvalidArgumentException
     * @throws CustomValidationException
     */
    public function writeTranslations($categoryId, $translations)
    {
        $attributes = $this->getAttributeProperties();

        foreach ($translations as $translation) {
            $shop = $this->getManager()->find(ShopModel::class, $translation['shopId']);
            if (!$shop) {
                throw new CustomValidationException(sprintf('Shop by id %s not found', $translation['shopId']));
            }

            $attributeTranslation = array_intersect_key($translation, array_flip($attributes));

            $this->translationComponent->write($shop->getId(), 'category', $categoryId, array_diff_key($translation, array_flip($attributes)));

            if (!empty($attributeTranslation)) {
                $this->translationComponent->write($shop->getId(), 's_categories_attributes', $categoryId, $attributeTranslation);
            }
        }
    }

    /**
     * @throws CustomValidationException
     */
    private function prepareCategoryData(array $params): array
    {
        if (!isset($params['name'])) {
            throw new CustomValidationException('A name is required');
        }

        // In order to have a consistent interface within the REST Api, one might want
        // to set the parent category by using 'parentId' instead of 'parent'
        if (isset($params['parentId']) && !isset($params['parent'])) {
            $params['parent'] = $params['parentId'];
        }

        if (!empty($params['parent'])) {
            $params['parent'] = Shopware()->Models()->getRepository(CategoryModel::class)->find($params['parent']);
            if (!$params['parent']) {
                throw new CustomValidationException(sprintf('Parent by id %s not found', $params['parent']));
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
     * @param array<string, mixed> $data
     *
     * @throws CustomValidationException
     *
     * @return array<string, mixed>
     */
    private function prepareMediaData(array $data, CategoryModel $categoryModel): array
    {
        if (!isset($data['media'])) {
            return $data;
        }

        $media = null;

        if (isset($data['media']['link'])) {
            $mediaResource = $this->getContainer()->get(Media::class);
            $media = $mediaResource->internalCreateMediaByFileLink($data['media']['link']);
        } elseif (!empty($data['media']['mediaId'])) {
            $media = $this->getManager()->find(MediaModel::class, (int) $data['media']['mediaId']);

            if (!($media instanceof MediaModel)) {
                throw new CustomValidationException(sprintf('Media by mediaId %s not found', $data['media']['mediaId']));
            }
        }

        $categoryModel->setMedia($media);
        unset($data['media']);

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function prepareManualSorting(array $data, CategoryModel $category): array
    {
        if (!\array_key_exists('manualSorting', $data)) {
            return $data;
        }

        $collection = [];
        $connection = $this->getManager()->getConnection();

        foreach ($data['manualSorting'] as $sorting) {
            if (!isset($sorting['product_id'])) {
                throw new CustomValidationException(sprintf('Field product_id is missing in manualSorting array'));
            }

            if (!isset($sorting['position'])) {
                throw new CustomValidationException(sprintf('Field position is missing in manualSorting array'));
            }

            if (!$connection->fetchOne('SELECT 1 FROM s_articles_categories_ro WHERE categoryID = ? AND articleID = ?', [
                $category->getId(),
                $sorting['product_id'],
            ])) {
                throw new CustomValidationException(sprintf('Product with id %d is not assigned to the category', $sorting['product_id']));
            }

            $sortingObj = new ManualSorting();
            $sortingObj->setCategory($category);

            $product = $this->getManager()->find(Product::class, $sorting['product_id']);
            if (!$product instanceof Product) {
                throw new ModelNotFoundException(Product::class, $sorting['product_id']);
            }

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
     * @return string[]
     */
    private function getAttributeProperties(): array
    {
        $crud = $this->getContainer()->get(CrudServiceInterface::class);
        $list = $crud->getList('s_categories_attributes');
        $fields = [];
        foreach ($list as $property) {
            $fields[] = '__attribute_' . $property->getColumnName();
        }

        return $fields;
    }
}
