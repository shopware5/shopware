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
use Shopware\Models\Category\Category as CategoryModel;

/**
 * Category API Resource
 *
 * @category  Shopware
 * @package   Shopware\Components\Api\Resource
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Category extends Resource
{
    /**
     * @return \Shopware\Models\Category\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Category\Category');
    }


    /**
     * @param int $id
     * @return array|\Shopware\Models\Category\Category
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $filters = array(array('property' => 'id','expression' => '=','value' => $id));
        $query = $this->getRepository()->getListQuery($filters, array(), 1);

        /** @var $category \Shopware\Models\Category\Category */
        $category = $query->getOneOrNullResult($this->getResultMode());


        if (!$category) {
            throw new ApiException\NotFoundException("Category by id $id not found");
        }

        return $category;
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

        $query = $this->getRepository()->getListQuery($criteria, $orderBy, $limit, $offset);
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the category data
        $categories = $paginator->getIterator()->getArrayCopy();

        return array('data' => $categories, 'total' => $totalResult);
    }

    /**
     * @param array $params
     * @return \Shopware\Models\Category\Category
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareCategoryData($params);

        $category = new \Shopware\Models\Category\Category();
        $category->fromArray($params);

        if (isset($params['id'])) {
            $metaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Category\Category');
            $metaData->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            $category->setPrimaryIdentifier($params['id']);
        }

        $violations = $this->getManager()->validate($category);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($category);
        $this->flush();

        return $category;
    }

    /**
     * @param int $id
     * @param array $params
     * @return \Shopware\Models\Category\Category
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $category \Shopware\Models\Category\Category */
        $category = $this->getRepository()->find($id);

        if (!$category) {
            throw new ApiException\NotFoundException("Category by id $id not found");
        }

        $params = $this->prepareCategoryData($params);
        $category->fromArray($params);

        $violations = $this->getManager()->validate($category);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $category;
    }

    /**
     * @param int $id
     * @return \Shopware\Models\Category\Category
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $category \Shopware\Models\Category\Category */
        $category = $this->getRepository()->find($id);

        if (!$category) {
            throw new ApiException\NotFoundException("Category by id $id not found");
        }

        $this->getManager()->remove($category);
        $this->flush();

        return $category;
    }

    private function prepareCategoryData($params)
    {
        if (!isset($params['name'])) {
            throw new ApiException\CustomValidationException("A name is required");
        }

        // in order to have a consistent interface within the REST Api, one might want
        // to set the parent category by using 'parentId' instead of 'parent'
        if (isset($params['parentId']) && !isset($params['parent'])) {
            $params['parent'] = $params['parentId'];
        }

        if (!empty($params['parent'])) {
            $params['parent'] = Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->find($params['parent']);
            if (!$params['parent']) {
                throw new ApiException\CustomValidationException(sprintf("Parent by id %s not found", $params['parent']));
            }
        } else {
            unset($params['parent']);
        }

        if (!empty($params['attribute'])) {
            foreach ($params['attribute'] as $key => $value) {
                if (is_numeric($key)) {
                    $params['attribute']['attribute'.$key] = $value;
                    unset($params[$key]);
                }
            }
        }

        return $params;
    }


    /**
     * Find a category by a given human readable path.
     * This will step through all categories from top to bottom and return the matching category.
     *
     * @param string $path              Path of the category to search separated by pipe. Eg. Deutsch|Foo|Bar
     * @param boolean $create           Should categories be created?
     * @return null|Category
     * @throws \RuntimeException
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

            $categoryModel = $this->getRepository()->findOneBy(array('name' => $categoryName, 'parentId' => $parentId));
            if (!$categoryModel) {
                if (!$create) {
                    return null;
                }

                if (null === $parent) {
                    /** @var \Shopware\Models\Category\Category $parent */
                    $parent = $this->getRepository()->find($parentId);
                    if (!$parent) {
                        throw new \RuntimeException(sprintf('Could not find parent %s', $parentId));
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

        if (empty($categoryModel)) {
            return null;
        }

        return $categoryModel;
    }
}
