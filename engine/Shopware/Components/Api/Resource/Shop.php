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

use Doctrine\ORM\Query;
use Exception;
use RuntimeException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop as ShopModel;
use Shopware\Models\Shop\Template;

/**
 * Shop API Resource
 */
class Shop extends Resource
{
    /**
     * @return Repository
     */
    public function getRepository()
    {
        return $this->getManager()->getRepository(ShopModel::class);
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return array|ShopModel
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $builder = $this->getRepository()->createQueryBuilder('shop')
                ->select('shop', 'currency')
                ->leftJoin('shop.currency', 'currency')
                ->where('shop.id = :id')
                ->setParameter(':id', $id);

        $query = $builder->getQuery();
        $query->setHydrationMode($this->getResultMode());

        $shop = $query->getOneOrNullResult($this->getResultMode());

        if (!$shop) {
            throw new NotFoundException(sprintf('Shop by id %s not found', $id));
        }

        return $shop;
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

        $builder = $this->getRepository()->createQueryBuilder('shop');

        $builder->addFilter($criteria)
                ->addOrderBy($orderBy)
                ->setFirstResult($offset)
                ->setMaxResults($limit);
        /** @var Query<ShopModel|array<string, mixed>> $query */
        $query = $builder->getQuery();
        $query->setHydrationMode($this->resultMode);

        $paginator = $this->getManager()->createPaginator($query);

        // returns the total count of the query
        $totalResult = $paginator->count();

        // returns the category data
        $shops = iterator_to_array($paginator);

        return ['data' => $shops, 'total' => $totalResult];
    }

    /**
     * @throws Exception
     * @throws ValidationException
     *
     * @return ShopModel
     */
    public function create(array $params)
    {
        $this->checkPrivilege('create');

        $params = $this->prepareShopData($params);

        $shop = new ShopModel();
        $shop->fromArray($params);

        $violations = $this->getManager()->validate($shop);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->getManager()->persist($shop);
        $this->flush();

        return $shop;
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     *
     * @return ShopModel
     */
    public function update($id, array $params)
    {
        $this->checkPrivilege('update');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $shop = $this->getRepository()->find($id);

        if (!$shop) {
            throw new NotFoundException(sprintf('Shop by id %s not found', $id));
        }

        $params = $this->prepareShopData($params, $shop);
        $shop->fromArray($params);

        $violations = $this->getManager()->validate($shop);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $this->flush();

        return $shop;
    }

    /**
     * @param int $id
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     *
     * @return ShopModel
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $shop = $this->getRepository()->find($id);

        if (!$shop) {
            throw new NotFoundException(sprintf('Shop by id %s not found', $id));
        }

        $this->getManager()->remove($shop);
        $this->flush();

        return $shop;
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function prepareShopData(array $params, ?ShopModel $shop = null): array
    {
        $requiredParams = ['name', 'localeId', 'currencyId', 'customerGroupId', 'categoryId'];
        foreach ($requiredParams as $param) {
            if (!$shop) {
                if (!isset($params[$param]) || empty($params[$param])) {
                    throw new ParameterMissingException($param);
                }
            } else {
                if (isset($params[$param]) && empty($params[$param])) {
                    throw new RuntimeException(sprintf('param %s may not be empty', $param));
                }
            }
        }

        if (isset($params['currencyId'])) {
            $currency = Shopware()->Models()->find(Currency::class, $params['currencyId']);
            if ($currency !== null) {
                $params['currency'] = $currency;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid currency id', $params['currencyId']));
            }
        }

        if (isset($params['localeId'])) {
            $locale = Shopware()->Models()->find(Locale::class, $params['localeId']);
            if ($locale !== null) {
                $params['locale'] = $locale;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid locale id', $params['localeId']));
            }
        }

        if (isset($params['customerGroupId'])) {
            $customerGroup = Shopware()->Models()->find(Group::class, $params['customerGroupId']);
            if ($customerGroup !== null) {
                $params['customerGroup'] = $customerGroup;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid customerGroup id', $params['customerGroupId']));
            }
        }

        if (isset($params['mainId'])) {
            $shopModel = Shopware()->Models()->find(ShopModel::class, $params['mainId']);
            if ($shopModel instanceof ShopModel) {
                $params['main'] = $shopModel;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid shop id', $params['mainId']));
            }
        }

        if (isset($params['templateId'])) {
            $template = Shopware()->Models()->find(Template::class, $params['templateId']);
            if ($template !== null) {
                $params['template'] = $template;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid template id', $params['templateId']));
            }
        }

        if (isset($params['documentTemplateId'])) {
            $template = Shopware()->Models()->find(Template::class, $params['documentTemplateId']);
            if ($template !== null) {
                $params['documentTemplate'] = $template;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid template id', $params['documentTemplateId']));
            }
        }

        if (isset($params['categoryId'])) {
            $category = Shopware()->Models()->find(Category::class, $params['categoryId']);
            if ($category !== null) {
                $params['category'] = $category;
            } else {
                throw new RuntimeException(sprintf('%s is not a valid category id', $params['categoryId']));
            }
        }

        return $params;
    }
}
