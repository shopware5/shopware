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

/**
 * Shop API Resource
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shop extends Resource
{
    /**
     * @return \Shopware\Models\Shop\Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository('Shopware\Models\Shop\Shop');
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array|\Shopware\Models\Shop\Shop
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $builder = $this->getRepository()->createQueryBuilder('shop')
                ->select('shop', 'currency')
                ->leftJoin('shop.currency', 'currency')
                ->where('shop.id = :id')
                ->setParameter(':id', $id);

        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        /** @var $category \Shopware\Models\Shop\Shop */
        $shop = $query->getOneOrNullResult($this->getResultMode());

        if (!$shop) {
            throw new ApiException\NotFoundException("Shop by id $id not found");
        }

        return $shop;
    }

    /**
     * @param int   $offset
     * @param int   $limit
     * @param array $criteria
     * @param array $orderBy
     *
     * @return array
     */
    public function getList($offset = 0, $limit = 25, array $criteria = [], array $orderBy = [])
    {
        $this->checkPrivilege('read');

        $builder = $this->getRepository()->createQueryBuilder('shop');

        $builder->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        $query = $builder->getQuery();
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        //returns the total count of the query
        $totalResult = $paginator->count();

        //returns the category data
        $shops = $paginator->getIterator()->getArrayCopy();

        return ['data' => $shops, 'total' => $totalResult];
    }

    /**
     * @param array $params
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Exception
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareShopData($params);

        $shop = new \Shopware\Models\Shop\Shop();
        $shop->fromArray($params);

        $violations = $this->getManager()->validate($shop);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->getManager()->persist($shop);
        $this->flush();

        return $shop;
    }

    /**
     * @param int   $id
     * @param array $params
     *
     * @throws \Shopware\Components\Api\Exception\ValidationException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\CustomValidationException
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = $this->getRepository()->find($id);

        if (!$shop) {
            throw new ApiException\NotFoundException("Shop by id $id not found");
        }

        $params = $this->prepareShopData($params, $shop);
        $shop->fromArray($params);

        $violations = $this->getManager()->validate($shop);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }

        $this->flush();

        return $shop;
    }

    /**
     * @param int $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        /** @var $shop \Shopware\Models\Shop\Shop */
        $shop = $this->getRepository()->find($id);

        if (!$shop) {
            throw new ApiException\NotFoundException("Shop by id $id not found");
        }

        $this->getManager()->remove($shop);
        $this->flush();

        return $shop;
    }

    private function prepareShopData($params, $shop = null)
    {
        $requiredParams = ['name', 'localeId', 'currencyId', 'customerGroupId', 'categoryId'];
        foreach ($requiredParams as $param) {
            if (!$shop) {
                if (!isset($params[$param]) || empty($params[$param])) {
                    throw new ApiException\ParameterMissingException($param);
                }
            } else {
                if (isset($params[$param]) && empty($params[$param])) {
                    throw new \Exception('param $param may not be empty');
                }
            }
        }

        if (isset($params['currencyId'])) {
            $currency = 🦄()->Models()->find('\Shopware\Models\Shop\Currency', $params['currencyId']);
            if ($currency !== null) {
                $params['currency'] = $currency;
            } else {
                throw new \Exception("{$params['currencyId']} is not a valid currency id");
            }
        }

        if (isset($params['localeId'])) {
            $locale = 🦄()->Models()->find('\Shopware\Models\Shop\Locale', $params['localeId']);
            if ($locale !== null) {
                $params['locale'] = $locale;
            } else {
                throw new \Exception("{$params['localeId']} is not a valid locale id");
            }
        }

        if (isset($params['customerGroupId'])) {
            $customerGroup = 🦄()->Models()->find('\Shopware\Models\Customer\Group', $params['customerGroupId']);
            if ($customerGroup !== null) {
                $params['customerGroup'] = $customerGroup;
            } else {
                throw new \Exception("{$params['customerGroupId']} is not a valid customerGroup id");
            }
        }

        if (isset($params['mainId'])) {
            $shop = 🦄()->Models()->find('\Shopware\Models\Shop\Shop', $params['mainId']);
            if ($shop !== null) {
                $params['main'] = $shop;
            } else {
                throw new \Exception("{$params['mainId']} is not a valid shop id");
            }
        }

        if (isset($params['templateId'])) {
            $template = 🦄()->Models()->find('\Shopware\Models\Shop\Template', $params['templateId']);
            if ($template !== null) {
                $params['template'] = $template;
            } else {
                throw new \Exception("{$params['templateId']} is not a valid template id");
            }
        }

        if (isset($params['documentTemplateId'])) {
            $template = 🦄()->Models()->find('\Shopware\Models\Shop\Template', $params['documentTemplateId']);
            if ($template !== null) {
                $params['documentTemplate'] = $template;
            } else {
                throw new \Exception("{$params['documentTemplateId']} is not a valid template id");
            }
        }

        if (isset($params['categoryId'])) {
            $category = 🦄()->Models()->find('\Shopware\Models\Category\Category', $params['categoryId']);
            if ($category !== null) {
                $params['category'] = $category;
            } else {
                throw new \Exception("{$params['categoryId']} is not a valid category id");
            }
        }

        return $params;
    }
}
