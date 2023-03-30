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
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\ProductSearchInterface;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\Sorting\PopularitySorting;
use Shopware\Bundle\SearchBundle\Sorting\ReleaseDateSorting;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\AdditionalTextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService;
use Shopware\Bundle\StoreFrontBundle\Service\ListingLinkRewriteServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductNumberServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PropertyServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\VariantListingPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Product;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Compatibility\LegacyEventManager;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\ProductStream\CriteriaFactoryInterface;
use Shopware\Components\ProductStream\Repository;
use Shopware\Components\QueryAliasMapper;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Repository as ArticleRepository;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository as MediaRepository;

/**
 * @phpstan-type ListingArray array{sArticles: array<string, array<string, mixed>>, criteria: Criteria, facets: array<FacetResultInterface>, sPage: int, pageIndex: int, pageSizes: array<int>, sPerPage: int|null, sNumberArticles: int, shortParameters: array<string, string>, sTemplate: string|null, sSort: int}
 * Shopware Class that handle products
 */
class sArticles implements Enlight_Hook
{
    /**
     * Pointer to sSystem object
     *
     * @var sSystem
     */
    public $sSYSTEM;

    /**
     * @var Category
     */
    public $category;

    /**
     * @var int
     */
    public $categoryId;

    /**
     * @var int
     */
    public $translationId;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * @var ArticleRepository
     */
    protected $articleRepository = null;

    /**
     * @var MediaRepository
     */
    protected $mediaRepository = null;

    private ContextServiceInterface $contextService;

    private Shopware_Components_Config $config;

    private ListProductServiceInterface $listProductService;

    private ProductServiceInterface $productService;

    private ConfiguratorServiceInterface $configuratorService;

    private PropertyServiceInterface $propertyService;

    private AdditionalTextServiceInterface $additionalTextService;

    private ProductSearchInterface $searchService;

    private Enlight_Event_EventManager $eventManager;

    private Enlight_Components_Db_Adapter_Pdo_Mysql $db;

    private Connection $connection;

    private LegacyStructConverter $legacyStructConverter;

    private LegacyEventManager $legacyEventManager;

    private QueryAliasMapper $queryAliasMapper;

    private Enlight_Controller_Front $frontController;

    private ProductNumberSearchInterface $productNumberSearch;

    private Enlight_Components_Session_Namespace $session;

    private StoreFrontCriteriaFactoryInterface $storeFrontCriteriaFactory;

    /**
     * @var array<int>
     */
    private array $cachePromotions = [];

    private sArticlesComparisons $productComparisons;

    private ProductNumberServiceInterface $productNumberService;

    private ListingLinkRewriteServiceInterface $listingLinkRewriteService;

    public function __construct(
        Category $category = null,
        $translationId = null,
        $customerGroupId = null
    ) {
        $container = Shopware()->Container();

        $this->category = $category ?: Shopware()->Shop()->getCategory();
        $this->categoryId = $this->category->getId();
        $this->translationId = $translationId ?: (!Shopware()->Shop()->getDefault() ? Shopware()->Shop()->getId() : null);
        $this->customerGroupId = $customerGroupId ?: ((int) Shopware()->Modules()->System()->sUSERGROUPDATA['id']);

        $this->config = $container->get(Shopware_Components_Config::class);
        $this->db = $container->get('db');
        $this->connection = $container->get(Connection::class);
        $this->eventManager = $container->get('events');
        $this->contextService = $container->get(ContextServiceInterface::class);
        $this->listProductService = $container->get(ListProductServiceInterface::class);
        $this->productService = $container->get(ProductServiceInterface::class);
        $this->productNumberSearch = $container->get(ProductNumberSearchInterface::class);
        $this->configuratorService = $container->get(ConfiguratorServiceInterface::class);
        $this->propertyService = $container->get(PropertyServiceInterface::class);
        $this->additionalTextService = $container->get(AdditionalTextServiceInterface::class);
        $this->searchService = $container->get(ProductSearchInterface::class);
        $this->queryAliasMapper = $container->get(QueryAliasMapper::class);
        $this->frontController = $container->get('front');
        $this->legacyStructConverter = $container->get(LegacyStructConverter::class);
        $this->legacyEventManager = $container->get(LegacyEventManager::class);
        $this->session = $container->get('session');
        $this->storeFrontCriteriaFactory = $container->get(StoreFrontCriteriaFactoryInterface::class);
        $this->productNumberService = $container->get(ProductNumberServiceInterface::class);
        $this->listingLinkRewriteService = $container->get(ListingLinkRewriteServiceInterface::class);

        $this->productComparisons = new sArticlesComparisons($this, $container);
    }

    /**
     * Delete products from comparision chart
     *
     * @param int $article Unique product id - refers to s_articles.id
     */
    public function sDeleteComparison($article)
    {
        $this->productComparisons->sDeleteComparison($article);
    }

    /**
     * Delete all products from comparision chart
     */
    public function sDeleteComparisons()
    {
        $this->productComparisons->sDeleteComparisons();
    }

    /**
     * Insert products in comparision chart
     *
     * @param int $article s_articles.id
     *
     * @return bool true/false
     */
    public function sAddComparison($article)
    {
        return $this->productComparisons->sAddComparison($article);
    }

    /**
     * Get all products from comparision chart
     *
     * @return array Associative array with all articles or empty array
     */
    public function sGetComparisons()
    {
        return $this->productComparisons->sGetComparisons();
    }

    /**
     * Get all products and a table of their properties as an array
     *
     * @return array Associative array with all products or empty array
     */
    public function sGetComparisonList()
    {
        return $this->productComparisons->sGetComparisonList();
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7. Use the sArticlesComparisons::sGetComparisonProperties instead.
     *
     * Returns all filterable properties depending on the given products
     *
     * @param array $articles
     *
     * @return array
     */
    public function sGetComparisonProperties($articles)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Use the sArticlesComparisons::sGetComparisonProperties instead.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->productComparisons->sGetComparisonProperties($articles);
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7. Use sArticlesComparisons::sFillUpComparisonArticles instead
     *
     * fills the product properties with the values and fills up empty values
     *
     * @param array $properties
     * @param array $articles
     *
     * @return array
     */
    public function sFillUpComparisonArticles($properties, $articles)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Use sArticlesComparisons::sFillUpComparisonArticles instead.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->productComparisons->sFillUpComparisonArticles($properties, $articles);
    }

    /**
     * Get all properties from one product
     *
     * @param int $articleId - s_articles.id
     *
     * @return array
     */
    public function sGetArticleProperties($articleId)
    {
        $orderNumber = $this->getOrderNumberByProductId((int) $articleId);
        if (!$orderNumber) {
            return [];
        }

        $productContext = $this->contextService->getShopContext();
        $product = $this->listProductService->get($orderNumber, $productContext);
        if (!$product || !$product->hasProperties()) {
            return [];
        }

        $set = $this->propertyService->get($product, $productContext);
        if (!$set) {
            return [];
        }

        return $this->legacyStructConverter->convertPropertySetStruct($set);
    }

    /**
     * Save a new product comment / voting
     * Reads several values directly from _POST
     *
     * @param int $article - s_articles.id
     *
     * @throws Enlight_Exception
     */
    public function sSaveComment($article)
    {
        $request = $this->frontController->Request();

        $sVoteName = strip_tags($request->getPost('sVoteName'));
        $sVoteSummary = strip_tags($request->getPost('sVoteSummary'));
        $sVoteComment = strip_tags($request->getPost('sVoteComment'));
        $sVoteStars = (float) $request->getPost('sVoteStars');
        $sVoteMail = strip_tags($request->getPost('sVoteMail'));

        if ($sVoteStars < 1 || $sVoteStars > 10) {
            $sVoteStars = 0;
        }

        $sVoteStars = $sVoteStars / 2;

        if ($this->config['sVOTEUNLOCK']) {
            $active = 0;
        } else {
            $active = 1;
        }

        if (!empty($this->session['sArticleCommentInserts'][$article])) {
            $sql = '
                DELETE FROM s_articles_vote WHERE id=?
            ';
            $this->db->executeUpdate($sql, [
                $this->session['sArticleCommentInserts'][$article],
            ]);
        }

        $date = date('Y-m-d H:i:s');

        $container = Shopware()->Container();
        $shopId = null;
        if ($container->initialized('shop')) {
            $shopId = $container->get('shop')->getId();
        }

        $query = $this->connection->createQueryBuilder();
        $query->insert('s_articles_vote');
        $query->values([
            'articleID' => ':productId',
            'name' => ':name',
            'headline' => ':headline',
            'comment' => ':comment',
            'points' => ':points',
            'datum' => ':datum',
            'active' => ':active',
            'email' => ':email',
            'shop_id' => ':shopId',
        ]);

        $query->setParameters([
            ':productId' => $article,
            ':name' => $sVoteName,
            ':headline' => $sVoteSummary,
            ':comment' => $sVoteComment,
            ':points' => $sVoteStars,
            ':datum' => $date,
            ':active' => $active,
            ':email' => $sVoteMail,
            ':shopId' => $shopId,
        ]);

        $success = $query->execute();
        if (empty($success)) {
            throw new Enlight_Exception('sSaveComment #00: Could not save comment');
        }

        $insertId = $this->connection->lastInsertId();
        if (!isset($this->session['sArticleCommentInserts'])) {
            $this->session['sArticleCommentInserts'] = new ArrayObject();
        }

        $this->session['sArticleCommentInserts'][$article] = $insertId;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.8 without replacement
     *
     * Get id from all products, that belongs to a specific supplier
     *
     * @param int $supplierID Supplier id (s_articles.supplierID)
     *
     * @return array
     */
    public function sGetArticlesBySupplier($supplierID = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (!empty($supplierID)) {
            $this->frontController->Request()->setQuery('sSearch', $supplierID);
        }

        if (!$this->frontController->Request()->getQuery('sSearch')) {
            return [];
        }
        $sSearch = (int) $this->frontController->Request()->getQuery('sSearch');

        return $this->db->fetchAll(
            'SELECT id FROM s_articles WHERE supplierID=? AND active=1 ORDER BY topseller DESC',
            [$sSearch]
        );
    }

    /**
     * @param int|null $categoryId
     * @param Criteria $criteria
     *
     * @throws Enlight_Exception
     *
     * @return ListingArray|false
     */
    public function sGetArticlesByCategory($categoryId = null, Criteria $criteria = null)
    {
        if (Shopware()->Events()->notifyUntil('Shopware_Modules_Articles_sGetArticlesByCategory_Start', [
            'subject' => $this,
            'id' => $categoryId,
        ])) {
            return false;
        }

        $context = $this->contextService->getShopContext();

        $request = Shopware()->Container()->get('front')->Request();
        if (!$request instanceof Enlight_Controller_Request_Request) {
            throw new RuntimeException('Required request not available');
        }

        if (!$criteria instanceof Criteria) {
            $criteria = $this->storeFrontCriteriaFactory->createListingCriteria($request, $context);
        }

        $result = $this->getListing($categoryId, $context, $request, $criteria);

        return $this->legacyEventManager->fireArticlesByCategoryEvents($result, $categoryId, $this);
    }

    /**
     * @deprecated in 5.6, will be removed in 5.8 without replacement
     *
     * Get supplier by id
     *
     * Uses the new Supplier Manager
     *
     * TestCase: /_tests/Shopware/Tests/Modules/Articles/SuppliersTest.php
     *
     * @param int $id - s_articles_supplier.id
     *
     * @return array
     */
    public function sGetSupplierById($id)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $id = (int) $id;
        $categoryId = (int) $this->frontController->Request()->getQuery('sCategory');

        $supplier = Shopware()->Models()->getRepository(Supplier::class)->find($id);
        if (!($supplier instanceof Supplier)) {
            return [];
        }
        $supplier = Shopware()->Models()->toArray($supplier);
        if (!Shopware()->Shop()->getDefault()) {
            $supplier = $this->sGetTranslation($supplier, $supplier['id'], 'supplier');
        }
        $supplier['link'] = $this->config['sBASEFILE'];
        $supplier['link'] .= '?sViewport=cat&sCategory=' . $categoryId . '&sPage=1&sSupplier=0';

        return $supplier;
    }

    /**
     * Product price calculation
     *
     * @param float $price
     * @param float $tax
     * @param int   $taxId
     * @param array $article product data as an array
     *
     * @throws Enlight_Exception
     *
     * @return string $price formatted price
     */
    public function sCalculatingPrice($price, $tax, $taxId = 0, $article = [])
    {
        if (empty($taxId)) {
            throw new Enlight_Exception('Empty taxID in sCalculatingPrice');
        }

        $price = (float) $price;

        // Support tax rate defined by certain conditions
        $getTaxByConditions = $this->getTaxRateByConditions($taxId);
        if ($getTaxByConditions === false) {
            $tax = (float) $tax;
        } else {
            $tax = (float) $getTaxByConditions;
        }

        // Calculate global discount
        if ($this->sSYSTEM->sUSERGROUPDATA['mode'] && $this->sSYSTEM->sUSERGROUPDATA['discount']) {
            $price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA['discount']);
        }
        if ($this->sSYSTEM->sCurrency['factor']) {
            $price = $price * (float) $this->sSYSTEM->sCurrency['factor'];
        }

        // Condition Output-Netto AND NOT overwrite by customer-group
        // OR Output-Netto NOT SET AND tax-settings provided by customer-group
        if (!$this->sSYSTEM->sUSERGROUPDATA['tax'] && $this->sSYSTEM->sUSERGROUPDATA['id']) {
            $price = $this->sFormatPrice($price);
        } else {
            $price = $this->sFormatPrice(round($price * (100 + $tax) / 100, 3));
        }

        return $price;
    }

    /**
     * @param int $taxId
     *
     * @return string|false
     */
    public function getTaxRateByConditions($taxId)
    {
        $context = $this->contextService->getShopContext();
        $taxRate = $context->getTaxRule($taxId);
        if ($taxRate) {
            return number_format($taxRate->getTax(), 2);
        }

        return false;
    }

    /**
     * Product price calculation un-formatted return
     *
     * @param float $price
     * @param float $tax
     * @param bool  $doNotRound
     * @param bool  $ignoreTax
     * @param int   $taxId
     * @param bool  $ignoreCurrency
     * @param array $article        product data as an array
     *
     * @throws Enlight_Exception
     *
     * @return float $price  price non-formatted
     */
    public function sCalculatingPriceNum(
        $price,
        $tax,
        $doNotRound = false,
        $ignoreTax = false,
        $taxId = 0,
        $ignoreCurrency = false,
        $article = []
    ) {
        if (empty($taxId)) {
            throw new Enlight_Exception('Empty tax id in sCalculatingPriceNum');
        }
        // Calculating global discount
        if ($this->sSYSTEM->sUSERGROUPDATA['mode'] && $this->sSYSTEM->sUSERGROUPDATA['discount']) {
            $price = $price - ($price / 100 * $this->sSYSTEM->sUSERGROUPDATA['discount']);
        }

        // Support tax rate defined by certain conditions
        $getTaxByConditions = $this->getTaxRateByConditions($taxId);
        if ($getTaxByConditions === false) {
            $tax = (float) $tax;
        } else {
            $tax = (float) $getTaxByConditions;
        }

        if (!empty($this->sSYSTEM->sCurrency['factor']) && $ignoreCurrency == false) {
            $price = (float) $price * (float) $this->sSYSTEM->sCurrency['factor'];
        }

        if ($ignoreTax == true) {
            return round($price, 2);
        }

        // Show brutto or netto ?
        // Condition Output-Netto AND NOT overwrite by customer-group
        // OR Output-Netto NOT SET AND tax-settings provided by customer-group
        if ($doNotRound == true) {
            if (!$this->sSYSTEM->sUSERGROUPDATA['tax'] && $this->sSYSTEM->sUSERGROUPDATA['id']) {
            } else {
                $price = $price * (100 + $tax) / 100;
            }
        } else {
            if (!$this->sSYSTEM->sUSERGROUPDATA['tax'] && $this->sSYSTEM->sUSERGROUPDATA['id']) {
                $price = round($price, 2);
            } else {
                $price = round($price * (100 + $tax) / 100, 2);
            }
        }

        return $price;
    }

    /**
     * Get product topsellers for a specific category
     *
     * @return array
     */
    public function sGetArticleCharts($category = null)
    {
        $sLimitChart = $this->config['sCHARTRANGE'];
        if (empty($sLimitChart)) {
            $sLimitChart = 20;
        }

        if (!empty($category)) {
            $category = (int) $category;
        } elseif ($this->frontController->Request()->getQuery('sCategory')) {
            $category = (int) $this->frontController->Request()->getQuery('sCategory');
        } else {
            $category = $this->categoryId;
        }

        $context = $this->contextService->getShopContext();

        $criteria = $this->storeFrontCriteriaFactory->createBaseCriteria([$category], $context);
        $criteria->limit($sLimitChart);

        $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));

        $criteria->setFetchCount(false);

        $result = $this->searchService->search($criteria, $context);
        $products = $this->legacyStructConverter->convertListProductStructList($result->getProducts());

        Shopware()->Events()->notify(
            'Shopware_Modules_Articles_GetArticleCharts',
            ['subject' => $this, 'category' => $category, 'articles' => $products]
        );

        return $products;
    }

    /**
     * Check if a product has an instant download
     *
     * @deprecated in 5.5, this function will be removed in 5.7 without replacement
     *
     * @param int  $id        s_articles.id
     * @param int  $detailsID s_articles_details.id
     * @param bool $realtime
     *
     * @return bool
     */
    public function sCheckIfEsd($id, $detailsID, $realtime = false)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        // Check if this product is esd-only (check in variants, too -> later)
        $id = (int) $id;
        if ($detailsID) {
            $detailsID = (int) $detailsID;
            $sqlGetEsd = "
            SELECT id, serials FROM s_articles_esd WHERE articleID=$id
            AND articledetailsID=$detailsID
            ";
        } else {
            $sqlGetEsd = "
            SELECT id, serials FROM s_articles_esd WHERE articleID=$id
            ";
        }

        $getEsd = $this->db->fetchRow($sqlGetEsd);
        if (isset($getEsd['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Read the id from all products that are in the same category as the product specified by parameter
     * (For product navigation in top of detail page)
     *
     * @param string $orderNumber
     * @param int    $categoryId
     *
     * @return array
     */
    public function getProductNavigation($orderNumber, $categoryId, Enlight_Controller_Request_RequestHttp $request)
    {
        $categoryId = (int) $categoryId;
        $context = $this->contextService->getShopContext();

        $criteria = $this->createProductNavigationCriteria($categoryId, $context, $request);
        // Elasticsearch default max_result_window
        $criteria->limit(10000);

        $searchResult = $this->productNumberSearch->search($criteria, $context);

        $navigation = $this->buildNavigation(
            $searchResult,
            $orderNumber,
            $categoryId,
            $context
        );

        $navigation['currentListing']['link'] = $this->buildCategoryLink($categoryId, $request);

        return $navigation;
    }

    /**
     * Read the unit types from a certain product
     *
     * @param int $id s_articles.id
     *
     * @return array
     */
    public function sGetUnit($id)
    {
        static $cache = [];
        if (isset($cache[$id])) {
            return $cache[$id];
        }
        $unit = $this->db->fetchRow('
          SELECT unit, description FROM s_core_units WHERE id=?
        ', [$id]);

        if (!empty($unit) && !Shopware()->Shop()->get('skipbackend')) {
            $sql = "SELECT objectdata
                    FROM s_core_translations
                    WHERE objecttype='config_units'
                      AND objectlanguage=" . Shopware()->Shop()->getId();
            $translation = $this->db->fetchOne($sql);
            if (!empty($translation)) {
                $translation = @unserialize($translation, ['allowed_classes' => false]);

                if ($translation === false) {
                    $translation = [];
                }
            }
            if (!empty($translation[$id])) {
                $unit = array_merge($unit, $translation[$id]);
            }
        }

        return $cache[$id] = $unit;
    }

    /**
     * Get discounts and discount table for a certain product
     *
     * @param string $customergroup id of customergroup key
     * @param int    $groupID       customer group id
     * @param float  $listprice     default price
     * @param int    $quantity
     * @param bool   $doMatrix      Return array with all block prices
     * @param array  $articleData   current product
     * @param bool   $ignore        deprecated
     *
     * @return array|float|false|void
     */
    public function sGetPricegroupDiscount(
        $customergroup,
        $groupID,
        $listprice,
        $quantity,
        $doMatrix = true,
        $articleData = [],
        $ignore = false
    ) {
        $getBlockPricings = [];
        $laststart = null;
        $divPercent = null;

        if (!empty($this->sSYSTEM->sUSERGROUPDATA['groupkey'])) {
            $customergroup = $this->sSYSTEM->sUSERGROUPDATA['groupkey'];
        }
        if (!$customergroup || !$groupID) {
            return false;
        }

        $sql = '
        SELECT s_core_pricegroups_discounts.discount AS discount,discountstart
        FROM
            s_core_pricegroups_discounts,
            s_core_customergroups AS scc
        WHERE
            groupID=? AND customergroupID = scc.id
        AND
            scc.groupkey = ?
        GROUP BY discount
        ORDER BY discountstart ASC
        ';

        $getGroups = $this->db->fetchAll($sql, [(int) $groupID, $customergroup]);
        $priceMatrix = [];

        if (\count($getGroups)) {
            foreach ($getGroups as $group) {
                $priceMatrix[$group['discountstart']] = ['percent' => $group['discount']];
                if (!empty($group['discount'])) {
                    $discountsFounds = true;
                }
            }

            if (empty($discountsFounds)) {
                if (empty($doMatrix)) {
                    return $listprice;
                }

                return;
            }

            if (!empty($doMatrix) && \count($priceMatrix) == 1) {
                return;
            }

            if (empty($doMatrix)) {
                $matchingPercent = 0;

                // Getting price rule matching to quantity
                foreach ($priceMatrix as $start => $percent) {
                    if ($start <= $quantity) {
                        $matchingPercent = $percent['percent'];
                    }
                }

                if ($matchingPercent) {
                    return $listprice / 100 * (100 - $matchingPercent);
                }
            } else {
                $i = 0;
                // Building price-ranges
                foreach ($priceMatrix as $start => $percent) {
                    $to = $start - 1;
                    if ($laststart && $to) {
                        $priceMatrix[$laststart]['to'] = $to;
                    }
                    $laststart = $start;
                }

                foreach ($priceMatrix as $start => $percent) {
                    $getBlockPricings[$i]['from'] = $start;
                    $getBlockPricings[$i]['to'] = $percent['to'];
                    if ($i === 0 && $ignore) {
                        $getBlockPricings[$i]['price'] = $this->sCalculatingPrice(
                            $listprice / 100 * 100,
                            $articleData['tax'],
                            $articleData['taxID'],
                            $articleData
                        );
                        $divPercent = $percent['percent'];
                    } else {
                        if ($ignore) {
                            $percent['percent'] -= $divPercent;
                        }
                        $getBlockPricings[$i]['price'] = $this->sCalculatingPrice(
                            $listprice / 100 * (100 - $percent['percent']),
                            $articleData['tax'],
                            $articleData['taxID'],
                            $articleData
                        );
                    }
                    ++$i;
                }

                return $getBlockPricings;
            }
        }
        if (!empty($doMatrix)) {
            return;
        }

        return $listprice;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     *
     * Get the cheapest price for a certain product
     *
     * @param int  $article                   id
     * @param int  $group                     customer group id
     * @param int  $pricegroup                pricegroup id
     * @param bool $usepricegroups            consider pricegroups
     * @param bool $realtime
     * @param bool $returnArrayIfConfigurator
     * @param bool $checkLiveshopping
     *
     * @return float|array cheapest price or null
     */
    public function sGetCheapestPrice(
        $article,
        $group,
        $pricegroup,
        $usepricegroups,
        $realtime = false,
        $returnArrayIfConfigurator = false,
        $checkLiveshopping = false
    ) {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($group != $this->sSYSTEM->sUSERGROUP) {
            $fetchGroup = $group;
        } else {
            $fetchGroup = $this->sSYSTEM->sUSERGROUP;
        }

        if (empty($usepricegroups)) {
            $sql = '
            SELECT price FROM s_articles_prices, s_articles_details WHERE
            s_articles_details.id=s_articles_prices.articledetailsID AND
            pricegroup=?
            AND s_articles_details.articleID=?
            GROUP BY ROUND(price,2)
            ORDER BY price ASC
            LIMIT 2
        ';
        } else {
            $sql = "
            SELECT price FROM s_articles_details
            LEFT JOIN
            s_articles_prices ON s_articles_details.id=s_articles_prices.articledetailsID AND
            pricegroup=? AND s_articles_prices.from = '1'
            WHERE
            s_articles_details.articleID=?
            GROUP BY ROUND(price,2)
            ORDER BY price ASC
            LIMIT 2
            ";
        }

        $queryCheapestPrice = $this->db->fetchAll(
            $sql,
            [$fetchGroup, $article]
        );

        if (\count($queryCheapestPrice) > 1) {
            $cheapestPrice = $queryCheapestPrice[0]['price'];
            if (empty($cheapestPrice)) {
                // No Price for this customer-group fetch defaultprice
                $sql = "
                SELECT price FROM s_articles_details
                LEFT JOIN s_articles_prices
                  ON s_articles_details.id=s_articles_prices.articledetailsID
                  AND pricegroup='EK'
                  AND s_articles_prices.from = '1'
                WHERE
                  s_articles_details.articleID=$article
                GROUP BY ROUND(price,2)
                ORDER BY price ASC
                LIMIT 2
                ";

                $queryCheapestPrice = $this->db->fetchAll($sql);
                if (\count($queryCheapestPrice) > 1) {
                    $cheapestPrice = $queryCheapestPrice[0]['price'];
                } else {
                    $cheapestPrice = 0;
                    $listPrice = $queryCheapestPrice[0]['price'];
                }
            }
            $foundPrice = true;
        } else {
            $cheapestPrice = 0;
            $listPrice = $queryCheapestPrice[0]['price'];
        }

        $sql = '
        SELECT s_core_pricegroups_discounts.discount AS discount,discountstart
        FROM
            s_core_pricegroups_discounts,
            s_core_customergroups AS scc
        WHERE
            groupID=? AND customergroupID = scc.id
        AND
            scc.groupkey = ?
        GROUP BY discount
        ORDER BY discountstart ASC
        ';

        $getGroups = $this->db->fetchAll($sql, [$pricegroup, $this->sSYSTEM->sUSERGROUP]);

        // if there are no discounts for this customergroup don't show "ab:"
        if (empty($getGroups)) {
            return $cheapestPrice;
        }

        // Updated / Fixed 28.10.2008 - STH
        if (!empty($usepricegroups)) {
            $listPrice = null;
            $foundPrice = null;

            if (!empty($cheapestPrice)) {
                $listPrice = $cheapestPrice;
            } else {
                $foundPrice = true;
            }

            $returnPrice = $this->sGetPricegroupDiscount(
                $this->sSYSTEM->sUSERGROUP,
                $pricegroup,
                $listPrice,
                99999,
                false
            );

            if (!empty($returnPrice) && $foundPrice) {
                $cheapestPrice = $returnPrice;
            } elseif ($foundPrice !== null && $returnPrice === 0.) {
                $cheapestPrice = '0.00';
            } else {
                $cheapestPrice = '0';
            }
        }

        if (isset($queryCheapestPrice[0]['count'])
            && $queryCheapestPrice[0]['count'] > 1
            && empty($queryCheapestPrice[1]['price'])
            && !empty($returnArrayIfConfigurator)
        ) {
            return [$cheapestPrice, $queryCheapestPrice[0]['count']];
        }

        return $cheapestPrice;
    }

    /**
     * Get one product with all available data
     *
     * @param int         $id          article id
     * @param int|null    $sCategoryID
     * @param string|null $number
     *
     * @return array
     */
    public function sGetArticleById($id = 0, $sCategoryID = null, $number = null, array $selection = [])
    {
        if ($sCategoryID === null) {
            $sCategoryID = $this->frontController->Request()->getParam('sCategory');
        }

        $providedNumber = $number;

        /**
         * Validates the passed configuration array for the configurator selection
         */
        $selection = $this->getCurrentSelection($selection);

        if (!$number) {
            $number = $this->productNumberService->getMainProductNumberById($id);
        }

        $context = $this->contextService->getShopContext();

        /**
         * Checks which product number should be loaded. If a configuration passed.
         */
        $productNumber = $this->productNumberService->getAvailableNumber(
            $number,
            $context,
            $selection
        );

        if (!$productNumber) {
            return [];
        }

        $product = $this->productService->get($productNumber, $context);

        if (!$product) {
            return [];
        }

        if ($this->config->get('hideNoInStock') && !$product->isAvailable()) {
            return [];
        }

        if ($product->hasConfigurator()) {
            $type = $this->getConfiguratorType($product->getId());

            /**
             * Check if a variant should be loaded. And load the configuration for the variant for pre selection.
             *
             * Requires the following scenario:
             * 1. $number has to be set (without a number we can't load a configuration)
             * 2. $number is equals to $productNumber (if the order number is invalid or inactive fallback to main variant)
             * 3. $configuration is empty (Customer hasn't not set an own configuration)
             */
            if ($providedNumber && $providedNumber == $productNumber && empty($selection) || $type === 0) {
                $selection = $product->getSelectedOptions();
            }
        }

        $categoryId = (int) $sCategoryID;
        if (empty($categoryId) || $categoryId == Shopware()->Shop()->getId()) {
            $categoryId = Shopware()->Modules()->Categories()->sGetCategoryIdByArticleId($id);
        }

        return $this->getLegacyProduct(
            $product,
            $categoryId,
            $selection
        );
    }

    /**
     * calculates the reference price with the base price data
     *
     * @param string|float $price         | the final price which will be shown
     * @param string|float $purchaseUnit
     * @param string|float $referenceUnit
     *
     * @return float
     */
    public function calculateReferencePrice($price, $purchaseUnit, $referenceUnit)
    {
        $purchaseUnit = (float) $purchaseUnit;
        $referenceUnit = (float) $referenceUnit;

        if (\is_string($price)) {
            $price = (float) str_replace(',', '.', $price);
        } elseif (!\is_float($price)) {
            throw new UnexpectedValueException('Parameter price needs to be of type "float" or a numeric string');
        }

        if ($purchaseUnit === 0.0 || $referenceUnit === 0.0) {
            return 0;
        }

        return $price / $purchaseUnit * $referenceUnit;
    }

    /**
     * Formats product prices
     *
     * @param float $price
     *
     * @return string
     */
    public function sFormatPrice($price)
    {
        $price = str_replace(',', '.', (string) $price);
        $price = $this->sRound($price);
        $price = str_replace('.', ',', (string) $price); // Replaces points with commas
        $commaPos = strpos((string) $price, ',');
        if ($commaPos) {
            $part = substr((string) $price, $commaPos + 1, \strlen((string) $price) - $commaPos);
            switch (\strlen($part)) {
                case 1:
                    $price .= '0';
                    break;
                case 2:
                    break;
            }
        } else {
            if (!$price) {
                $price = '0';
            } else {
                $price .= ',00';
            }
        }

        return $price;
    }

    /**
     * Round product price
     *
     * @param float|string $moneyfloat
     *
     * @return float price
     */
    public function sRound($moneyfloat = null)
    {
        if (is_numeric($moneyfloat)) {
            $moneyfloat = sprintf('%F', $moneyfloat);
        }
        $money_str = explode('.', (string) $moneyfloat);
        if (empty($money_str[1])) {
            $money_str[1] = 0;
        }
        $money_str[1] = substr((string) $money_str[1], 0, 3); // convert to rounded (to the nearest thousandth) string

        $value = (float) ($money_str[0] . '.' . $money_str[1]);

        // round handles "-0" different since PHP 7.4, @see https://bugs.php.net/bug.php?id=78660
        if ($value === -0.0) {
            return 0;
        }

        return round($value, 2);
    }

    /**
     * @param string $ordernumber
     *
     * @return array<string, mixed>|false
     */
    public function sGetProductByOrdernumber($ordernumber)
    {
        if (Shopware()->Events()->notifyUntil(
            'Shopware_Modules_Articles_sGetProductByOrdernumber_Start',
            ['subject' => $this, 'value' => $ordernumber]
        )) {
            return false;
        }

        return Shopware()->Events()->filter(
            'Shopware_Modules_Articles_sGetProductByOrdernumber_FilterResult',
            $this->getPromotion(null, $ordernumber),
            ['subject' => $this, 'value' => $ordernumber]
        );
    }

    /**
     * Get basic product data in various modes (firmly defined by id, random, top, new)
     *
     * @param string                    $mode      Modus (fix, random, top, new)
     * @param int                       $category  filter by category
     * @param int|string|numeric-string $value     product id / order number for firmly defined products
     * @param bool                      $withImage
     *
     * @return array<string, mixed>|false
     */
    public function sGetPromotionById($mode, $category = 0, $value = 0, $withImage = false)
    {
        $category = (int) $category;
        $notifyUntil = $this->eventManager->notifyUntil(
            'Shopware_Modules_Articles_GetPromotionById_Start',
            [
                'subject' => $this,
                'mode' => $mode,
                'category' => $category,
                'value' => $value,
            ]
        );

        if ($notifyUntil) {
            return false;
        }

        $value = $this->getPromotionNumberByMode($mode, $category, $value);

        if ($value === '') {
            return false;
        }

        $result = $this->getPromotion($category, $value);

        if (!$result) {
            return false;
        }

        return $result;
    }

    /**
     * Optimize text, strip html tags etc.
     *
     * @param string $text
     *
     * @return string $text
     */
    public function sOptimizeText($text)
    {
        $text = html_entity_decode($text, ENT_NOQUOTES, 'UTF-8');
        $text = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $text);
        $text = preg_replace('!<[^>]*?>!u', ' ', $text);
        $text = preg_replace('/\s\s+/u', ' ', $text);

        return trim((string) $text);
    }

    /**
     * Internal helper function to get the cover image of a product.
     * If the orderNumber parameter is set, the function checks first
     * if an variant image configured. If this is the case, this
     * image will be used as cover image. Otherwise the function calls the
     * getArticleMainCover function which returns the absolute main image
     *
     * @param int    $articleId
     * @param string $orderNumber
     * @param Album  $articleAlbum
     *
     * @return array
     */
    public function getArticleCover($articleId, $orderNumber, $articleAlbum)
    {
        if (!empty($orderNumber)) {
            // Check for specify variant images. For example:
            // If the user is on a detail page of a shoe and select the color "red"
            // we have to check if the current variant has an own configured picture for a red shoe.
            // The query selects orders the result at first by the image main flag, at second for the position.
            $cover = $this->getProductRepository()
                ->getVariantImagesByArticleNumberQuery($orderNumber, 0, 1)
                ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        }

        // If we have found a configured product image which has the same options like the passed product order number
        // we have to return this one.
        if (!empty($cover)) {
            return $this->getDataOfProductImage($cover, $articleAlbum);
        }

        // If we haven't found and variant image we have to select the first image which has no configuration.
        // The query orders the result at first by the image main flag, at second by the position.
        $cover = $this->getProductRepository()
            ->getArticleCoverImageQuery($articleId)
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (!empty($cover)) {
            return $this->getDataOfProductImage($cover, $articleAlbum);
        }

        // If no variant or normal product image is found we will return the main image of the product even if this image has a variant restriction
        return $this->getArticleMainCover($articleId, $articleAlbum);
    }

    /**
     * Returns the the absolute main product image
     * This method returns the main cover depending on the main flag no matter if any variant restriction is set
     *
     * @param int   $articleId
     * @param Album $articleAlbum
     *
     * @return array
     */
    public function getArticleMainCover($articleId, $articleAlbum)
    {
        $cover = $this->getProductRepository()
            ->getArticleFallbackCoverQuery($articleId)
            ->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        return $this->getDataOfProductImage($cover, $articleAlbum);
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7. Use the sArticles::sGetArticlePictures instead.
     *
     * Wrapper method to specialize the sGetArticlePictures method for the listing images
     *
     * @param int  $articleId
     * @param bool $forceMainImage | if true this will return the main image no matter which variant restriction is set
     *
     * @return array
     */
    public function getArticleListingCover($articleId, $forceMainImage = false)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Use the sArticles::sGetArticlePictures instead.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->sGetArticlePictures($articleId, true, 0, null, false, false, $forceMainImage);
    }

    /**
     * Get all pictures from a certain product
     *
     * @param int    $sArticleID
     * @param bool   $onlyCover
     * @param int    $pictureSize    | unused variable
     * @param string $ordernumber
     * @param bool   $allImages      | unused variable
     * @param bool   $realtime       | unused variable
     * @param bool   $forceMainImage | will return the main image no matter which variant restriction is set
     *
     * @return array
     */
    public function sGetArticlePictures(
        $sArticleID,
        $onlyCover = true,
        $pictureSize = 0,
        $ordernumber = null,
        $allImages = false,
        $realtime = false,
        $forceMainImage = false
    ) {
        static $articleAlbum;
        if ($articleAlbum === null) {
            // Now we search for the default product album of the media manager, this album contains the thumbnail configuration.
            /** @var Album $articleAlbum */
            $articleAlbum = $this->getMediaRepository()
                ->getAlbumWithSettingsQuery(-1)
                ->getOneOrNullResult();
        }

        // First we convert the passed product id into an integer to prevent sql injections
        $productId = (int) $sArticleID;

        Shopware()->Events()->notify(
            'Shopware_Modules_Articles_GetArticlePictures_Start',
            ['subject' => $this, 'id' => $productId]
        );

        // First we get the product cover
        if ($forceMainImage) {
            $cover = $this->getArticleMainCover($productId, $articleAlbum);
        } else {
            $cover = $this->getArticleCover($productId, $ordernumber, $articleAlbum);
        }

        if ($onlyCover) {
            $cover = Shopware()->Events()->filter(
                'Shopware_Modules_Articles_GetArticlePictures_FilterResult',
                $cover,
                ['subject' => $this, 'id' => $productId]
            );

            return $cover;
        }

        // Now we select all product images of the passed product id.
        $productImages = $this->getProductRepository()
            ->getArticleImagesQuery($productId)
            ->getArrayResult();

        // If an order number passed to the function, we have to select the configured variant images
        $variantImages = [];
        if (!empty($ordernumber)) {
            $variantImages = $this->getProductRepository()
                ->getVariantImagesByArticleNumberQuery($ordernumber)
                ->getArrayResult();
        }
        // We have to collect the already added image ids, otherwise the images
        // would be displayed multiple times.
        $addedImages = [$cover['id']];
        $images = [];

        // First we add all variant images, this images has a higher priority as the normal product images
        foreach ($variantImages as $variantImage) {
            // If the image wasn't added already, we can add the image
            if (!\in_array($variantImage['id'], $addedImages)) {
                // First we have to convert the image data, to resolve the image path and get the thumbnail configuration
                $image = $this->getDataOfProductImage($variantImage, $articleAlbum);

                // After the data was converted we add the image to the result array and add the id to the addedImages array
                $images[] = $image;
                $addedImages[] = $variantImage['id'];
            }
        }

        // After the variant images added, we can add the normal images, this images has a lower priority as the variant images
        foreach ($productImages as $productImage) {
            // Add only normal images without any configuration
            // If the image wasn't added already, we can add the image
            if (!\in_array($productImage['id'], $addedImages)) {
                // First we have to convert the image data, to resolve the image path and get the thumbnail configuration
                $image = $this->getDataOfProductImage($productImage, $articleAlbum);

                // After the data was converted we add the image to the result array and add the id to the addedImages array
                $images[] = $image;
                $addedImages[] = $productImage['id'];
            }
        }

        $images = Shopware()->Events()->filter(
            'Shopware_Modules_Articles_GetArticlePictures_FilterResult',
            $images,
            ['subject' => $this, 'id' => $productId]
        );

        return $images;
    }

    /**
     * Get product id by ordernumber
     *
     * @param string $ordernumber
     *
     * @return int|false $id or false
     */
    public function sGetArticleIdByOrderNumber($ordernumber)
    {
        $checkForProduct = $this->db->fetchRow(
            'SELECT articleID AS id FROM s_articles_details WHERE ordernumber=?',
            [$ordernumber]
        );

        if (isset($checkForProduct['id'])) {
            return $checkForProduct['id'];
        }

        return false;
    }

    /**
     * Get name from a certain product by order number
     *
     * @param string $orderNumber
     * @param bool   $returnAll   Return only name or additional data, too
     * @param bool   $translate   Disables the translation of the product if set to false
     *
     * @return ($returnAll is true ? array{id: numeric-string, main_detail_id: numeric-string, did: numeric-string, articleName: string, additionaltext: string, configurator_set_id: numeric-string} : string)|false
     */
    public function sGetArticleNameByOrderNumber($orderNumber, $returnAll = false, $translate = true)
    {
        $product = $this->db->fetchRow(
            'SELECT
                s_articles.id,
                s_articles.main_detail_id,
                s_articles_details.id AS did,
                s_articles.name AS articleName,
                additionaltext,
                s_articles.configurator_set_id
            FROM s_articles_details, s_articles
            WHERE ordernumber = :orderNumber
                AND s_articles.id = s_articles_details.articleID',
            [
                'orderNumber' => $orderNumber,
            ]
        );

        if (!$product) {
            return false;
        }

        // Load translations for product or variant
        if ($translate) {
            if ((int) $product['did'] !== (int) $product['main_detail_id']) {
                $product = $this->sGetTranslation(
                    $product,
                    $product['did'],
                    'variant'
                );
            } else {
                $product = $this->sGetTranslation(
                    $product,
                    $product['id'],
                    'article'
                );
            }
        }

        // If product has variants, we need to append the additional text to the name
        if ($product['configurator_set_id'] > 0) {
            $productStruct = new ListProduct(
                (int) $product['id'],
                (int) $product['did'],
                $orderNumber
            );

            $productStruct->setAdditional($product['additionaltext']);

            $context = $this->contextService->getShopContext();
            $productStruct = $this->additionalTextService->buildAdditionalText($productStruct, $context);

            if (!$returnAll) {
                return $product['articleName'] . ' ' . $productStruct->getAdditional();
            }

            $product['additionaltext'] = $productStruct->getAdditional();
        }

        if (!$returnAll) {
            return $product['articleName'];
        }

        return $product;
    }

    /**
     * Get product name by s_articles.id
     *
     * @param int  $articleId
     * @param bool $returnAll
     *
     * @return ($returnAll is true ? array{id: numeric-string, main_detail_id: numeric-string, did: numeric-string, articleName: string, configurator_set_id: numeric-string} : string)|false
     */
    public function sGetArticleNameByArticleId($articleId, $returnAll = false)
    {
        $orderNumber = (string) $this->db->fetchOne(
            'SELECT ordernumber FROM s_articles_details WHERE kind=1 AND articleID=?',
            [$articleId]
        );

        return $this->sGetArticleNameByOrderNumber($orderNumber, $returnAll);
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     *
     * Get product taxrate by id
     *
     * @param int $id product id
     *
     * @return float|false tax or false
     */
    public function sGetArticleTaxById($id)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $checkForProduct = $this->db->fetchRow(
            'SELECT s_core_tax.tax AS tax
            FROM s_core_tax, s_articles
            WHERE s_articles.id=?
              AND s_articles.taxID = s_core_tax.id',
            [$id]
        );

        if (isset($checkForProduct['tax'])) {
            return $checkForProduct['tax'];
        }

        return false;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7. Use sArticle::sGetTranslation instead.
     *
     * Read translation for one or more products
     *
     * @param array  $data
     * @param string $object
     *
     * @return array
     */
    public function sGetTranslations($data, $object)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Use sArticle::sGetTranslation instead.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (Shopware()->Shop()->get('skipbackend') || empty($data)) {
            return $data;
        }
        $language = Shopware()->Shop()->getId();
        $fallback = Shopware()->Shop()->get('fallback');
        $ids = $this->db->quote(array_keys($data));

        switch ($object) {
            case 'article':
                $map = [
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'description_long',
                    'txtshippingtime' => 'shippingtime',
                    'txtArtikel' => 'articleName',
                    'txtzusatztxt' => 'additionaltext',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packunit',
                ];
                break;
            case 'configuratorgroup':
                $map = [
                    'description' => 'groupdescription',
                    'name' => 'groupname',
                ];
                break;
            default:
                return $data;
        }

        $object = $this->db->quote($object);

        $sql = '';
        if (!empty($fallback)) {
            $sql .= "
                SELECT s.objectdata, s.objectkey
                FROM s_core_translations s
                WHERE
                    s.objecttype = $object
                AND
                    s.objectkey IN ($ids)
                AND
                    s.objectlanguage = '$fallback'
            UNION ALL
            ";
        }
        $sql .= "
            SELECT s.objectdata, s.objectkey
            FROM s_core_translations s
            WHERE
                s.objecttype = $object
            AND
                s.objectkey IN ($ids)
            AND
                s.objectlanguage = '$language'
        ";

        $translations = $this->db->fetchAll($sql);

        if (empty($translations)) {
            return $data;
        }

        foreach ($translations as $translation) {
            $productId = (int) $translation['objectkey'];
            $object = @unserialize($translation['objectdata'], ['allowed_classes' => false]);

            if ($object === false) {
                $object = [];
            }

            foreach ($object as $translateKey => $value) {
                if (isset($map[$translateKey])) {
                    $key = $map[$translateKey];
                } else {
                    $key = $translateKey;
                }
                if (!empty($value) && \array_key_exists($key, $data[$productId])) {
                    $data[$productId][$key] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * Get translation for an object (article / variant / link / download / supplier)
     *
     * @param array  $data
     * @param int    $id
     * @param string $object
     * @param int    $language
     *
     * @return array
     */
    public function sGetTranslation($data, $id, $object, $language = null)
    {
        if (Shopware()->Shop()->get('skipbackend')) {
            return $data;
        }
        $id = (int) $id;
        $language = $language ?: Shopware()->Shop()->getId();
        $fallback = Shopware()->Shop()->get('fallback');

        switch ($object) {
            case 'article':
                $map = [
                    'txtshortdescription' => 'description',
                    'txtlangbeschreibung' => 'description_long',
                    'txtshippingtime' => 'shippingtime',
                    'txtArtikel' => 'articleName',
                    'txtzusatztxt' => 'additionaltext',
                    'txtkeywords' => 'keywords',
                    'txtpackunit' => 'packunit',
                ];
                break;
            case 'variant':
                $map = ['txtshippingtime' => 'shippingtime', 'txtzusatztxt' => 'additionaltext', 'txtpackunit' => 'packunit'];
                break;
            case 'link':
                $map = ['linkname' => 'description'];
                break;
            case 'download':
                $map = ['downloadname' => 'description'];
                break;
            case 'configuratoroption':
                $map = [
                    'name' => 'optionname',
                ];
                break;
            case 'configuratorgroup':
                $map = [
                    'description' => 'groupdescription',
                    'name' => 'groupname',
                ];
                break;
            case 'supplier':
                $map = [
                    'meta_title' => 'title',
                    'description' => 'description',
                ];
                break;
        }

        $sql = "
            SELECT objectdata FROM s_core_translations
            WHERE objecttype = '$object'
            AND objectkey = ?
            AND objectlanguage = '$language'
        ";
        $objectData = $this->db->fetchOne($sql, [$id]);
        if (!empty($objectData)) {
            $objectData = @unserialize($objectData, ['allowed_classes' => false]);

            if ($objectData === false) {
                $objectData = [];
            }
        } else {
            $objectData = [];
        }
        if (!empty($fallback)) {
            $sql = "
                SELECT objectdata FROM s_core_translations
                WHERE objecttype = '$object'
                AND objectkey = $id
                AND objectlanguage = '$fallback'
            ";
            $objectFallback = $this->db->fetchOne($sql);
            if (!empty($objectFallback)) {
                $objectFallback = unserialize($objectFallback, ['allowed_classes' => false]);

                if ($objectFallback === false) {
                    $objectFallback = [];
                }

                $objectData = array_merge($objectFallback, $objectData);
            }
        }
        if (!empty($objectData)) {
            foreach ($objectData as $translateKey => $value) {
                $key = $translateKey;
                if (isset($map[$translateKey])) {
                    $key = $map[$translateKey];
                }
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * Get array of images from a certain configurator combination
     *
     * @param array  $sArticle     Associative array with all product data
     * @param string $sCombination Currently active combination
     *
     * @return array
     */
    public function sGetConfiguratorImage($sArticle, $sCombination = '')
    {
        if (!empty($sArticle['sConfigurator']) || !empty($sCombination)) {
            $foundImage = false;
            $configuratorImages = false;
            $mainKey = 0;

            if (empty($sCombination)) {
                if (!empty($sArticle['image']['res']['description'])) {
                    $sArticle['image']['description'] = $sArticle['image']['res']['description'];
                }
                $sArticle['image']['relations'] = $sArticle['image']['res']['relations'];
                foreach ($sArticle['sConfigurator'] as $key => $group) {
                    foreach ($group['values'] as $key2 => $option) {
                        $groupVal = $group['groupnameOrig'] ? $group['groupnameOrig'] : $group['groupname'];
                        $groupVal = str_replace(['/', ' '], '', $groupVal);
                        $optionVal = $option['optionnameOrig'] ? $option['optionnameOrig'] : $option['optionname'];
                        $optionVal = str_replace(['/', ' '], '', $optionVal);
                        if (!empty($option['selected'])) {
                            $replacedOptionVal = str_replace(' ', '', $optionVal);
                            $referenceImages[strtolower($groupVal . ':' . $optionVal)] = true;
                        }
                    }
                }

                foreach (array_merge($sArticle['images'] ?? [], [\count($sArticle['images'] ?? []) => $sArticle['image']]) as $value) {
                    if (preg_match('/(.*){(.*)}/', $value['relations'])) {
                        $configuratorImages = true;

                        break;
                    }
                }
            } else {
                $referenceImages = array_flip(explode('$$', $sCombination));
                foreach ($referenceImages as $key => $v) {
                    $keyNew = str_replace('/', '', $key);
                    unset($referenceImages[$key]);
                    $referenceImages[$keyNew] = $v;
                }
                $sArticle = ['images' => $sArticle, 'image' => []];
                foreach ($sArticle['images'] as $k => $value) {
                    if (preg_match('/(.*){(.*)}/', $value['relations'])) {
                        $configuratorImages = true;
                    }
                    if ((int) $value['main'] === 1) {
                        $mainKey = $k;
                    }
                }
                if (empty($configuratorImages)) {
                    return $sArticle['images'][$mainKey];
                }
            }

            if (!empty($configuratorImages)) {
                $sArticle['images'] = array_merge($sArticle['images'], [\count($sArticle['images']) => $sArticle['image']]);

                unset($sArticle['image']);

                $positions = [];

                foreach ($sArticle['images'] as $imageKey => $image) {
                    if (empty($image['src']['original']) || empty($image['relations'])) {
                        continue;
                    }
                    $string = $image['relations'];
                    // Parsing string
                    $stringParsed = [];

                    preg_match('/(.*){(.*)}/', $string, $stringParsed);

                    $relation = $stringParsed[1];
                    $available = explode('/', $stringParsed[2]);

                    if (!@\count($available)) {
                        $available = [0 => $stringParsed[2]];
                    }

                    $imageFailedCheck = [];

                    foreach ($available as $checkKey => $checkCombination) {
                        $getCombination = explode(':', $checkCombination);
                        $group = $getCombination[0];
                        $option = $getCombination[1];

                        if (isset($referenceImages[strtolower($checkCombination)])) {
                            $imageFailedCheck[] = true;
                        }
                    }
                    if ($relation === '||' && \count($imageFailedCheck) && \count($imageFailedCheck) >= 1 && \count($available) >= 1) { // OR combination
                        $sArticle['images'][$imageKey]['relations'] = '';
                        $positions[$image['position']] = $imageKey;
                    } elseif ($relation === '&' && \count($imageFailedCheck) === \count($available)) { // AND combination
                        $sArticle['images'][$imageKey]['relations'] = '';
                        $positions[$image['position']] = $imageKey;
                    } else {
                        unset($sArticle['images'][$imageKey]);
                    }
                }
                ksort($positions);
                $posKeys = array_keys($positions);

                $sArticle['image'] = $sArticle['images'][$positions[$posKeys[0]]];
                unset($sArticle['images'][$positions[$posKeys[0]]]);

                if (!empty($sCombination)) {
                    return $sArticle['image'];
                }
            }
        }

        if (!empty($sArticle['images'])) {
            foreach ($sArticle['images'] as $key => $image) {
                if ($image['relations'] === '&{}' || $image['relations'] === '||{}') {
                    $sArticle['images'][$key]['relations'] = '';
                }
            }
        }

        return $sArticle;
    }

    /**
     * @param string $mode
     * @param int    $category
     *
     * @return int
     */
    protected function getRandomArticle($mode, $category = 0)
    {
        $category = (int) $category;
        $context = $this->contextService->getShopContext();
        if (empty($category)) {
            $category = $context->getShop()->getCategory()->getId();
        }

        $criteria = $this->storeFrontCriteriaFactory->createBaseCriteria([$category], $context);

        $criteria->offset(0)
            ->limit(100);

        switch ($mode) {
            case 'top':
                $criteria->addSorting(new PopularitySorting(SortingInterface::SORT_DESC));
                break;
            case 'new':
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
                break;
            default:
                $criteria->addSorting(new ReleaseDateSorting(SortingInterface::SORT_DESC));
        }

        $criteria->setFetchCount(false);

        $result = $this->productNumberSearch->search($criteria, $context);

        $ids = array_map(function (BaseProduct $product) {
            return $product->getId();
        }, $result->getProducts());

        $diff = array_diff($ids, $this->cachePromotions);
        if (empty($diff)) {
            $diff = $ids;
        }

        if ($mode === 'new') {
            $value = current($diff);
        } else {
            shuffle($diff);
            $value = $diff[array_rand($diff)];
        }

        $this->cachePromotions[] = (int) $value;

        return $value;
    }

    /**
     * Helper function to get access to the media repository.
     */
    private function getMediaRepository(): MediaRepository
    {
        if ($this->mediaRepository === null) {
            $this->mediaRepository = Shopware()->Models()->getRepository(Media::class);
        }

        return $this->mediaRepository;
    }

    /**
     * Helper function to get access to the product repository.
     */
    private function getProductRepository(): ArticleRepository
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository(Article::class);
        }

        return $this->articleRepository;
    }

    /**
     * @throws Exception
     */
    private function createProductNavigationCriteria(
        int $categoryId,
        ShopContextInterface $context,
        Enlight_Controller_Request_RequestHttp $request
    ): Criteria {
        $streamId = $this->getStreamIdOfCategory($categoryId);
        if ($streamId === null) {
            return $this->storeFrontCriteriaFactory->createProductNavigationCriteria(
                $request,
                $context,
                $categoryId
            );
        }

        $factory = Shopware()->Container()->get(CriteriaFactoryInterface::class);
        $criteria = $factory->createCriteria($request, $context);
        $criteria->limit(null);

        $streamRepository = Shopware()->Container()->get(Repository::class);
        $streamRepository->prepareCriteria($criteria, $streamId);

        return $criteria;
    }

    private function getStreamIdOfCategory(int $categoryId): ?int
    {
        $streamId = $this->db->fetchOne('SELECT `stream_id` FROM `s_categories` WHERE id = ?', [$categoryId]);

        if (empty($streamId)) {
            return null;
        }

        return (int) $streamId;
    }

    private function buildNavigation(
        ProductNumberSearchResult $searchResult,
        string $orderNumber,
        int $categoryId,
        ShopContextInterface $context
    ): array {
        $products = array_values($searchResult->getProducts());

        if (empty($products)) {
            return [];
        }

        foreach ($products as $index => $currentProduct) {
            if ($currentProduct->getNumber() != $orderNumber) {
                continue;
            }

            $previousProduct = isset($products[$index - 1]) ? $products[$index - 1] : null;
            $nextProduct = isset($products[$index + 1]) ? $products[$index + 1] : null;

            $navigation = [];

            if ($previousProduct && $previousListProduct = $this->listProductService->get($previousProduct->getNumber(), $context)) {
                $navigation['previousProduct']['orderNumber'] = $previousListProduct->getNumber();
                $navigation['previousProduct']['link'] = $this->config->get('sBASEFILE') . '?sViewport=detail&sDetails=' . $previousListProduct->getId() . '&sCategory=' . $categoryId;
                $navigation['previousProduct']['name'] = $previousListProduct->getName();

                $previousCover = $previousListProduct->getCover();
                if ($previousCover) {
                    $navigation['previousProduct']['image'] = $this->legacyStructConverter->convertMediaStruct(
                        $previousCover
                    );
                }
            }

            if ($nextProduct && $nextListProduct = $this->listProductService->get($nextProduct->getNumber(), $context)) {
                $navigation['nextProduct']['orderNumber'] = $nextListProduct->getNumber();
                $navigation['nextProduct']['link'] = $this->config->get('sBASEFILE') . '?sViewport=detail&sDetails=' . $nextListProduct->getId() . '&sCategory=' . $categoryId;
                $navigation['nextProduct']['name'] = $nextListProduct->getName();

                $nextCover = $nextListProduct->getCover();
                if ($nextCover) {
                    $navigation['nextProduct']['image'] = $this->legacyStructConverter->convertMediaStruct(
                        $nextCover
                    );
                }
            }

            $navigation['currentListing']['position'] = $index + 1;
            $navigation['currentListing']['totalCount'] = $searchResult->getTotalCount();

            return $navigation;
        }

        return [];
    }

    private function buildCategoryLink(int $categoryId, Enlight_Controller_Request_RequestHttp $request): string
    {
        $params = $this->queryAliasMapper->replaceLongParams($request->getParams());

        unset(
            $params['__csrf_token'],
            $params['ordernumber'],
            $params['categoryId'],
            $params['module'],
            $params['controller'],
            $params['action']
        );

        $params = array_merge(
            $params,
            [
                'sViewport' => 'cat',
                'sCategory' => $categoryId,
            ]
        );

        $queryPrams = http_build_query($params, '', '&');

        return sprintf('%s?%s', $this->config->get('sBASEFILE'), $queryPrams);
    }

    /**
     * @param int $productId
     *
     * @return bool|int
     */
    private function getConfiguratorType($productId)
    {
        $type = $this->db->fetchOne(
            'SELECT type
             FROM s_article_configurator_sets configuratorSet
              INNER JOIN s_articles product
                ON product.configurator_set_id = configuratorSet.id
             WHERE product.id = ?',
            [$productId]
        );

        if ($type === false) {
            return false;
        }

        return (int) $type;
    }

    /**
     * @param int|string|numeric-string $value
     */
    private function getPromotionNumberByMode(string $mode, int $category, $value): string
    {
        switch ($mode) {
            case 'top':
            case 'new':
            case 'random':
                $value = $this->getRandomArticle($mode, $category);
                break;
            case 'fix':
            default:
                break;
        }

        if (!$value) {
            return '';
        }

        $checkOrderNumber = $this->connection->fetchOne(
            'SELECT `ordernumber` FROM `s_articles_details` WHERE `ordernumber` = :orderNumber',
            ['orderNumber' => $value]
        );
        if ($checkOrderNumber === false) {
            $number = $this->getOrderNumberByProductId((int) $value);
            if ($number !== '') {
                return $number;
            }
        }

        return (string) $value;
    }

    /**
     * Internal helper function to convert the image data from the database to the frontend structure.
     *
     * @param array<string, mixed>|null $image
     *
     * @return array<string, mixed>
     */
    private function getDataOfProductImage(?array $image, Album $productAlbum): array
    {
        // Initial the data array
        $imageData = [];
        $mediaService = Shopware()->Container()->get(MediaServiceInterface::class);

        if (empty($image['path'])) {
            return $imageData;
        }

        // First we get all thumbnail sizes of the product album
        $sizes = $productAlbum->getSettings()->getThumbnailSize();

        $highDpiThumbnails = $productAlbum->getSettings()->isThumbnailHighDpi();

        // If no extension is configured, shopware use jpg as default extension
        if (empty($image['extension'])) {
            $image['extension'] = 'jpg';
        }

        $imageData['src']['original'] = $mediaService->getUrl($image['media']['path']);
        $imageData['res']['original']['width'] = $image['width'];
        $imageData['res']['original']['height'] = $image['height'];
        $imageData['res']['description'] = $image['description'];
        $imageData['position'] = $image['position'];
        $imageData['extension'] = $image['extension'];
        $imageData['main'] = $image['main'];
        $imageData['id'] = $image['id'];
        $imageData['parentId'] = $image['parentId'];

        // Attributes as array as they come from non configurator products
        if (!empty($image['attribute'])) {
            unset($image['attribute']['id'], $image['attribute']['articleImageId']);
            $imageData['attribute'] = $image['attribute'];
        } else {
            $imageData['attribute'] = [];
        }

        // Attributes as keys as they come from configurator products
        if (!empty($image['attribute1'])) {
            $imageData['attribute']['attribute1'] = $image['attribute1'];
        }

        if (!empty($image['attribute2'])) {
            $imageData['attribute']['attribute2'] = $image['attribute2'];
        }

        if (!empty($image['attribute3'])) {
            $imageData['attribute']['attribute3'] = $image['attribute3'];
        }

        foreach ($sizes as $key => $size) {
            if (str_starts_with($size, 'x')) {
                $size = sprintf('%sx%s', $size, $size);
            }

            if ($image['type'] === Media::TYPE_IMAGE || $image['media']['type'] === Media::TYPE_IMAGE) {
                $imageData['src'][$key] = $mediaService->getUrl('media/image/thumbnail/' . $image['path'] . '_' . $size . '.' . $image['extension']);

                if ($highDpiThumbnails) {
                    $imageData['srchd'][$key] = $mediaService->getUrl('media/image/thumbnail/' . $image['path'] . '_' . $size . '@2x.' . $image['extension']);
                }
            } else {
                $imageData['src'][$key] = $mediaService->getUrl($image['media']['path']);

                if ($highDpiThumbnails) {
                    $imageData['srchd'][$key] = $mediaService->getUrl($image['media']['path']);
                }
            }
        }

        $translation = $this->sGetTranslation([], $imageData['id'], 'articleimage');

        if (!empty($translation)) {
            if (!empty($translation['description'])) {
                $imageData['res']['description'] = $translation['description'];
            }
        }

        return $imageData;
    }

    /**
     * Returns a minified product which can be used for listings,
     * sliders or emotions.
     *
     * @return array<string, mixed>|false
     */
    private function getPromotion(?int $category, string $number)
    {
        $context = $this->contextService->getShopContext();

        $product = $this->listProductService->get(
            $number,
            $context
        );

        if (!$product) {
            return false;
        }

        $promotion = $this->legacyStructConverter->convertListProductStruct($product);
        if (!empty($category) && $category != $context->getShop()->getCategory()->getId()) {
            $promotion['linkDetails'] .= "&sCategory=$category";
        }

        // check if the product has a configured property set which stored in s_filter.
        // the mini product doesn't contains this data so we have to load this lazy.
        if (!$product->hasProperties()) {
            return $promotion;
        }

        $propertySet = $this->propertyService->get(
            $product,
            $context
        );

        if (!$propertySet) {
            return $promotion;
        }

        $promotion['sProperties'] = $this->legacyStructConverter->convertPropertySetStruct($propertySet);
        $promotion['filtergroupID'] = $propertySet->getId();

        return $promotion;
    }

    /**
     * Returns a listing of products. Used for the backward compatibility category listings.
     * This function calls the new shopware core and converts the result to the old listing structure.
     *
     * @return ListingArray
     */
    private function getListing(
        ?int $categoryId,
        ShopContextInterface $context,
        Enlight_Controller_Request_Request $request,
        Criteria $criteria
    ): array {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);
        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        if (\count($conditions) > 0) {
            $this->config->offsetSet('forceArticleMainImageInListing', 0);
            $searchResult = $this->searchService->search($criteria, $context);
            $this->config->offsetSet('forceArticleMainImageInListing', 1);
        } else {
            $searchResult = $this->searchService->search($criteria, $context);
        }

        $products = [];
        foreach ($searchResult->getProducts() as $productStruct) {
            $product = $this->legacyStructConverter->convertListProductStruct($productStruct);

            if (!empty($categoryId) && $categoryId != $context->getShop()->getCategory()->getId()) {
                $product['linkDetails'] .= "&sCategory=$categoryId";
            }

            $product['description_long'] = $this->sOptimizeText($product['description_long']);

            $products[(string) $product['ordernumber']] = $product;
        }

        $products = $this->listingLinkRewriteService->rewriteLinks($criteria, $products, $context);

        $pageSizes = explode('|', $this->config->get('numberArticlesToShow'));
        $pageSizes = array_map('\intval', $pageSizes);
        $sPage = (int) $request->getParam('sPage', 1);

        return [
            'sArticles' => $products,
            'criteria' => $criteria,
            'facets' => $searchResult->getFacets(),
            'sPage' => $sPage,
            'pageIndex' => $sPage,
            'pageSizes' => $pageSizes,
            'sPerPage' => $criteria->getLimit(),
            'sNumberArticles' => $searchResult->getTotalCount(),
            'shortParameters' => $this->queryAliasMapper->getQueryAliases(),
            'sTemplate' => $request->getParam('sTemplate') ? (string) $request->getParam('sTemplate') : null,
            'sSort' => (int) $request->getParam('sSort', $this->config->get('defaultListingSorting')),
        ];
    }

    /**
     * Helper function which loads a full product struct and converts the product struct
     * to the shopware 3 array structure.
     *
     * @param array<int, int> $selection
     *
     * @return array<string, mixed>
     */
    private function getLegacyProduct(Product $product, int $categoryId, array $selection): array
    {
        $data = $this->legacyStructConverter->convertProductStruct($product);
        $data['categoryID'] = $categoryId;

        if ($product->hasConfigurator()) {
            $configurator = $this->configuratorService->getProductConfigurator(
                $product,
                $this->contextService->getShopContext(),
                $selection
            );
            $convertedConfigurator = $this->legacyStructConverter->convertConfiguratorStruct($product, $configurator);
            $data = array_merge($data, $convertedConfigurator);

            $convertedConfiguratorPrice = $this->legacyStructConverter->convertConfiguratorPrice($product, $configurator);
            $data = array_merge($data, $convertedConfiguratorPrice);

            // generate additional text
            if (!empty($selection)) {
                $this->additionalTextService->buildAdditionalText($product, $this->contextService->getShopContext());
                $data['additionaltext'] = $product->getAdditional();
            }

            if ($this->config->get('forceArticleMainImageInListing')
                && $configurator->getType() !== ConfiguratorService::CONFIGURATOR_TYPE_STANDARD
                && empty($selection)
            ) {
                $data['image'] = $this->legacyStructConverter->convertMediaStruct($product->getCover());
                $data['images'] = [];
                foreach ($product->getMedia() as $image) {
                    if ($image->getId() !== $product->getCover()->getId()) {
                        $data['images'][] = $this->legacyStructConverter->convertMediaStruct($image);
                    }
                }
            }
        }

        $data = array_merge($data, $this->getLinksOfProduct($product, $categoryId, !empty($selection)));

        $data['articleName'] = $this->sOptimizeText($data['articleName']);
        $data['description_long'] = htmlspecialchars_decode($data['description_long']);

        $data['mainVariantNumber'] = $this->db->fetchOne(
            'SELECT variant.ordernumber
             FROM s_articles_details variant
             INNER JOIN s_articles product
                ON product.main_detail_id = variant.id
                AND product.id = ?',
            [$product->getId()]
        );

        $data['sDescriptionKeywords'] = $this->getDescriptionKeywords(
            $data['description_long']
        );

        $isSelectionSpecified = false;
        if (isset($data['isSelectionSpecified']) || \array_key_exists('isSelectionSpecified', $data)) {
            $isSelectionSpecified = $data['isSelectionSpecified'];
        }

        if ($isSelectionSpecified === true || !$product->hasConfigurator()) {
            $data = $this->legacyEventManager->fireArticleByIdEvents($data, $this);

            return $data;
        }

        $criteria = new Criteria();
        foreach ($selection as $groupId => $optionId) {
            $criteria->addBaseCondition(
                new VariantCondition([(int) $optionId], true, (int) $groupId)
            );
        }

        $service = Shopware()->Container()->get(VariantListingPriceServiceInterface::class);

        $result = new ProductSearchResult(
            [$product->getNumber() => $product],
            1,
            [],
            $criteria,
            $this->contextService->getShopContext()
        );

        $service->updatePrices($criteria, $result, $this->contextService->getShopContext());

        if ($product->displayFromPrice()) {
            $data['priceStartingFrom'] = $product->getListingPrice()->getCalculatedPrice();
        }

        $data['price'] = $product->getListingPrice()->getCalculatedPrice();

        return $this->legacyEventManager->fireArticleByIdEvents($data, $this);
    }

    /**
     * Creates different links for the product like `add to basket`, `add to note`, `view detail page`, ...
     *
     * @param int  $categoryId
     * @param bool $addNumber
     *
     * @return array
     */
    private function getLinksOfProduct(ListProduct $product, $categoryId, $addNumber)
    {
        $baseFile = $this->config->get('baseFile');
        $context = $this->contextService->getShopContext();

        $detail = $baseFile . '?sViewport=detail&sArticle=' . $product->getId();
        if ($categoryId) {
            $detail .= '&sCategory=' . $categoryId;
        }

        $rewrite = Shopware()->Modules()->Core()->sRewriteLink($detail, $product->getName());

        if ($addNumber) {
            $rewrite .= strpos($rewrite, '?') !== false ? '&' : '?';
            $rewrite .= 'number=' . $product->getNumber();
        }

        $basket = $baseFile . '?sViewport=basket&sAdd=' . $product->getNumber();
        $note = $baseFile . '?sViewport=note&sAdd=' . $product->getNumber();
        $friend = $baseFile . '?sViewport=tellafriend&sDetails=' . $product->getId();
        $pdf = $baseFile . '?sViewport=detail&sDetails=' . $product->getId() . '&sLanguage=' . $context->getShop()->getId() . '&sPDF=1';

        return [
            'linkBasket' => $basket,
            'linkDetails' => $detail,
            'linkDetailsRewrited' => $rewrite,
            'linkNote' => $note,
            'linkTellAFriend' => $friend,
            'linkPDF' => $pdf,
        ];
    }

    /**
     * @param string $longDescription
     *
     * @return string
     */
    private function getDescriptionKeywords($longDescription)
    {
        // sDescriptionKeywords
        $string = strip_tags(html_entity_decode($longDescription, ENT_COMPAT | ENT_HTML401, 'UTF-8'));
        $string = str_replace(',', '', $string);
        $words = preg_split('/ /', $string, -1, PREG_SPLIT_NO_EMPTY);
        if (!\is_array($words)) {
            $words = [];
        }
        $badWords = explode(',', $this->config->get('badwords'));
        $words = array_count_values(array_diff($words, $badWords));
        foreach (array_keys($words) as $word) {
            if (\strlen($word) < 2) {
                unset($words[$word]);
            }
        }
        arsort($words);

        return htmlspecialchars(
            implode(', ', \array_slice(array_keys($words), 0, 20)),
            ENT_QUOTES,
            'UTF-8',
            false
        );
    }

    /**
     * Helper function which checks the passed $selection parameter for empty.
     * If this is the case the function implements the fallback on the legacy
     * _POST access to get the configuration selection directly of the request object.
     *
     * Additionally the function removes empty array elements.
     * Array elements of the configuration selection can be empty, if the user resets the
     * different group selections.
     *
     * @param array<int|string, int|string> $selection
     *
     * @return array<int, int>
     */
    private function getCurrentSelection(array $selection): array
    {
        if (empty($selection) && $this->frontController->Request()->has('group')) {
            $selection = $this->frontController->Request()->getParam('group');
        }

        $formattedSelection = [];
        foreach ($selection as $groupId => $optionId) {
            $groupId = (int) $groupId;
            $optionId = (int) $optionId;

            if ($groupId <= 0 || $optionId <= 0) {
                continue;
            }

            $formattedSelection[$groupId] = $optionId;
        }

        return $formattedSelection;
    }

    private function getOrderNumberByProductId(int $productId): string
    {
        return (string) $this->db->fetchOne(
            'SELECT ordernumber
             FROM s_articles_details
                INNER JOIN s_articles
                  ON s_articles.main_detail_id = s_articles_details.id
             WHERE articleID = ?',
            [$productId]
        );
    }
}
