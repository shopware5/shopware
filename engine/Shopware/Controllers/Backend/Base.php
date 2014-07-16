<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Backend Controller for the Shopware 4 global configured stores.
 *
 * The following stores are configured global:
 *  - Article
 *  - Categories
 *  - Country
 *  - Customer groups
 *  - Dispatches
 *  - Payments
 *  - Suppliers
 *  - Shops
 *  - Locales (By sth)
 *  - User (By ps)
 *
 */
class Shopware_Controllers_Backend_Base extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Initials the script renderer and handles the json request.
     * If the internal customer repository isn't initialed the
     * repository is initialed.
     *
     * @codeCoverageIgnore
     * @return void
     */
    public function init()
    {
        if (!$this->Request()->getActionName()
            || in_array($this->Request()->getActionName(), array('index', 'load'))
        ) {

            $this->View()->addTemplateDir('.');
            $this->Front()->Plugins()->ScriptRenderer()->setRender();
            Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        } else {
            parent::init();
        }
    }

   /**
    * Add the table alias to the passed filter and sort parameters.
    * @param $properties
    * @param $fields
    * @return array|mixed
    */
    private function prepareParam($properties, $fields)
    {
        foreach ($properties as $key => $property) {
        if (array_key_exists($property['property'], $fields)) {
            $property['property'] = $fields[$property['property']];
        }
            $properties[$key] = $property;
        }
        return $properties;
    }

    /**
     * Returns all supported detail status as an array. The status are used on the detail
     * page in the position grid to edit or create an order position.
     */
    public function getDetailStatusAction()
    {
        /**@var $repository \Shopware\Models\Order\Repository*/
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $data = $repository->getDetailStatusQuery()->getArrayResult();
        $this->View()->assign(array('success' => true, 'data' => $data));
    }


    /**
     * Returns a list of taxes. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each shop has the following fields:
     * <code>
     *    [int]      id
     *    [double]   tax
     *    [string]   description
     * </code>
     */
    public function getTaxesAction()
    {
        /** @var $repository Shopware\Components\Model\ModelRepository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Tax\Tax');

        $query = $repository->queryBy(
            $this->Request()->getParam('filter', array()),
            $this->Request()->getParam('sort', array()),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('start')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of payments. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each payment has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     *    [string]   description
     *    [int]      position
     *    [int]      active
     * </code>
     */
    public function getPaymentsAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment');

        $query = $repository->getPaymentsQuery(
            $filter = $this->Request()->getParam('filter', array()),
            $order = $this->Request()->getParam('sort', array()),
            $offset = $this->Request()->getParam('start'),
            $limit = $this->Request()->getParam('limit')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));

    }

    /**
     * Returns a list of customer groups. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each customer group has the following fields:
     * <code>
     *    [int]     id
     *    [string]  key
     *    [string]  description
     *    [int]     tax
     *    [int]     taxInput
     *    [int]     mode
     * </code>
     */
    public function getCustomerGroupsAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Group');

        $builder = $repository->createQueryBuilder('groups');
        $builder->select(array(
            'groups.id as id',
            'groups.key as key',
            'groups.name as name',
            'groups.tax as tax',
            'groups.taxInput as taxInput',
            'groups.mode as mode'
        ));
        $builder->addFilter($this->Request()->getParam('filter', array()));
        $builder->addOrderBy($this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }


    /**
     * Returns a list of categories. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each category has the following fields:
     * <code>
     *    [int]     id
     *    [int]     parent
     *    [string]  name
     *    [int]     position
     *    [int]     active
     * </code>
     */
    public function getCategoriesAction()
    {
        /** @var $repository \Shopware\Models\Category\Repository */
        $repository = Shopware()->Models()->Category();

        $query = $repository->getListQuery(
            $this->Request()->getParam('filter', array()),
            $this->Request()->getParam('sort', array()),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('start')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }


    /**
     * Returns a list of dispatches. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each dispatch has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     *    [int]      type
     *    [string]   comment
     *    [int]      active
     *    [int]      position
     * </code>
     */
    public function getDispatchesAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Dispatch\Dispatch');

        $filters = $this->Request()->getParam('filter', array());
        $filters[] = array('property' => 'dispatches.active', 'value' => 1);
        $query = $repository->getDispatchesQuery(
            $filters,
            $this->Request()->getParam('sort', array()),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of suppliers. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each supplier has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     *    [string]   img
     *    [string]   link
     *    [string]   description
     * </code>
     */
    public function getSuppliersAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Supplier');

        $builder = $repository->createQueryBuilder('s');
        $builder->select(array(
            's.id as id',
            's.name as name',
            's.image as image',
            's.link as link'
        ));

        $builder->addFilter($this->Request()->getParam('filter', array()));

        //use the query param because this is the default in ext js
        $searchQuery = $this->Request()->getParam('query', array());
        //search for values
        if (!empty($searchQuery)) {
            $builder->andWhere('s.name LIKE :searchQuery')
                    ->setParameter('searchQuery', '%' . $searchQuery . '%');
        }

        $builder->addOrderBy($this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of payment states. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each payment state has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     * </code>
     */
    public function getPaymentStatusAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');

        $query = $repository->getPaymentStatusQuery(
            $filter = $this->Request()->getParam('filter', array()),
            $order = $this->Request()->getParam('sort', array()),
            $offset = $this->Request()->getParam('start'),
            $limit = $this->Request()->getParam('limit')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of order states. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each order state has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     * </code>
     */
    public function getOrderStatusAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        $query = $repository->getOrderStatusQuery(
            $filter = $this->Request()->getParam('filter', array()),
            $order = $this->Request()->getParam('sort', array()),
            $offset = $this->Request()->getParam('start'),
            $limit = $this->Request()->getParam('limit')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of countries. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each country has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     *    [string]   iso
     *    [int]      position
     *    [int]      active
     * </code>
     */
    public function getCountriesAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Country\Country');
        $query = $repository->getCountriesQuery(
            $filter = $this->Request()->getParam('filter', array()),
            $order = $this->Request()->getParam('sort', array()),
            $offset = $this->Request()->getParam('start'),
            $limit = $this->Request()->getParam('limit')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of articles. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each article has the following fields:
     * <code>
     *   [int]      id
     *   [string]   name
     *   [string]   number
     *   [int]      supplierId
     *   [string]   supplierName
     *   [string]   description
     *   [int]      active
     *   [array]    changeTime
     *   [int]      detailId
     *   [int]      inStock
     * </code>
     */
    public function getArticlesAction()
    {
        //load shop repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');

        $builder = $repository->createQueryBuilder('articles');

        $fields = array(
            'id' => 'articles.id',
            'name' => 'articles.name',
            'description' => 'articles.description',
            'active' => 'articles.active',
            'changeTime' => 'articles.changed',
            'number' => 'detail.number',
            'detailId' => 'detail.id as detailId',
            'inStock' => 'detail.inStock',
            'supplierName' => 'supplier.name as supplierName',
            'supplierId' => 'supplier.id as supplierId'
        );
        $builder->select($fields);

        $builder->addSelect($builder->expr()->count('details.id') . ' as countDetails');

        $builder->innerJoin('articles.mainDetail', 'detail');
        $builder->innerJoin('articles.supplier', 'supplier');

        $builder->leftJoin('articles.details', 'details');
        $builder->groupBy('articles.id');

        //don't search for normal articles
        $displayArticles = (bool) $this->Request()->getParam('articles', true);
        if (!$displayArticles) {
            $builder->andWhere('articles.configuratorSetId IS NOT NULL');
        }

        //don't search for variant articles?
        $displayVariants = (bool) $this->Request()->getParam('variants', true);
        if (!$displayVariants) {
            $builder->andWhere('articles.configuratorSetId IS NULL');
        }

        //don't search for configurator articles
        $displayConfigurators = (bool) $this->Request()->getParam('configurator', true);
        if (!$displayConfigurators) {
            $builder->andWhere('articles.configuratorSetId IS NULL');
        }

        $filters = $this->Request()->getParam('filter', array());
        foreach ($filters as $filter) {
            if ($filter['property'] === 'free') {
                $builder->andWhere(
                    $builder->expr()->orX(
                        'detail.number LIKE :free',
                        'articles.name LIKE :free'
                    )
                );
                $builder->setParameter(':free', $filter['value']);
            } else {
                $repository->addFilter($builder, $filter);
            }
        }

        $repository->addOrderBy($builder, $this->prepareParam($this->Request()->getParam('sort', array()), $fields));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    /**
     * Returns a list of all backend-users. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each user has the following fields:
     * <code>
     *    [int]      id
     *    [int]        roleId
     *    [string]   username
     *    [string]   password
     *    [int]      localeId
     *       [string]   sessionId
     *    [date]     lastLogin
     *    [string]   name
     *    [string]   email
     *    [int]      active
     *    [int]      admin
     *    [int]      salted
     *    [int]      failedLogins
     *    [date]     lockedUntil
     * </code>
     */
    public function getUsersAction()
    {
        //load user repository
        $repository = Shopware()->Models()->getRepository('Shopware\Models\User\User');
        $builder = $repository->createQueryBuilder('user');
        $fields = array(
            'id' => 'user.id',
            'roleId' => 'user.roleId',
            'username' => 'user.username',
            'password' => 'user.password',
            'localeId' => 'user.localeId',
            'sessionId' => 'user.sessionId',
            'lastLogin' => 'user.lastLogin',
            'name' => 'user.name',
            'email' => 'user.email',
            'active' => 'user.active',
            'admin' => 'user.admin',
            'salted' => 'user.salted',
            'failedLogins' => 'user.failedLogins',
            'lockedUntil' => 'user.lockedUntil'
        );
        $builder->select($fields);

        $builder->addFilter($this->Request()->getParam('filter', array()));
        $builder->addOrderBy($this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getShopsAction()
    {
        //load shop repository
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $query = $repository->getBaseListQuery(
            $filter = $this->Request()->getParam('filter', array()),
            $order = $this->Request()->getParam('sort', array()),
            $offset = $this->Request()->getParam('start'),
            $limit = $this->Request()->getParam('limit')
        );

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getTemplatesAction()
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Template');
        $builder = $repository->createQueryBuilder('t');
        $builder->select(array(
            't.id as id',
            't.name as name',
            't.template as template'
        ));
        $builder->addFilter((array) $this->Request()->getParam('filter', array()));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
                ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getCurrenciesAction()
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Currency');

        $builder = $repository->createQueryBuilder('c');
        $builder->select(array(
            'c.id as id',
            'c.name as name',
            'c.currency as currency'
        ));

        $builder->addFilter((array) $this->Request()->getParam('filter', array()));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
                ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        //get total result of the query
        $total = Shopware()->Models()->getQueryCount($query);

        //select all shop as array
        $data = $query->getArrayResult();

        //return the data and total count
        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getLocalesAction()
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Locale');

        $builder = $repository->createQueryBuilder('l');
        $builder->select(array(
            'l.id as id',
            'l.locale as locale',
            'l.language as language',
            'l.territory as territory'
        ));
        $builder->addFilter((array) $this->Request()->getParam('filter', array()));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $total = Shopware()->Models()->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getCountryAreasAction()
    {
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Country\Area');

        $builder = $repository->createQueryBuilder('area');
        $builder->select(array(
            'area.id as id',
            'area.name as name'
        ));
        $builder->addFilter((array) $this->Request()->getParam('filter', array()));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
                ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $total = Shopware()->Models()->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getCountryStatesAction()
    {
        $countryId = $this->Request()->getParam('countryId', null);

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Country\State');

        $builder = $repository->createQueryBuilder('state');
        $builder->select(array(
            'state.id as id',
            'state.name as name'
        ));
        if ($countryId !== null) {
            $builder ->where('state.countryId = :cId');
            $builder->setParameter(':cId', $countryId);
        }
        $builder->addFilter((array) $this->Request()->getParam('filter', array()));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', array()));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $total = Shopware()->Models()->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $total));
    }

    public function getAvailableHashesAction()
    {
        $hashes = Shopware()->PasswordEncoder()->getCompatibleEncoders();

        $result = array();

        $result[] = array('id' => 'Auto');

        $blacklist = array('prehashed', 'legacybackendmd5');

        foreach ($hashes as $hash) {

            if (in_array(strtolower($hash->getName()), $blacklist)) {
                continue;
            }

            $result[] = array(
                'id' => $hash->getName()
            );
        }

        $totalResult = count($hashes);

        $this->View()->assign(array(
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ));
    }
}
