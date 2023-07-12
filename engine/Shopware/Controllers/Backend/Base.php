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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Password\Encoder\PasswordEncoderInterface;
use Shopware\Components\StateTranslatorService;
use Shopware\Components\StateTranslatorServiceInterface;
use Shopware\Components\Theme\Service;
use Shopware\Components\Validator\EmailValidator;
use Shopware\Components\Validator\EmailValidatorInterface;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware\Models\Config\Element;
use Shopware\Models\Country\Area;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Group;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Document\Document;
use Shopware\Models\Emotion\Emotion;
use Shopware\Models\Order\Order;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Currency;
use Shopware\Models\Shop\Locale;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Shopware\Models\Site\Site;
use Shopware\Models\Tax\Tax;
use Shopware\Models\User\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Backend Controller for the Shopware global configured stores.
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
 *  - Locales
 *  - User
 */
class Shopware_Controllers_Backend_Base extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    private const AS_STRING = ' as ';

    /**
     * Initials the script renderer and handles the json request.
     * If the internal customer repository isn't initialed the
     * repository is initialed.
     *
     * @codeCoverageIgnore
     */
    public function init()
    {
        if (!$this->Request()->getActionName()
            || \in_array($this->Request()->getActionName(), ['index', 'load'])
        ) {
            $this->View()->addTemplateDir('.');
            $this->Front()->Plugins()->ScriptRenderer()->setRender();
            Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        } else {
            parent::init();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
        ];
    }

    /**
     * Returns all supported detail status as an array. The status are used on the detail
     * page in the position grid to edit or create an order position.
     */
    public function getDetailStatusAction()
    {
        $repository = $this->get('models')->getRepository(Order::class);
        $data = $repository->getDetailStatusQuery()->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $data]);
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
        $repository = $this->get('models')->getRepository(Tax::class);

        $query = $repository->getTaxQuery(
            $this->Request()->getParam('filter', []),
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
     * </code>getlocalesaction
     */
    public function getPaymentsAction()
    {
        // Load shop repository
        $repository = $this->get('models')->getRepository(Payment::class);
        $filter = $this->Request()->getParam('filter', []);
        $hasActiveFilter = \in_array('active', array_column($filter, 'property'), true);

        if (!$hasActiveFilter) {
            $filter[] = [
                'property' => 'active',
                'value' => true,
            ];
        }

        $query = $repository->getAllPaymentsQuery(
            $filter,
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Translate payments
        /** @var Shopware_Components_Translation $translationComponent */
        $translationComponent = $this->get(Shopware_Components_Translation::class);
        $data = $translationComponent->translatePaymentMethods($data);

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Group::class);

        $builder = $repository->createQueryBuilder('groups');
        $builder->select([
            'groups.id as id',
            'groups.key as key',
            'groups.name as name',
            'groups.tax as tax',
            'groups.taxInput as taxInput',
            'groups.mode as mode',
        ]);

        $request = $this->Request();
        $filter = $request->getParam('filter', []);
        $query = trim($request->getParam('query', ''));
        if ($query !== '') {
            $query = '%' . $query . '%';
            $filter[] = [
                'property' => 'groups.key',
                'value' => $query,
                'expression' => 'LIKE',
                'operator' => 'OR',
            ];
            $filter[] = [
                'property' => 'groups.name',
                'value' => $query,
                'expression' => 'LIKE',
                'operator' => 'OR',
            ];
        }

        $builder->addFilter($filter);
        $builder->addOrderBy($request->getParam('sort', []));

        $builder
            ->setFirstResult($request->getParam('start'))
            ->setMaxResults($request->getParam('limit'));

        $query = $builder->getQuery();

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        $repository = $this->get('models')->getRepository(Category::class);

        $query = $repository->getListQuery(
            $this->Request()->getParam('filter', []),
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('limit'),
            $this->Request()->getParam('start')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Dispatch::class);

        $query = $repository->getDispatchesQuery(
            $this->Request()->getParam('filter', []),
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Translate dispatch methods
        /** @var Shopware_Components_Translation $translationComponent */
        $translationComponent = $this->get(Shopware_Components_Translation::class);
        $data = $translationComponent->translateDispatchMethods($data);

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Supplier::class);

        $builder = $repository->createQueryBuilder('s');
        $builder->select([
            's.id as id',
            's.name as name',
            's.image as image',
            's.link as link',
        ]);

        $builder->addFilter($this->Request()->getParam('filter', []));

        // Use the query param because this is the default in ext js
        $searchQuery = $this->Request()->getParam('query', []);
        // Search for values
        if (!empty($searchQuery)) {
            $builder->andWhere('s.name LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $searchQuery . '%');
        }

        $builder->addOrderBy($this->Request()->getParam('sort', []));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Order::class);

        $query = $repository->getPaymentStatusQuery(
            $this->Request()->getParam('filter'),
            $this->Request()->getParam('sort'),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        /** @var StateTranslatorServiceInterface $stateTranslator */
        $stateTranslator = $this->get('shopware.components.state_translator');
        $data = array_map(function ($paymentStateItem) use ($stateTranslator) {
            $paymentStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);

            return $paymentStateItem;
        }, $data);

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Order::class);
        $query = $repository->getOrderStatusQuery(
            $this->Request()->getParam('filter', []),
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        /** @var StateTranslatorServiceInterface $stateTranslator */
        $stateTranslator = $this->get('shopware.components.state_translator');
        $data = array_map(function ($orderStateItem) use ($stateTranslator) {
            $orderStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $orderStateItem);

            return $orderStateItem;
        }, $data);

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Country::class);
        $query = $repository->getCountriesQuery(
            $this->Request()->getParam('filter', []),
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
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
        // Load shop repository
        $repository = $this->get('models')->getRepository(Article::class);

        $builder = $repository->createQueryBuilder('articles');

        $fields = [
            'id' => 'articles.id',
            'name' => 'articles.name',
            'description' => 'articles.description',
            'active' => 'articles.active',
            'changeTime' => 'articles.changed',
            'number' => 'detail.number',
            'detailId' => 'detail.id as detailId',
            'inStock' => 'detail.inStock',
            'supplierName' => 'supplier.name as supplierName',
            'supplierId' => 'supplier.id as supplierId',
        ];
        $builder->select($fields);

        $builder->addSelect($builder->expr()->count('details.id') . ' as countDetails');

        $builder->innerJoin('articles.mainDetail', 'detail');
        $builder->innerJoin('articles.supplier', 'supplier');

        $builder->leftJoin('articles.details', 'details');
        $builder->groupBy('articles.id');

        // Don't search for normal articles
        $displayProducts = (bool) $this->Request()->getParam('articles', true);
        if (!$displayProducts) {
            $builder->andWhere('articles.configuratorSetId IS NOT NULL');
        }

        // Don't search for variant articles?
        // This is deprecated, because it's the same as "configurator". Further it does not search for variant articles,
        // This was replaced by the option to set another store. To search for look at the example explained in the
        // search scope documentation of the Shopware.form.field.ArticleSearch.js
        $displayVariants = (bool) $this->Request()->getParam('variants', true);
        if (!$displayVariants) {
            $builder->andWhere('articles.configuratorSetId IS NULL');
        }

        // Don't search for configurator articles
        $displayConfigurators = (bool) $this->Request()->getParam('configurator', true);
        if (!$displayConfigurators) {
            $builder->andWhere('articles.configuratorSetId IS NULL');
        }

        $filters = $this->Request()->getParam('filter', []);
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
                $repository->addFilter($builder, [$filter]);
            }
        }

        $repository->addOrderBy($builder, $this->prepareParam($this->Request()->getParam('sort', []), $fields));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $paginator = $this->get('models')->createPaginator($query);

        // Get total result of the query
        $total = $paginator->count();

        // Select all shop as array
        $data = iterator_to_array($paginator);

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * Returns a list of articles with variants. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * This function is at the first look very similar to "getArticleAction()" but it differs in the query builder and the result very strong
     *
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
    public function getVariantsAction()
    {
        $builder = Shopware()->Container()->get(Connection::class)->createQueryBuilder();

        $fields = [
            'details.id',
            'articles.name',
            'articles.description',
            'articles.active',
            'details.ordernumber',
            'articles.id as articleId',
            'details.inStock',
            'supplier.name as supplierName',
            'supplier.id as supplierId',
            'details.additionalText',
            'prices.price',
            'taxes.tax',
        ];

        $builder->select($fields);
        $builder->from('s_articles_details', 'details');
        $builder->innerJoin('details', 's_articles', 'articles', 'details.articleID = articles.id');
        $builder->innerJoin('articles', 's_core_tax', 'taxes', 'articles.taxID = taxes.id');
        $builder->innerJoin('articles', 's_articles_supplier', 'supplier', 'supplier.id = articles.supplierID');
        $builder->innerJoin('details', 's_articles_prices', 'prices', 'details.id = prices.articledetailsID');
        $builder->where('prices.pricegroup = "EK"');

        $filters = $this->Request()->getParam('filter', []);
        foreach ($filters as $filter) {
            if ($filter['property'] === 'free') {
                $builder->andWhere(
                    $builder->expr()->orX(
                        'details.ordernumber LIKE :free',
                        'articles.name LIKE :free',
                        'supplier.name LIKE :free'
                    )
                );
                $builder->setParameter(':free', $filter['value']);
            } else {
                $builder->addFilter($filter);
            }
        }

        $properties = $this->prepareVariantParam($this->Request()->getParam('sort', []), $fields);
        foreach ($properties as $property) {
            $builder->addOrderBy($property['property'], $property['direction']);
        }

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'))
            ->orderBy('details.id', 'ASC');

        $result = $builder->execute()->fetchAll(PDO::FETCH_ASSOC);

        $total = (int) $builder->getConnection()->fetchColumn('SELECT FOUND_ROWS()');

        $result = $this->addAdditionalTextForVariant($result);

        foreach ($result as $index => $variant) {
            $result[$index]['price'] = round($variant['price'] / 100 * (100 + $variant['tax']), 2);
        }

        $this->View()->assign(['success' => true, 'data' => $result, 'total' => $total]);
    }

    /**
     * Returns a list of all backend-users. Supports store paging, sorting and filtering over the standard ExtJs store parameters.
     * Each user has the following fields:
     * <code>
     *    [int]      id
     *    [int]      roleId
     *    [string]   username
     *    [string]   password
     *    [int]      localeId
     *    [string]   sessionId
     *    [date]     lastLogin
     *    [string]   name
     *    [string]   email
     *    [int]      active
     *    [int]      failedLogins
     *    [date]     lockedUntil
     * </code>
     */
    public function getUsersAction()
    {
        // Load user repository
        $repository = $this->get('models')->getRepository(User::class);
        $builder = $repository->createQueryBuilder('user');
        $fields = [
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
            'failedLogins' => 'user.failedLogins',
            'lockedUntil' => 'user.lockedUntil',
        ];
        $builder->select($fields);

        $builder->addFilter($this->Request()->getParam('filter', []));
        $builder->addOrderBy($this->Request()->getParam('sort', []));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getShopsAction()
    {
        $repository = $this->get('models')->getRepository(Shop::class);

        $request = $this->Request();
        $filter = $request->getParam('filter', []);
        $query = trim($request->getParam('query', ''));
        if ($query !== '') {
            $query = '%' . $query . '%';
            $filter[] = [
                'property' => 'shop.name',
                'expression' => 'LIKE',
                'value' => $query,
            ];
        }

        $query = $repository->getBaseListQuery(
            $filter,
            $order = $request->getParam('sort', []),
            $offset = $request->getParam('start'),
            $limit = $request->getParam('limit'),
            true
        );
        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * Returns a list of shops that have themes assigned
     * Used for theme cache warm up in the backend
     */
    public function getShopsWithThemesAction()
    {
        $repository = $this->get('models')->getRepository(Shop::class);

        $shopId = $this->Request()->getParam('shopId');
        $filter = $this->Request()->getParam('filter', []);
        if ($shopId) {
            $filter[] = [
                'property' => 'shop.id',
                'value' => $shopId,
                'operator' => null,
                'expression' => null,
            ];
        }

        $query = $repository->getShopsWithThemes(
            $filter,
            $this->Request()->getParam('sort', []),
            $this->Request()->getParam('start'),
            $this->Request()->getParam('limit')
        );

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getTemplatesAction()
    {
        $repository = $this->get('models')->getRepository(Template::class);
        /** @var Template[] $templates */
        $templates = $repository->findAll();

        $result = [];
        foreach ($templates as $template) {
            $data = [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'template' => $template->getTemplate(),
            ];

            $data = $this->get(Service::class)->translateTheme(
                $template,
                $data
            );

            $result[] = $data;
        }

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    public function getCurrenciesAction()
    {
        $repository = $this->get('models')->getRepository(Currency::class);

        /** @var QueryBuilder $builder */
        $builder = $repository->createQueryBuilder('c');
        $builder->select([
            'c.id as id',
            'c.name as name',
            'c.currency as currency',
        ]);

        $builder->addFilter((array) $this->Request()->getParam('filter', []));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', []));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        // Get total result of the query
        $total = $this->get('models')->getQueryCount($query);

        // Select all shop as array
        $data = $query->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getLocalesAction()
    {
        $repository = $this->get(ModelManager::class)->getRepository(Locale::class);

        $builder = $repository->createQueryBuilder('l');
        $builder->select([
            'l.id as id',
            'l.locale as locale',
            'l.language as language',
            'l.territory as territory',
        ]);

        $builder->addFilter((array) $this->Request()->getParam('filter', []));

        $sort = $this->Request()->getParam('sort', []);
        if (\is_array($sort) && \count($sort) === 0) {
            $builder->addOrderBy('l.language');
            $builder->addOrderBy('l.territory');
        }
        $builder->addOrderBy($sort);

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $total = $this->get(ModelManager::class)->getQueryCount($query);
        $data = $query->getArrayResult();

        $data = $this->getSnippetsForLocales($data);

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getCountryAreasAction()
    {
        $repository = $this->get('models')->getRepository(Area::class);

        /** @var QueryBuilder $builder */
        $builder = $repository->createQueryBuilder('area');
        $builder->select([
            'area.id as id',
            'area.name as name',
        ]);
        $builder->addFilter((array) $this->Request()->getParam('filter', []));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', []));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $total = $this->get('models')->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getCountryStatesAction()
    {
        $countryId = $this->Request()->getParam('countryId');

        $repository = $this->get('models')->getRepository(State::class);

        /** @var QueryBuilder $builder */
        $builder = $repository->createQueryBuilder('state');
        $builder->select([
            'state.id as id',
            'state.name as name',
        ]);
        if ($countryId !== null) {
            $builder->where('state.countryId = :cId');
            $builder->setParameter(':cId', $countryId);
        }
        $builder->addFilter((array) $this->Request()->getParam('filter', []));
        $builder->addOrderBy((array) $this->Request()->getParam('sort', []));

        $builder->setFirstResult($this->Request()->getParam('start'))
            ->setMaxResults($this->Request()->getParam('limit'));

        $query = $builder->getQuery();

        $total = $this->get('models')->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getAvailableHashesAction()
    {
        /** @var PasswordEncoderInterface[] $hashes */
        $hashes = Shopware()->PasswordEncoder()->getCompatibleEncoders();

        $result = [];

        $result[] = ['id' => 'Auto'];

        $blacklist = ['prehashed', 'legacybackendmd5'];

        foreach ($hashes as $hash) {
            if (\in_array(strtolower($hash->getName()), $blacklist)) {
                continue;
            }

            $result[] = [
                'id' => $hash->getName(),
            ];
        }

        $totalResult = \count($hashes);

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ]);
    }

    public function getAvailableCaptchasAction()
    {
        $captchaRepository = $this->get('shopware.captcha.repository');
        $namespace = $namespace = Shopware()->Snippets()->getNamespace('backend/captcha/display_names');
        $availableCaptchas = $captchaRepository->getList();
        $result = [];

        foreach ($availableCaptchas as $captcha) {
            $result[] = [
                'id' => $captcha->getName(),
                'displayname' => $namespace->get($captcha->getName(), ucfirst(strtolower($captcha->getName()))),
            ];
        }

        $totalResult = \count($availableCaptchas);

        $this->View()->assign([
            'success' => true,
            'data' => $result,
            'total' => $totalResult,
        ]);
    }

    /**
     * Loads options for the 404 page config options.
     * Returns an array of all defined emotion pages, plus the 2 default options.
     */
    public function getPageNotFoundDestinationOptionsAction()
    {
        $limit = $this->Request()->getParam('limit');
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort');

        $namespace = Shopware()->Snippets()->getNamespace('backend/base/page_not_found_destination_options');

        $query = $this->get('models')->getRepository(Emotion::class)
            ->getNameListQuery(true, $sort, $offset, $limit);
        $count = $this->get('models')->getQueryCount($query);
        $emotions = $query->getArrayResult();
        foreach ($emotions as &$emotion) {
            $emotion['name'] = $namespace->get('emotion_page_prefix', 'Shopping world') . ': ' . $emotion['name'];
        }

        $options = array_merge(
            [
                [
                    'id' => '-2',
                    'name' => $namespace->get('show_homepage', 'Show homepage'),
                ],
                [
                    'id' => '-1',
                    'name' => $namespace->get('show_error_page', 'Show default error page'),
                ],
            ],
            $emotions
        );

        $this->View()->assign([
            'success' => true,
            'data' => $options,
            'total' => $count + 2,
        ]);
    }

    /**
     * Validates the email address in parameter "value"
     * Sets the response body to "1" if valid, to an empty string otherwise
     */
    public function validateEmailAction()
    {
        // Disable template renderer and automatic json renderer
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $emails = array_map('trim', explode(',', (string) $this->Request()->getParam('value', '')));

        /** @var EmailValidatorInterface $emailValidator */
        $emailValidator = $this->container->get(EmailValidator::class);

        foreach ($emails as $email) {
            if (!$emailValidator->isValid($email)) {
                $this->Response()->setContent('');

                return;
            }
        }

        $this->Response()->setContent(1);
    }

    public function getSalutationsAction()
    {
        $value = $this->getAvailableSalutationKeys();

        $whitelist = $this->Request()->getParam('ids', []);

        if (!empty($whitelist)) {
            $whitelist = json_decode($whitelist, true);
            $value = array_filter($value, function ($key) use ($whitelist) {
                return \in_array($key, $whitelist, true);
            });
        }

        $namespace = Shopware()->Container()->get('snippets')->getNamespace('frontend/salutation');
        $salutations = [];
        foreach ($value as $key) {
            $label = $namespace->get($key, $key);
            if (empty(trim($label))) {
                $label = $key;
            }
            $salutations[] = ['key' => $key, 'label' => $label];
        }

        $this->View()->assign('data', $salutations);
    }

    /**
     * Returns a list of document types. Supports store paging, sorting and filtering over the standard ExtJs store
     * parameters. Each document type has the following fields:
     * <code>
     *    [int]      id
     *    [string]   name
     *    [string]   template
     *    [string]   numbers
     *    [int]      left
     *    [int]      right
     *    [int]      top
     *    [int]      bottom
     *    [int]      pageBreak
     * </code>
     */
    public function getDocTypesAction()
    {
        $modelManager = $this->container->get(ModelManager::class);
        $repository = $modelManager
            ->getRepository(Document::class);

        $builder = $repository->createQueryBuilder('d');

        $builder->select('d')
            ->addFilter((array) $this->Request()->getParam('filter', []))
            ->addOrderBy((array) $this->Request()->getParam('sort', []))
            ->setFirstResult($this->Request()->getParam('start', 0))
            ->setMaxResults($this->Request()->getParam('limit', 250));

        $query = $builder->getQuery();
        $total = $modelManager->getQueryCount($query);
        $data = $query->getArrayResult();

        // translate the document names
        /** @var Shopware_Components_Translation $translationComponent */
        $translationComponent = $this->get(Shopware_Components_Translation::class);
        $data = $translationComponent->translateDocuments($data);

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getLandingPagesAction(Request $request): void
    {
        $modelManager = $this->container->get(ModelManager::class);
        $emotionRepository = $modelManager->getRepository(Emotion::class);

        $builder = $emotionRepository->createQueryBuilder('emotion');

        $builder->select('emotion')
            ->where('emotion.isLandingPage = 1')
            ->addFilter((array) $request->get('filter', []))
            ->addOrderBy((array) $request->get('sort', []))
            ->setFirstResult((int) $request->get('start', 0))
            ->setMaxResults((int) $request->get('limit', 250));

        $query = $builder->getQuery();
        $total = $modelManager->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getBlogsAction(Request $request): void
    {
        $modelManager = $this->container->get(ModelManager::class);
        $emotionRepository = $modelManager->getRepository(Blog::class);

        $builder = $emotionRepository->createQueryBuilder('blog');

        $builder->select('blog')
            ->addFilter((array) $request->get('filter', []))
            ->addOrderBy((array) $request->get('sort', []))
            ->setFirstResult((int) $request->get('start', 0))
            ->setMaxResults((int) $request->get('limit', 250));

        $query = $builder->getQuery();
        $total = $modelManager->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    public function getStaticsAction(Request $request): void
    {
        $modelManager = $this->container->get(ModelManager::class);
        $emotionRepository = $modelManager->getRepository(Site::class);

        $builder = $emotionRepository->createQueryBuilder('site');

        $builder->select('site')
            ->addFilter((array) $request->get('filter', []))
            ->addOrderBy((array) $request->get('sort', []))
            ->setFirstResult((int) $request->get('start', 0))
            ->setMaxResults((int) $request->get('limit', 250));

        $query = $builder->getQuery();
        $total = $modelManager->getQueryCount($query);
        $data = $query->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * Adds the additional text for variants
     *
     * @param array $data
     */
    protected function addAdditionalTextForVariant($data)
    {
        $variantIds = [];
        $tmpVariant = [];

        // Checks if an additional text is available
        foreach ($data as $key => $variantData) {
            if (!empty($variantData['additionalText'])) {
                $data[$key]['name'] = $variantData['name'] . ' ' . $variantData['additionalText'];
            } else {
                $variantIds[$variantData['id']] = $variantData['id'];
            }

            $tmpVariant[$variantData['id']] = $variantData;
        }

        if (empty($variantIds)) {
            return $data;
        }

        $builder = Shopware()->Container()->get(Connection::class)->createQueryBuilder();

        $builder->select([
            'details.id',
            'config_groups.name AS groupName',
            'config_options.name AS optionName',
        ]);

        $builder->from('s_articles_details', 'details');

        $builder->innerJoin('details', 's_article_configurator_option_relations', 'mapping', 'mapping.article_id = details.id');
        $builder->innerJoin('mapping', 's_article_configurator_options', 'config_options', 'config_options.id = mapping.option_id');
        $builder->innerJoin('config_options', 's_article_configurator_groups', 'config_groups', 'config_options.group_id = config_groups.id');

        $builder->where('details.id IN (:detailsId)');
        $builder->setParameter('detailsId', $variantIds, Connection::PARAM_INT_ARRAY);

        $result = $builder->execute()->fetchAllAssociative();

        $tmpVariant = $this->buildDynamicText($tmpVariant, $result);

        // Maps the associative data array back to a normal indexed array
        return array_values($tmpVariant);
    }

    /**
     * Prepares the sort params for the variant search
     */
    private function prepareVariantParam(array $properties, array $fields): array
    {
        // Maps the fields to the correct table
        foreach ($properties as $key => $property) {
            foreach ($fields as $field) {
                $asStr = self::AS_STRING;
                $dotPos = strpos($field, '.');
                $asPos = strpos($field, $asStr, 1);

                if ($asPos) {
                    $fieldName = substr($field, $asPos + \strlen($asStr));
                } else {
                    $fieldName = substr($field, $dotPos + 1);
                }

                if ($fieldName == $property['property']) {
                    $properties[$key]['property'] = $field;
                }
            }
        }

        return $properties;
    }

    /**
     * Add the table alias to the passed filter and sort parameters.
     *
     * @return array
     */
    private function prepareParam(array $properties, array $fields)
    {
        if (empty($properties)) {
            return $properties;
        }

        foreach ($properties as $key => $property) {
            if (\array_key_exists($property['property'], $fields)) {
                $property['property'] = $fields[$property['property']];
            }
            $properties[$key] = $property;
        }

        return $properties;
    }

    /**
     * Helper function to generate the additional text dynamically
     */
    private function buildDynamicText(array $data, array $variantsWithoutAdditionalText): array
    {
        foreach ($variantsWithoutAdditionalText as $variantWithoutAdditionalText) {
            $variantData = &$data[$variantWithoutAdditionalText['id']];

            if (empty($variantData['additionalText'])) {
                $variantData['additionalText'] .= $variantWithoutAdditionalText['optionName'];
                $variantData['name'] .= ' ' . $variantWithoutAdditionalText['optionName'];
            } else {
                $variantData['additionalText'] .= ' / ' . $variantWithoutAdditionalText['optionName'];
                $variantData['name'] .= ' / ' . $variantWithoutAdditionalText['optionName'];
            }
        }

        return $data;
    }

    private function getAvailableSalutationKeys(): array
    {
        $builder = Shopware()->Container()->get(ModelManager::class)->createQueryBuilder();
        $builder->select(['element', 'values'])
            ->from(Element::class, 'element')
            ->leftJoin('element.values', 'values')
            ->where('element.name = :name')
            ->setParameter('name', 'shopsalutations');

        $data = $builder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $value = explode(',', $data['value']);
        if (!empty($data['values'])) {
            $value = [];
        }

        foreach ($data['values'] as $shopValue) {
            $value = array_merge($value, explode(',', $shopValue['value']));
        }

        return array_unique(array_filter($value));
    }

    /**
     * Replaces the locales with the snippets data
     *
     * @return array $data
     */
    private function getSnippetsForLocales(array $data): array
    {
        $snippets = $this->container->get('snippets');
        foreach ($data as &$locale) {
            if (!empty($locale['language'])) {
                $locale['language'] = $snippets->getNamespace('backend/locale/language')->get(
                    $locale['locale'],
                    $locale['language'],
                    true
                );
            }
            if (!empty($locale['territory'])) {
                $locale['territory'] = $snippets->getNamespace('backend/locale/territory')->get(
                    $locale['locale'],
                    $locale['territory'],
                    true
                );
            }
        }

        return $data;
    }
}
