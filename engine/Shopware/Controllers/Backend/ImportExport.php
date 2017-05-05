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

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Random;
use Shopware\Models\Newsletter\Address;
use Shopware\Models\Newsletter\ContactData;
use Shopware\Models\Newsletter\Group;

/**
 * Backend Controller for the Import/Export backend module
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_ImportExport extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Entity Manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager = null;

    /**
     * Repository for the article model.
     *
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * Repository for the articleDetail model.
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $articleDetailRepository = null;

    /**
     * Repository for the category model
     *
     * @var \Shopware\Models\Category\Repository
     */
    protected $categoryRepository = null;

    /**
     * Repository for the Group model
     *
     * @var \Shopware\Models\Newsletter\Repository
     */
    protected $groupRepository = null;

    /**
     * Repository for the Address model
     *
     * @var \Shopware\Models\Newsletter\Repository
     */
    protected $addressRepository = null;

    /**
     * Repository for the ContactData model
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $contactDataRepository = null;

    /**
     * @var string path to termporary uploaded file for import
     */
    protected $uploadedFilePath;

    /**
     * Garbage-Collector
     * Deletes uploaded file
     */
    public function __destruct()
    {
        if (!empty($this->uploadedFilePath) && file_exists($this->uploadedFilePath)) {
            @unlink($this->uploadedFilePath);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'exportArticles',
            'exportInStock',
            'exportNotInStock',
            'exportPrices',
            'exportNewsletter',
            'exportCategories',
            'exportArticleImages',
            'exportOrders',
            'import',
        ];
    }

    /**
     * Helper function to get access to the category repository.
     *
     * @return \Shopware\Models\Category\Repository
     */
    public function getCategoryRepository()
    {
        if ($this->categoryRepository === null) {
            $this->categoryRepository = $this->getManager()->getRepository('Shopware\Models\Category\Category');
        }

        return $this->categoryRepository;
    }

    /**
     * Exports article-prices as CSV
     */
    public function exportArticlesAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $format = strtolower($this->Request()->getParam('format', 'csv'));

        if ($format === 'excel' || $format === 'csv' || $format === 'array') {
            $this->exportArticlesFlat($format);

            return;
        }

        if ($format === 'xml') {
            $this->exportArticleXml();

            return;
        }

        echo json_encode([
            'success' => false,
            'message' => sprintf("Exportformat '%s' invalid", $format),
        ]);
    }

    /**
     * @param array $row
     * @param array $languages
     * @param array $translationFields
     *
     * @return array
     */
    public function prepareArticleRow($row, $languages, $translationFields)
    {
        if (empty($row['configuratorsetID'])) {
            $row['mainnumber'] = '';
            $row['additionaltext'] = '';
        }

        if (!empty($row['categories'])) {
            $categorypaths = [];
            $categories = explode('|', $row['categories']);
            foreach ($categories as $category) {
                $categorypath = $this->getCategoryPath($category);

                if (!empty($categorypath)) {
                    $categorypaths[] = $categorypath;
                }
            }
            $row['categorypaths'] = implode("\r\n", $categorypaths);
        }

        if (!empty($languages)) {
            foreach ($languages as $language) {
                if (!empty($row['article_translation_' . $language])) {
                    $objectdata = unserialize($row['article_translation_' . $language]);
                } elseif (!empty($row['detail_translation_' . $language])) {
                    $objectdata = unserialize($row['detail_translation_' . $language]);
                } else {
                    continue;
                }

                if (!empty($objectdata)) {
                    foreach ($objectdata as $key => $value) {
                        if (isset($translationFields[$key])) {
                            $row[$translationFields[$key] . '_' . $language] = $value;
                        }
                    }
                }
            }

            foreach ($languages as $language) {
                unset($row['article_translation_' . $language]);
                unset($row['detail_translation_' . $language]);
            }
        }

        return $row;
    }

    /**
     * Exports Article-InStock as CSV
     */
    public function exportInStockAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $limit = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $exportVariants = (bool) $this->Request()->getParam('exportVariants', false);
        if ($exportVariants) {
            $variantsSql = '
            INNER JOIN s_articles_details d
                ON d.articleID=a.id AND d.kind <> 3
            ';
        } else {
            $variantsSql = '
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1
            ';
        }

        $sql = "
            SELECT
                d.ordernumber as ordernumber,
                d.instock as instock,
                a.name as `_name`,
                d.additionaltext as `_additionaltext`,
                s.name as `_supplier`,
                REPLACE(ROUND(p.price*(100+t.tax)/100,2),'.',',') as `_price`

            FROM s_articles a

            {$variantsSql}

            LEFT JOIN s_core_tax t
            ON t.id=a.taxID

            LEFT JOIN s_articles_supplier as s
            ON a.supplierID = s.id

            LEFT JOIN s_articles_prices p
            ON p.articledetailsID=d.id
            AND p.`from`=1
            AND p.pricegroup='EK'

            WHERE d.instock > 0

            ORDER BY a.id, d.kind, ordernumber

            LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);
        $this->sendCsv($stmt, 'export.instock.' . date('Y.m.d') . '.csv');
    }

    /**
     * Exports articles with no stock
     */
    public function exportNotInStockAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $limit = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $exportVariants = (bool) $this->Request()->getParam('exportVariants', false);
        if ($exportVariants) {
            $variantsSql = '
            INNER JOIN s_articles_details d
                ON d.articleID=a.id AND d.kind <> 3
            ';
        } else {
            $variantsSql = '
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1
            ';
        }

        $sql = "
            SELECT
            d.ordernumber as ordernumber,
            d.instock as instock,
            a.name as `_name`,
            d.additionaltext as `_additionaltext`,
            s.name as `_supplier`,
            REPLACE(ROUND(p.price*(100+t.tax)/100,2),'.',',') as `_price`
            FROM s_articles a

            {$variantsSql}

            INNER JOIN s_core_tax t
            ON t.id=a.taxID

            LEFT JOIN s_articles_supplier as s
            ON a.supplierID = s.id

            LEFT JOIN s_articles_prices p
            ON p.articledetailsID=d.id
            AND p.`from`=1
            AND p.pricegroup='EK'

            WHERE d.instock <= 0

            ORDER BY a.id, d.kind, ordernumber

            LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);
        $this->sendCsv($stmt, 'export.noinstock.' . date('Y.m.d') . '.csv');
    }

    /**
     * Exports article-prices as CSV
     */
    public function exportPricesAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $limit = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $exportVariants = (bool) $this->Request()->getParam('exportVariants', false);
        if ($exportVariants) {
            $variantsSql = '
            INNER JOIN s_articles_details d
                ON d.articleID=a.id AND d.kind <> 3
            ';
        } else {
            $variantsSql = '
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1
            ';
        }
        $sql = "
            SELECT
            d.ordernumber as ordernumber,
            IF(cg.taxinput = 0,REPLACE(ROUND(p.price,2),'.',','),REPLACE(ROUND(p.price*(100+t.tax)/100,2),'.',',')) as price,
            p.pricegroup as pricegroup,
            IF(p.`from`=1,NULL,p.`from`) as `from`,
            REPLACE(ROUND(p.pseudoprice*(100+t.tax)/100,2),'.',',') as pseudoprice,
            a.name as `_name`,
            d.additionaltext as `_additionaltext`,
            s.name as `_supplier`
            FROM s_articles a

            {$variantsSql}

            INNER JOIN s_core_tax t
            ON t.id=a.taxID

            LEFT JOIN s_articles_supplier as s
            ON a.supplierID = s.id

            LEFT JOIN s_articles_prices p
            ON p.articledetailsID=d.id

            INNER JOIN s_core_customergroups cg
            ON p.pricegroup=cg.groupkey

            ORDER BY a.id, d.kind, ordernumber, `from`

            LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);
        $this->sendCsv($stmt, 'export.prices.' . date('Y.m.d') . '.csv');
    }

    /**
     * Exports newsletter subscriptions as CSV
     */
    public function exportNewsletterAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $sql = '
            SELECT
            cm.email,
            cg.name as `group`,
            IFNULL(ub.salutation, nd.salutation) as salutation,
            IFNULL(ub.firstname, nd.firstname) as firstname,
            IFNULL(ub.lastname, nd.lastname) as lastname,
            IFNULL(ub.street, nd.street) as street,
            IFNULL(ub.zipcode, nd.zipcode) as zipcode,
            IFNULL(ub.city, nd.city) as city,
            lastmailing,
            lastread,
            u.id as userID
            FROM s_campaigns_mailaddresses cm
            LEFT JOIN s_campaigns_groups cg
            ON cg.id=cm.groupID
            LEFT JOIN s_campaigns_maildata nd
            ON nd.email=cm.email
            LEFT JOIN s_user u
            ON u.email=cm.email
            AND u.accountmode=0
            LEFT JOIN s_user_billingaddress ub
            ON ub.userID=u.id
        ';

        $stmt = Shopware()->Db()->query($sql);
        $this->sendCsv($stmt, 'export.newsletter.' . date('Y.m.d') . '.csv');
    }

    /**
     * Exports Categories
     */
    public function exportCategoriesAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $categories = $this->getCategories();

        $format = strtolower($this->Request()->getParam('format', 'csv'));

        if ($format === 'excel') {
            $this->Response()->setHeader('Content-Type', 'application/vnd.ms-excel;charset=UTF-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.' . date('Y.m.d') . '.xls"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $excel = new Shopware_Components_Convert_Excel();
            $excel->setTitle('Categories Export');
            $excel->addRow(array_keys(reset($categories)));
            $excel->addArray($categories);
            echo $excel->getAll();
        }

        if ($format === 'xml') {
            $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.' . date('Y.m.d') . '.xml"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $convert = new Shopware_Components_Convert_Xml();
            $xmlmap = ['shopware' => ['categories' => ['category' => $categories]]];
            echo $convert->encode($xmlmap);
        }

        if ($format === 'csv') {
            $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.' . date('Y.m.d') . '.csv"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $convert = new Shopware_Components_Convert_Csv();
            $convert->sSettings['newline'] = "\r\n";
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            echo $convert->encode($categories);
        }
    }

    /**
     * Exports Article-InStock as CSV
     */
    public function exportArticleImagesAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $path = $this->Request()->getScheme() . '://' . $this->Request()->getHttpHost() . $this->Request()->getBasePath() . '/media/image/';

        $sqlDetail = "
            SELECT
                d.ordernumber,
                CONCAT('$path', ai.img,'.',ai.extension) as image,
                ai.main,
                ai.description,
                ai.position,
                ai.width,
                ai.height,
                GROUP_CONCAT(CONCAT(im.id, '|', mr.option_id, '|' , co.name, '|', cg.name)) as relations

            FROM s_articles_img ai
            INNER JOIN s_articles a ON ai.articleId = a.id
            INNER JOIN s_articles_details d ON a.main_detail_id = d.id

            LEFT JOIN s_article_img_mappings im ON im.image_id = ai.id
            LEFT JOIN s_article_img_mapping_rules mr ON mr.mapping_id = im.id
            LEFT JOIN s_article_configurator_options co ON mr.option_id = co.id
            LEFT JOIN s_article_configurator_groups cg ON co.group_id = cg.id

            WHERE ai.parent_id is NULL
            AND ai.article_detail_id IS NULL
            GROUP BY ai.id
            ORDER BY mr.mapping_id
        ";

        $stmt = Shopware()->Db()->query($sqlDetail);
        $result = $stmt->fetchAll();

        foreach ($result as &$image) {
            if (empty($image['relations'])) {
                continue;
            }

            $relations = explode(',', $image['relations']);

            $out = [];
            foreach ($relations as $rule) {
                $split = explode('|', $rule);
                $ruleId = $split[0];
                $optionId = $split[1];
                $name = $split[2];
                $groupName = $split[3];

                $out[$ruleId][] = "$groupName:$name";
            }

            $relations = '';
            foreach ($out as $group) {
                $name = $group['name'];
                $relations .= '&{' . implode(',', $group) . '}';
            }

            $image['relations'] = $relations;
        }

        $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.images.' . date('Y.m.d') . '.csv"');
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $convert = new Shopware_Components_Convert_Csv();
        $convert->sSettings['newline'] = "\r\n";
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $convert->encode($result);
    }

    /**
     * Export orders
     */
    public function exportOrdersAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $format = strtolower($this->Request()->getParam('format', 'csv'));

        /** @var \Shopware\Models\Order\Repository $repository */
        $repository = $this->getManager()->getRepository('Shopware\Models\Order\Order');

        /** @var \Shopware\Components\Model\ModelRepository $currencyRepostiroy */
        $currencyRepository = $this->getManager()->getRepository('Shopware\Models\Shop\Currency');

        $builder = $repository->createQueryBuilder('orders');

        $orderState = $this->Request()->getParam('orderstate');
        if (is_numeric($orderState)) {
            $builder->andWhere('orders.status = :orderstate');
            $builder->setParameter('orderstate', $orderState);
        }

        $paymentState = $this->Request()->getParam('paymentstate');
        if (is_numeric($paymentState)) {
            $builder->andWhere('orders.cleared = :paymentstate');
            $builder->setParameter('paymentstate', $paymentState);
        }

        $orderNumberFrom = $this->Request()->getParam('ordernumberFrom');
        if (is_numeric($orderNumberFrom)) {
            $builder->andWhere('orders.number > :orderNumberFrom');
            $builder->setParameter('orderNumberFrom', $orderNumberFrom);
        }

        $dateFrom = $this->Request()->getParam('dateFrom');
        if ($dateFrom) {
            $dateFrom = new \DateTime($dateFrom);
            $builder->andWhere('orders.orderTime >= :dateFrom');
            $builder->setParameter('dateFrom', $dateFrom);
        }

        $dateTo = $this->Request()->getParam('dateTo');
        if ($dateTo) {
            $dateTo = new Zend_Date($dateTo);
            $dateTo->setHour('23');
            $dateTo->setMinute('59');
            $dateTo->setSecond('59');

            $builder->andWhere('orders.orderTime <= :dateTo');
            $builder->setParameter('dateTo', $dateTo->get('yyyy-MM-dd HH:mm:ss'));
        }

        if ($format == 'xml') {
            $selectFields = [
                'orders',
                'details',
                'documents',
                'payment',
                'customer',
                'shipping',
                'billing',
                'billingCountry',
                'shippingCountry',
                'shop',
                'dispatch',
                'paymentStatus',
                'orderStatus',
                'documentType',
            ];
        }

        if ($format == 'csv' || $format == 'excel') {
            $selectFields = [
                'orders.id as orderId',
                'orders.number as ordernumber',
                'orders.orderTime as ordertime',
                'orders.customerId as customerID',
                'orders.paymentId as paymentID',
                'orders.transactionId as transactionID',
                'orders.partnerId as partnerID',
                'orders.cleared as clearedID',
                'orders.status as statusID',

                'dispatch.id as dispatchID',
                'orders.shopId as subshopID',

                'orders.invoiceAmount as invoice_amount',
                'orders.invoiceAmountNet as invoice_amount_net',

                'orders.invoiceShipping as invoice_shipping',
                'orders.invoiceShippingNet as invoice_shipping_net',
                'orders.net as netto',

                'paymentStatus.description as cleared_description',
                'orderStatus.description as status_description',

                'payment.description as payment_description',
                'dispatch.description as dispatch_description',

                // dummy for currency_description
                'orders.id as currency_description',

                'orders.referer as referer',
                'orders.clearedDate as cleareddate',
                'orders.trackingCode as trackingcode',
                'orders.languageIso as language',
                'orders.currency as currency',
                'orders.currencyFactor as currencyFactor',

                // dummy for count positions
                'orders.id as count_positions',

                'details.id as orderdetailsID',
                'details.articleId as articleID',
                'details.articleNumber as articleordernumber',
                'details.articleName as name',
                'details.price as price',
                'details.quantity as quantity',
                'details.ean as ean',
                'details.unit as unit',
                'details.packUnit as packUnit',

                'details.price * details.quantity as invoice',

                'details.releaseDate as releasedate',
                'taxes.tax as tax',
                'details.esdArticle as esd',
                'details.mode as modus',

                'customer.number as customernumber',

                'billing.company as billing_company',
                'billing.department as billing_department',
                'billing.salutation as billing_salutation',
                'billing.firstName as billing_firstname',
                'billing.lastName as billing_lastname',
                'billing.street as billing_street',
                'billing.zipCode as billing_zipcode',
                'billing.city as billing_city',
                'billingCountry.name as billing_country',
                'billingCountry.isoName as billing_countryen',
                'billingCountry.iso as billing_countryiso',

                'shipping.company as shipping_company',
                'shipping.department as shipping_department',
                'shipping.salutation as shipping_salutation',
                'shipping.firstName as shipping_firstname',
                'shipping.lastName as shipping_lastname',
                'shipping.street as shipping_street',
                'shipping.zipCode as shipping_zipcode',
                'shipping.city as shipping_city',
                'shippingCountry.name as shipping_country',
                'shippingCountry.isoName as shipping_countryen',
                'shippingCountry.iso as shipping_countryiso',

                'billing.vatId as ustid',
                'billing.phone as phone',
                'customer.email as email',
                'customer.groupKey as customergroup',
                'customer.newsletter as newsletter',
                'customer.affiliate as affiliate',
            ];
            $builder->addGroupBy('orderdetailsID');
        }

        $builder->select($selectFields);

        $builder->leftJoin('orders.details', 'details')
                ->leftJoin('details.tax', 'taxes')
                ->leftJoin('orders.documents', 'documents')
                ->leftJoin('documents.type', 'documentType')
                ->leftJoin('orders.payment', 'payment')
                ->leftJoin('orders.paymentStatus', 'paymentStatus')
                ->leftJoin('orders.orderStatus', 'orderStatus')
                ->leftJoin('orders.customer', 'customer')
                ->leftJoin('customer.billing', 'customerBilling')
                ->leftJoin('orders.billing', 'billing')
                ->leftJoin('billing.country', 'billingCountry')
                ->leftJoin('orders.shipping', 'shipping')
                ->leftJoin('orders.shop', 'shop')
                ->leftJoin('orders.dispatch', 'dispatch')
                ->leftJoin('shipping.country', 'shippingCountry');

        $builder->addOrderBy('orders.orderTime');

        $query = $builder->getQuery();
        $result = $query->getArrayResult();

        $updateStateId = $this->Request()->getParam('updateOrderstate');
        if (!empty($updateStateId)) {
            $orderIds = [];

            if ($format == 'csv' || $format == 'excel') {
                foreach ($result as $item) {
                    $orderIds[] = $item['orderId'];
                }

                $orderIds = array_unique($orderIds);
            }

            if ($format == 'xml') {
                foreach ($result as $item) {
                    $orderIds[] = $item['id'];
                }
            }
            $this->updateOrderStatus($orderIds, $updateStateId);
        }

        if ($format == 'csv' || $format == 'excel') {
            $builder = $repository->createQueryBuilder('orders');
            $builder->select([
                'count(details.id) as count_positions',
            ]);

            $builder->leftJoin('orders.details', 'details')
                    ->andWhere('details.orderId = :orderId')
                    ->groupBy('orders.id');

            foreach ($result as &$item) {
                $builder->setParameter('orderId', $item['orderId']);
                try {
                    $item['count_positions'] = $builder->getQuery()->getSingleScalarResult();
                } catch (\Exception $e) {
                    $item['count_positions'] = 0;
                }

                $currencyModel = $currencyRepository->findOneBy(['currency' => $item['currency']]);
                if ($currencyModel) {
                    $item['currency_description'] = $currencyModel->getName();
                } else {
                    $item['currency_description'] = '';
                }

                // Format tax
                $item['tax'] = (float) $item['tax'];
            }

            array_walk_recursive($result, function (&$value) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
            });

            if ($format === 'excel') {
                $this->Response()->setHeader('Content-Type', 'application/vnd.ms-excel;charset=UTF-8');
                $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.orders.' . date('Y.m.d') . '.xls"');
                $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

                $excel = new Shopware_Components_Convert_Excel();
                $excel->setTitle('Orders Export');
                $excel->addRow(array_keys(reset($result)));
                $excel->addArray($result);
                echo $excel->getAll();
            }

            if ($format == 'csv') {
                $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
                $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.orders.' . date('Y.m.d') . '.csv"');
                $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

                $convert = new Shopware_Components_Convert_Csv();
                $convert->sSettings['newline'] = "\r\n";
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                echo $convert->encode($result);
            }
        }

        if ($format == 'xml') {
            array_walk_recursive($result, function (&$value) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
            });

            $orders = ['shopware' => ['orders' => ['order' => $result]]];
            $convert = new Shopware_Components_Convert_Xml();

            $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.orders.' . date('Y.m.d') . '.xml"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            echo $convert->encode($orders);
        }
    }

    /**
     * Import snippet action
     */
    public function importAction()
    {
        try {
            @set_time_limit(0);
            $this->Front()->Plugins()->Json()->setRenderer(false);

            $type = strtolower(trim($this->Request()->getParam('type')));

            if (!$type) {
                echo json_encode([
                    'success' => false,
                    'message' => "No Importtype given. This might result from 'post_max_size' not being big enough in your php.ini.",
                ]);

                return;
            }

            if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Could not upload file',
                ]);

                return;
            }

            $fileName = basename($_FILES['file']['name']);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($extension, ['csv', 'xml'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unknown Extension',
                ]);

                return;
            }

            $destPath = Shopware()->DocPath('media_' . 'temp');
            if (!is_dir($destPath)) {
                // Try to create directory with write permissions
                mkdir($destPath, 0777, true);
            }

            $destPath = realpath($destPath);
            if (!file_exists($destPath)) {
                echo json_encode([
                    'success' => false,
                    'message' => sprintf("Destination directory '%s' does not exist.", $destPath),
                ]);

                return;
            }

            if (!is_writable($destPath)) {
                echo json_encode([
                    'success' => false,
                    'message' => sprintf("Destination directory '%s' does not have write permissions.", $destPath),
                ]);

                return;
            }

            $filePath = tempnam($destPath, 'import_');

            if (false === move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                echo json_encode([
                    'success' => false,
                    'message' => sprintf('Could not move %s to %s.', $_FILES['file']['tmp_name'], $filePath),
                ]);

                return;
            }
            $this->uploadedFilePath = $filePath;
            chmod($filePath, 0644);

            if ($type === 'instock') {
                $this->importInStock($filePath);

                return;
            }

            if ($type === 'prices') {
                $this->importPrices($filePath);

                return;
            }

            if ($type === 'categories') {
                $this->importCategories($filePath, $extension);

                return;
            }

            if ($type === 'images') {
                $this->importImages($filePath);

                return;
            }

            if ($type === 'newsletter') {
                $this->importNewsletter($filePath);

                return;
            }

            if ($type === 'articles') {
                if ($extension === 'csv') {
                    $this->importArticlesCsv($filePath);

                    return;
                }

                if ($extension === 'xml') {
                    $this->importArticlesXml($filePath);

                    return;
                }
            }

            echo json_encode([
                'success' => false,
                'message' => sprintf('Could not handle upload of type: %s.', $type),
            ]);

            return;
        } catch (\Exception $e) {
            // At this point any Exception would result in the import/export frontend "loading forever"
            // Append stack trace in order to be able to debug
            $message = $e->getMessage() . "<br />\r\nStack Trace:" . $e->getTraceAsString();
            echo json_encode([
                'success' => false,
                'message' => $message,
            ]);

            return;
        }
    }

    /**
     * @param $filePath
     */
    public function importImages($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $counter = 0;
        $total = 0;
        $errors = [];

        $articleDetailRepository = $this->getArticleDetailRepository();

        $configuratorGroupRepository = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Group');
        $configuratorOptionRepository = $this->getManager()->getRepository('Shopware\Models\Article\Configurator\Option');

        $recreateImagesLater = [];

        foreach ($results as $imageData) {
            if (!empty($imageData['relations'])) {
                $relations = [];
                $results = explode('&', $imageData['relations']);
                foreach ($results as $result) {
                    if ($result !== '') {
                        $result = preg_replace('/{|}/', '', $result);
                        list($group, $option) = explode(':', $result);

                        // Try to get given configurator group/option. Continue, if they don't exist
                        $cGroupModel = $configuratorGroupRepository->findOneBy(['name' => $group]);
                        if ($cGroupModel === null) {
                            continue;
                        }
                        $cOptionModel = $configuratorOptionRepository->findOneBy(
                            ['name' => $option,
                                 'groupId' => $cGroupModel->getId(),
                            ]
                        );
                        if ($cOptionModel === null) {
                            continue;
                        }
                        $relations[] = ['group' => $cGroupModel, 'option' => $cOptionModel];
                    }
                }
            }

            if (empty($imageData['ordernumber']) || empty($imageData['image'])) {
                continue;
            }
            ++$counter;

            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepository->findOneBy(['number' => $imageData['ordernumber']]);
            if (!$articleDetailModel) {
                continue;
            }

            /** @var \Shopware\Models\Article\Article $article */
            $article = $articleDetailModel->getArticle();

            $image = new \Shopware\Models\Article\Image();

            try {
                $name = pathinfo($imageData['image'], PATHINFO_FILENAME);
                $path = $this->load($imageData['image'], $name);
            } catch (\Exception $e) {
                $errors[] = sprintf("Could not load image {$imageData['image']}: %s", $e->getMessage());
                continue;
            }

            $file = new \Symfony\Component\HttpFoundation\File\File($path);

            $media = new \Shopware\Models\Media\Media();
            $media->setAlbumId(-1);
            $media->setAlbum($this->getManager()->find('Shopware\Models\Media\Album', -1));

            $media->setFile($file);
            $media->setName(pathinfo($imageData['image'], PATHINFO_FILENAME));
            $media->setDescription('');
            $media->setCreated(new \DateTime());
            $media->setUserId(0);

            try { //persist the model into the model manager
                $this->getManager()->persist($media);
                $this->getManager()->flush();
            } catch (\Doctrine\ORM\ORMException $e) {
                $errors[] = sprintf('Could not move image: %s', $e->getMessage());
                continue;
            }

            if (empty($imageData['main'])) {
                $imageData['main'] = 1;
            }

            //generate thumbnails
            if ($media->getType() == \Shopware\Models\Media\Media::TYPE_IMAGE) {
                /** @var $manager \Shopware\Components\Thumbnail\Manager */
                $manager = Shopware()->Container()->get('thumbnail_manager');
                $manager->createMediaThumbnail($media, [], true);
            }

            $image->setArticle($article);
            $image->setDescription($imageData['description']);
            $image->setPosition($imageData['position']);
            $image->setPath($media->getName());
            $image->setExtension($media->getExtension());
            $image->setMedia($media);
            $image->setMain($imageData['main']);
            $this->getManager()->persist($image);
            $this->getManager()->flush($image);

            // Set mappings
            if (!empty($relations)) {
                foreach ($relations as $relation) {
                    $optionModel = $relation['option'];
                    Shopware()->Db()->insert('s_article_img_mappings', [
                        'image_id' => $image->getId(),
                    ]);
                    $mappingID = Shopware()->Db()->lastInsertId();
                    Shopware()->Db()->insert('s_article_img_mapping_rules', [
                        'mapping_id' => $mappingID,
                        'option_id' => $optionModel->getId(),
                    ]);
                }

                $recreateImagesLater[] = $article->getId();
            }

            // Prevent multiple images from being a preview
            if ((int) $imageData['main'] === 1) {
                Shopware()->Db()->update('s_articles_img',
                    [
                        'main' => 2,
                    ],
                    [
                        'articleID = ?' => $article->getId(),
                        'id <> ?' => $image->getId(),
                    ]
                );
            }

            ++$total;
        }

        try {
            // Clear the entity manager and rebuild images in order to get proper variant images
            Shopware()->Models()->clear();
            foreach ($recreateImagesLater as $articleId) {
                $this->recreateVariantImages($articleId);
            }
        } catch (\Exception $e) {
            $errors[] = sprintf('Error building variant images. If no other errors occurred, the images have been
                uploaded but the image-variant mapping in the shop frontend might fail. Errormessage: %s', $e->getMessage());
        }

        if (!empty($errors)) {
            $errors = $this->toUtf8($errors);
            $message = implode("<br>\n", $errors);
            echo json_encode([
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ]);

            return;
        }

        echo json_encode([
            'success' => true,
            'message' => sprintf('Successfully uploaded %s of %s Images', $total, $counter),
        ]);
    }

    /**
     * @param $filePath
     * @param $extension
     */
    public function importCategories($filePath, $extension)
    {
        if ($extension === 'xml') {
            $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
            $results = $xml->categories->category;
        }

        if ($extension === 'csv') {
            $results = new Shopware_Components_CsvIterator($filePath, ';');
        }

        /** @var \Shopware\Models\Category\Repository $categoryRepository */
        $categoryRepository = $this->getManager()->getRepository('Shopware\Models\Category\Category');
        $metaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Category\Category');
        $metaData->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $counter = 0;
        $total = 0;
        $categoryIds = [];

        $this->getManager()->clear();

        $this->getManager()->getConnection()->beginTransaction(); // suspend auto-commit
        try {
            foreach ($results as $category) {
                ++$total;
                $category = (array) $category;

                if (empty($category['parentID']) || empty($category['categoryID']) || empty($category['description'])) {
                    continue;
                }

                $categoryModel = $this->saveCategory($category, $categoryRepository, $metaData);
                $this->getManager()->flush();
                $this->getManager()->clear();
                if ($categoryModel) {
                    ++$counter;
                    $categoryIds[] = $categoryModel->getId();
                }
            }

            $this->getManager()->getConnection()->commit();
            $this->getManager()->clear();
        } catch (\Exception $e) {
            $this->getManager()->getConnection()->rollBack();
            $this->getManager()->close();
            echo json_encode([
                'success' => false,
                'message' => sprintf("Error in line {$counter}: %s", $e->getMessage()),
            ]);

            return;
        }

        echo json_encode([
            'success' => true,
            'message' => sprintf('Successfully imported %s of %s categories', $counter, $total),
        ]);
    }

    /**
     * @param array                                $category
     * @param \Shopware\Models\Category\Repository $categoryRepository
     * @param $metaData
     *
     * @return Shopware\Models\Category\Category
     */
    public function saveCategory($category, \Shopware\Models\Category\Repository $categoryRepository, $metaData)
    {
        $parent = $categoryRepository->find($category['parentID']);
        if (!$parent) {
            throw new \Exception(sprintf('Could not update/insert category with id %s, could not find parentId %s', $category['categoryID'], $category['parentID']));
        }

        $category = $this->toUtf8($category);

        $mapping = [];
        foreach ($metaData->fieldMappings as $fieldMapping) {
            $mapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $mapping = $mapping + [
            'categoryID' => 'id',
            'ac_attr1' => 'attribute_attribute1',
            'ac_attr2' => 'attribute_attribute2',
            'ac_attr3' => 'attribute_attribute3',
            'ac_attr4' => 'attribute_attribute4',
            'ac_attr5' => 'attribute_attribute5',
            'ac_attr6' => 'attribute_attribute6',
        ];

        $updateData = $this->mapFields($category, $mapping);
        $updateData['parent'] = $parent;

        $attribute = $this->prefixToArray($updateData, 'attribute_');
        if (!empty($attribute)) {
            $updateData['attribute'] = $attribute;
        }

        /** @var $categoryModel \Shopware\Models\Category\Category */
        $categoryModel = $categoryRepository->find($category['categoryID']);
        if (!$categoryModel) {
            $categoryModel = new \Shopware\Models\Category\Category();
            $categoryModel->setPrimaryIdentifier($category['categoryID']);
            $this->getManager()->persist($categoryModel);
        }

        $categoryModel->fromArray($updateData);

        return $categoryModel;
    }

    /**
     * @param $filePath
     */
    public function importNewsletter($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $insertCount = 0;
        $updateCount = 0;

        $errors = [];

        $emailValidator = $this->container->get('validator.email');
        foreach ($results as $newsletterData) {
            if (empty($newsletterData['email'])) {
                $errors[] = 'Empty email field';
                continue;
            }

            if (!$emailValidator->isValid($newsletterData['email'])) {
                $errors[] = 'Invalid email address: ' . $newsletterData['email'];
                continue;
            }

            // Set newsletter recipient/group
            $group = null;
            if ($newsletterData['group']) {
                $group = $this->getGroupRepository()->findOneByName($newsletterData['group']);
            }
            if (!$group && $newsletterData['group']) {
                $group = new Group();
                $group->setName($newsletterData['group']);
                $this->getManager()->persist($group);
            } elseif (!$group && $groupId = Shopware()->Config()->get('sNEWSLETTERDEFAULTGROUP')) {
                $group = $this->getGroupRepository()->findOneBy($groupId);
            } elseif (!$group) {
                // If no group is specified and no default config exists, don't import the address
                // This should never actually happen, as a default should always exist
                // but its better to be safe than sorry
                continue;
            }

            //Create/Update the Address entry
            $recipient = $this->getAddressRepository()->findOneByEmail($newsletterData['email']) ?: new Address();
            if ($recipient->getId()) {
                ++$updateCount;
            } else {
                ++$insertCount;
            }
            $recipient->setEmail($newsletterData['email']);
            $recipient->setIsCustomer(!empty($newsletterData['userID']));

            //Only set the group if it was explicitly provided or it's a new entry
            if ($group && ($newsletterData['group'] || !$recipient->getId())) {
                $recipient->setNewsletterGroup($group);
            }
            $this->getManager()->persist($recipient);
            $this->getManager()->flush();

            //Create/Update the ContactData entry
            $contactData = $this->getContactDataRepository()->findOneByEmail($newsletterData['email']) ?: new ContactData();
            //sanitize to avoid setting fields the user's not supposed to access
            unset($newsletterData['added']);
            unset($newsletterData['deleted']);
            $contactData->fromArray($newsletterData);

            //Only set the group if it was explicitly provided or it's a new entry
            if ($group && ($newsletterData['group'] || !$contactData->getId())) {
                $contactData->setGroupId($group->getId());
            }
            $contactData->setAdded(new \DateTime());

            $this->getManager()->persist($contactData);
            $this->getManager()->flush();
        }

        if (!empty($errors)) {
            $message = implode("<br>\n", $errors);
            echo json_encode([
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ]);

            return;
        }

        echo json_encode([
            'success' => true,
            'message' => sprintf('Imported: %s. Updated: %s.', $insertCount, $updateCount),
        ]);
    }

    /**
     * @param $filePath
     */
    public function importInStock($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $counter = 0;
        $total = 0;

        foreach ($results as $articleData) {
            if (empty($articleData['ordernumber'])) {
                continue;
            }
            ++$counter;

            $result = Shopware()->Db()->update(
                's_articles_details',
                ['instock' => (int) $articleData['instock']],
                ['ordernumber = ?' => $articleData['ordernumber']]
            );

            $total += $result;
        }

        echo json_encode([
            'success' => true,
            'message' => sprintf('Successfully updated %s of %s artticles', $total, $counter),
        ]);
    }

    /**
     * @param string $filePath
     */
    public function importPrices($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $counter = 0;
        $total = 0;

        $sql = 'SELECT `groupkey` as `key`, `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC';
        $customergroups = Shopware()->Db()->fetchAssoc($sql);

        foreach ($results as $articleData) {
            if (empty($articleData['ordernumber'])) {
                continue;
            }

            ++$counter;

            $sql = 'SELECT id as detailId, ordernumber, articleId FROM s_articles_details WHERE ordernumber = ?';
            $article = Shopware()->Db()->fetchRow($sql, $articleData['ordernumber']);

            if (!$article) {
                continue;
            }

            $sql = '
                SELECT articleID, t.tax
                FROM s_articles_details ad
                JOIN s_articles a
                ON a.id = ad.articleID
                JOIN s_core_tax t
                ON t.id = a.taxID
                WHERE ordernumber LIKE ?
            ';
            $stmt = Shopware()->Db()->query($sql, $articleData['ordernumber']);
            $result = $stmt->fetch();

            $tax = $result['tax'];

            if (empty($articleData['pricegroup'])) {
                $articleData['pricegroup'] = 'EK';
            }

            $articleData['price'] = floatval(str_replace(',', '.', $articleData['price']));
            $articleData['pseudoprice'] = floatval(str_replace(',', '.', $articleData['pseudoprice']));

            if (!empty($customergroups[$articleData['pricegroup']]['taxinput'])) {
                $articleData['price'] = $articleData['price'] / (100 + $tax) * 100;

                if (isset($articleData['pseudoprice'])) {
                    $articleData['pseudoprice'] = $articleData['pseudoprice'] / (100 + $tax) * 100;
                } else {
                    $articleData['pseudoprice'] = 0;
                }
            }

            if (isset($articleData['percent'])) {
                $articleData['percent'] = $this->sValFloat($articleData['percent']);
            } else {
                $articleData['percent'] = 0;
            }

            if (empty($articleData['from'])) {
                $articleData['from'] = 1;
            } else {
                $articleData['from'] = intval($articleData['from']);
            }

            if (empty($articleData['price']) && empty($articleData['percent'])) {
                continue;
            }

            if ($articleData['from'] <= 1 && empty($articleData['price'])) {
                continue;
            }

            Shopware()->Db()->delete('s_articles_prices', [
                'pricegroup = ?' => $articleData['pricegroup'],
                'articledetailsID = ?' => $article['detailId'],
                'CAST(`from` AS UNSIGNED) >= ?' => $articleData['from'],
            ]);

            if ($articleData['from'] != 1) {
                Shopware()->Db()->update(
                    's_articles_prices',
                    ['to' => $articleData['from'] - 1],
                    [
                        'pricegroup = ?' => $articleData['pricegroup'],
                        'articleId = ?' => $article['articleId'],
                        'articledetailsID = ?' => $article['detailId'],
                        '`to` LIKE ?' => 'beliebig',
                    ]
                );
            }

            Shopware()->Db()->insert('s_articles_prices', [
                'articleID' => $article['articleId'],
                'articledetailsID' => $article['detailId'],
                'pricegroup' => $articleData['pricegroup'],
                'from' => $articleData['from'],
                'to' => 'beliebig',
                'price' => $articleData['price'],
                'pseudoprice' => $articleData['pseudoprice'],
                'percent' => $articleData['percent'],
            ]);
            ++$total;
        }

        echo json_encode([
            'success' => true,
            'message' => sprintf('Successfully updated %s of %s artticles', $total, $counter),
        ]);
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $limit = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        // ("SHOW COLUMNS FROM $table");
        $stmt = Shopware()->Db()->query('SELECT * FROM s_categories_attributes LIMIT 1');
        $attributes = $stmt->fetch();

        $attributesSelect = '';
        if ($attributes) {
            unset($attributes['id']);
            unset($attributes['categoryID']);
            $attributes = array_keys($attributes);

            $prefix = 'attr';
            $attributesSelect = [];
            foreach ($attributes as $attribute) {
                $attributesSelect[] = sprintf('%s.%s as attribute_%s', $prefix, $attribute, $attribute);
            }

            $attributesSelect = ",\n" . implode(",\n", $attributesSelect);
        }

        $sql = "
            SELECT
                c.id as categoryID,
                c.parent as parentID,
                c.description,
                c.position,
                c.metakeywords,
                c.metadescription,
                c.cmsheadline,
                c.cmstext,
                c.template,
                c.active,
                c.blog,
                c.external,
                c.hidefilter
                $attributesSelect
            FROM s_categories c
            LEFT JOIN s_categories_attributes attr
                ON attr.categoryID = c.id
            WHERE c.id != 1
            ORDER BY c.parent, c.position

             LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * @param Zend_Db_Statement_Interface $stmt
     * @param string                      $filename
     */
    public function sendCsv(Zend_Db_Statement_Interface $stmt, $filename)
    {
        $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $convert = new Shopware_Components_Convert_Csv();
        $first = true;
        $keys = [];
        while ($row = $stmt->fetch()) {
            if ($first) {
                $first = false;
                $keys = array_keys($row);
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                echo $convert->_encode_line(array_combine($keys, $keys), $keys) . "\r\n";
            }
            echo $convert->_encode_line($row, $keys) . "\r\n";
        }
    }

    /**
     * @param Zend_Db_Statement_Interface $stmt
     * @param string                      $filename
     * @param string                      $title
     */
    public function sendExcel($stmt, $filename, $title)
    {
        $this->Response()->setHeader('Content-Type', 'application/vnd.ms-excel;charset=UTF-8');
        $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $excel = new Shopware_Components_Convert_Excel();
        $excel->setTitle($title);

        $first = true;
        echo $excel->getHeader();
        while ($row = $stmt->fetch()) {
            if ($first) {
                $first = false;
                echo $excel->encodeRow(array_keys($row));
            }

            echo $excel->encodeRow($row);
        }

        echo $excel->getFooter();
    }

    /**
     * Replace dot with comma as decimal point
     *
     * @param string $value
     *
     * @return float
     */
    public function sValFloat($value)
    {
        return floatval(str_replace(',', '.', $value));
    }

    /**
     * @param $input
     *
     * @return array
     */
    public function prepareImportXmlData($input)
    {
        $isIndexed = array_values($input) === $input;

        if ($isIndexed) {
            return $input;
        }

        return [$input];
    }

    /**
     * @param SimpleXMLElement
     *
     * @return array|string
     */
    public function simplexml2array($xml)
    {
        if (get_class($xml) == 'SimpleXMLElement') {
            $attributes = $xml->attributes();
            foreach ($attributes as $k => $v) {
                if ($v) {
                    $a[$k] = (string) $v;
                }
            }
            $x = $xml;
            $xml = get_object_vars($xml);
        }
        if (is_array($xml)) {
            if (count($xml) == 0) {
                return (string) $x;
            } // for CDATA
            foreach ($xml as $key => $value) {
                $r[$key] = $this->simplexml2array($value);
            }
            if (isset($a)) {
                $r['@attributes'] = $a;
            }    // Attributes
            return $r;
        }

        return (string) $xml;
    }

    /**
     * Returns the category path for the given category id
     *
     * @param int $id
     *
     * @return string
     */
    public function getCategoryPath($id)
    {
        $repository = $this->getManager()->getRepository('Shopware\Models\Category\Category');

        $path = $repository->getPathById($id, 'name');
        unset($path[0]);
        $path = implode($path, '|');

        return $path;
    }

    /**
     * @param string $categorypaths
     *
     * @return array
     */
    public function createCategoriesByCategoryPaths($categorypaths)
    {
        $categoryIds = [];
        $categorypaths = explode("\n", $categorypaths);

        foreach ($categorypaths as $categorypath) {
            $categorypath = trim($categorypath);
            if (empty($categorypath)) {
                continue;
            }

            $categories = explode('|', $categorypath);
            $categoryId = 1;
            foreach ($categories as $categoryName) {
                $categoryName = trim($categoryName);
                if (empty($categoryName)) {
                    break;
                }

                $categoryModel = $this->getCategoryRepository()->findOneBy(['name' => $categoryName, 'parentId' => $categoryId]);
                if (!$categoryModel) {
                    $parent = $this->getCategoryRepository()->find($categoryId);
                    if (!$parent) {
                        throw new \Exception(sprintf('Could not find %s '));
                    }
                    $categoryModel = new \Shopware\Models\Category\Category();
                    $categoryModel->setParent($parent);
                    $categoryModel->setName($categoryName);
                    $this->getManager()->persist($categoryModel);
                    $this->getManager()->flush();
                    $this->getManager()->clear();
                }

                $categoryId = $categoryModel->getId();

                if (empty($categoryId)) {
                    continue;
                }

                if (!in_array($categoryId, $categoryIds)) {
                    $categoryIds[] = $categoryId;
                }
            }
        }

        return $categoryIds;
    }

    /**
     * Inits ACL-Permissions
     */
    protected function initAcl()
    {
        $this->addAclPermission('exportArticles', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportInStock', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportNotInStock', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportPrices', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportNewsletter', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportCategories', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportArticleImages', 'export', 'Insufficient Permissions');
        $this->addAclPermission('exportOrders', 'export', 'Insufficient Permissions');

        $this->addAclPermission('import', 'import', 'Insufficient Permissions');
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }

        return $this->manager;
    }

    /**
     * Helper function to get access to the article repository.
     *
     * @return Shopware\Models\Article\Repository
     */
    protected function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = $this->getManager()->getRepository('Shopware\Models\Article\Article');
        }

        return $this->articleRepository;
    }

    /**
     * Helper function to get access to the Group repository.
     *
     * @return Shopware\Models\Newsletter\Repository
     */
    protected function getGroupRepository()
    {
        if ($this->groupRepository === null) {
            $this->groupRepository = $this->getManager()->getRepository('Shopware\Models\Newsletter\Group');
        }

        return $this->groupRepository;
    }

    /**
     * Helper function to get access to the Address repository.
     *
     * @return Shopware\Models\Newsletter\Repository
     */
    protected function getAddressRepository()
    {
        if ($this->addressRepository === null) {
            $this->addressRepository = $this->getManager()->getRepository('Shopware\Models\Newsletter\Address');
        }

        return $this->addressRepository;
    }

    /**
     * Helper function to get access to the ContactData repository.
     *
     * @return Shopware\Components\Model\ModelRepository
     */
    protected function getContactDataRepository()
    {
        if ($this->contactDataRepository === null) {
            $this->contactDataRepository = $this->getManager()->getRepository('Shopware\Models\Newsletter\ContactData');
        }

        return $this->contactDataRepository;
    }

    /**
     * Helper function to get access to the articleDetail repository.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getArticleDetailRepository()
    {
        if ($this->articleDetailRepository === null) {
            $this->articleDetailRepository = $this->getManager()->getRepository('Shopware\Models\Article\Detail');
        }

        return $this->articleDetailRepository;
    }

    /**
     * Export articles as XML file
     */
    protected function exportArticleXml()
    {
        $exportVariants = (bool) $this->Request()->getParam('exportVariants', false);

        $limit = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $builder = $this->getArticleRepository()->createQueryBuilder('article');

        $builder->select([
                    'article',
                    'mainDetail',
                    'mainDetailAttribute',
                    'mainDetailPrices',
                    'PARTIAL categories.{id}',
                    'PARTIAL similar.{id}',
                    'PARTIAL accessories.{id}',
                    'images',
                    'links',
                    'downloads',
                    'tax',
                    'customerGroups',
                    'propertyValues',
                ])
                ->leftJoin('article.mainDetail', 'mainDetail')
                ->leftJoin('mainDetail.attribute', 'mainDetailAttribute')
                ->leftJoin('mainDetail.prices', 'mainDetailPrices')
                ->leftJoin('article.tax', 'tax')
                ->leftJoin('article.categories', 'categories')
                ->leftJoin('article.links', 'links')
                ->leftJoin('article.images', 'images')
                ->leftJoin('article.downloads', 'downloads')
                ->leftJoin('article.related', 'accessories')
                ->leftJoin('article.propertyValues', 'propertyValues')
                ->leftJoin('article.similar', 'similar')
                ->leftJoin('article.customerGroups', 'customerGroups')
                ->where('images.parentId IS NULL');

        if ($exportVariants) {
            $builder->addSelect([
                 'details',
                 'detailsAttribute',
                 'detailsPrices',
            ]);
            $builder->leftJoin('article.details', 'details', 'WITH', 'details.kind = 2')
                    ->leftJoin('details.attribute', 'detailsAttribute')
                    ->leftJoin('details.prices', 'detailsPrices');
        }

        $builder->setFirstResult($offset);
        $builder->setMaxResults($limit);

        $query = $builder->getQuery();
        $query = $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);

        $result = $paginator->getIterator()->getArrayCopy();
        foreach ($result as $key => &$article) {
            foreach ($result[$key]['details'] as &$variant) {
                $variant['prices'] = $this->prepareXmlArray($variant['prices'], 'price');
            }
            $article['variants'] = $this->prepareXmlArray($article['details'], 'variant');
            unset($article['details']);

            $article['categories'] = $this->prepareXmlArray($article['categories'], 'category');
            $article['related'] = $this->prepareXmlArray($article['related'], 'related');
            $article['similar'] = $this->prepareXmlArray($article['similar'], 'similar');
            $article['propertyValues'] = $this->prepareXmlArray($article['propertyValues'], 'propertyValue');

            $article['mainDetail']['prices'] = $this->prepareXmlArray($article['mainDetail']['prices'], 'price');
        }

        array_walk_recursive($result, function (&$value) {
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
        });

        $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.articles.' . date('Y.m.d') . '.xml"');
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $convert = new Shopware_Components_Convert_Xml();
        $xmlmap = ['shopware' => ['articles' => ['article' => $result]]];

        echo $convert->encode($xmlmap);
    }

    /**
     * @param $format
     */
    protected function exportArticlesFlat($format)
    {
        $exportVariants = (bool) $this->Request()->getParam('exportVariants', false);

        $exportArticleTranslations = (bool) $this->Request()->getParam('exportArticleTranslations', false);
        $exportCustomergroupPrices = (bool) $this->Request()->getParam('exportCustomergroupPrices', false);

        $limit = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $metaDataFactory = $this->getManager()->getMetadataFactory();
        $attributeFields = $metaDataFactory->getMetadataFor('Shopware\Models\Attribute\Article')->fieldNames;

        $attributeFields = array_flip($attributeFields);
        unset($attributeFields['id']);
        unset($attributeFields['articleDetailId']);
        unset($attributeFields['articleId']);
        $attributeFields = array_flip($attributeFields);

        $selectAttributes = [];
        foreach ($attributeFields as $columnName => $fieldName) {
            $selectAttributes[] = 'at.' . $columnName . ' as attr_' . $fieldName;
        }
        $selectAttributes = implode(",\n", $selectAttributes);

        $joinStatements = '';
        $selectStatements = [];

        if ($exportArticleTranslations) {
            $sql = '
                SELECT id
                FROM s_core_shops
                WHERE `default`=0
            ';

            $languages = Shopware()->Db()->fetchCol($sql);

            $translationFields = [
                'txtArtikel' => 'name',
                'txtzusatztxt' => 'additionaltext',
                'txtshortdescription' => 'description',
                'txtlangbeschreibung' => 'description_long',
            ];

            foreach ($languages as $language) {
                $joinStatements[] = "
                    LEFT JOIN s_core_translations as ta_$language
                    ON ta_$language.objectkey=a.id AND ta_$language.objecttype='article' AND ta_$language.objectlanguage='$language'

                    LEFT JOIN s_core_translations as td_$language
                    ON td_$language.objectkey=d.id AND td_$language.objecttype='variant' AND td_$language.objectlanguage='$language'
                ";

                $selectStatements[] = "ta_$language.objectdata as article_translation_$language";
                $selectStatements[] = "td_$language.objectdata as detail_translation_$language";

                foreach ($translationFields as $field) {
                    $selectStatements[] = "'' as {$field}_{$language}";
                }
            }
        }

        if ($selectStatements) {
            $selectStatements = ', ' . implode(', ', $selectStatements);
        } else {
            $selectStatements = '';
        }

        if ($exportCustomergroupPrices) {
            $sql = 'SELECT `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC';
        } else {
            $sql = "SELECT `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE `groupkey`='EK' AND mode=0";
        }
        $customergroups = Shopware()->Db()->fetchAll($sql);

        $sqlAddSelectP = '';
        if (!empty($customergroups)) {
            foreach ($customergroups as $cg) {
                if ($cg['groupkey'] == 'EK') {
                    $cg['id'] = '';
                }

                $joinStatements[] = "
                    LEFT JOIN `s_articles_prices` `p{$cg['id']}`
                    ON `p{$cg['id']}`.articledetailsID = d.id
                    AND `p{$cg['id']}`.pricegroup='{$cg['groupkey']}'
                    AND `p{$cg['id']}`.from=1
                ";

                if (empty($cg['taxinput'])) {
                    $sqlAddSelectP .= "REPLACE(ROUND(`p{$cg['id']}`.price,2),'.',',')";
                } else {
                    $sqlAddSelectP .= "REPLACE(ROUND(`p{$cg['id']}`.price*(100+t.tax)/100,2),'.',',')";
                }

                if ($cg['groupkey'] == 'EK') {
                    $sqlAddSelectP .= ' as price, ';
                } else {
                    $sqlAddSelectP .= " as price_{$cg['groupkey']}, ";
                }
            }
        }

        $joinStatements = implode(" \n ", $joinStatements);

        if ($exportVariants) {
            $variantsSql = '
            INNER JOIN s_articles_details d
            ON d.articleID = a.id AND d.kind <> 3

            INNER JOIN s_articles_details d2
            ON d2.id = a.main_detail_id
            ';
        } else {
            $variantsSql = '
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1

            INNER JOIN s_articles_details d2
            ON d2.id = a.main_detail_id
            ';
        }

        $shop = $this->getManager()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources();

        $imagePath = 'http://' . $shop->getHost() . $shop->getBasePath() . '/media/image/';

        $sql = "
            SELECT
                d.ordernumber,
                d2.ordernumber as mainnumber,
                a.name,
                d.additionaltext,
                s.name as supplier,
                t.tax,
                {$sqlAddSelectP}
                REPLACE(p.price,'.',',') as net_price,
                REPLACE(ROUND(p.pseudoprice*(100+t.tax)/100,2),'.',',') as pseudoprice,
                REPLACE(ROUND(p.pseudoprice,2),'.',',') as net_pseudoprice,
                REPLACE(ROUND(d.purchaseprice,2),'.',',') as purchaseprice,
                a.active,
                d.instock,
                d.stockmin,
                a.description,
                a.description_long,
                d.shippingtime,
                IF(a.datum='0000-00-00','',a.datum) as added,
                IF(a.changetime='0000-00-00 00:00:00','',a.changetime) as `changed`,
                IF(d.releasedate='0000-00-00','',d.releasedate) as releasedate,
                d.shippingfree,
                a.topseller,
                a.metaTitle,
                a.keywords,
                d.minpurchase,
                d.purchasesteps,
                d.maxpurchase,
                d.purchaseunit,
                d.referenceunit,
                d.packunit,
                d.unitID,
                a.pricegroupID,
                a.pricegroupActive,
                a.laststock,
                d.suppliernumber,
                COALESCE(sai.impressions, 0) as impressions,
                d.sales,
                IF(e.file IS NULL,0,1) as esd,
                d.weight,
                d.width,
                d.height,
                d.length,
                d.ean,
                u.unit,
                (
                    SELECT GROUP_CONCAT(rad.ordernumber SEPARATOR '|') FROM s_articles_similar sa
                    INNER JOIN s_articles ra ON ra.id = sa.relatedarticle
                    INNER JOIN s_articles_details rad ON ra.main_detail_id = rad.id
                    WHERE sa.articleID=a.id
                ) as similar,
                (
                    SELECT GROUP_CONCAT(rad.ordernumber SEPARATOR '|') FROM s_articles_relationships sa
                    INNER JOIN s_articles ra ON ra.id = sa.relatedarticle
                    INNER JOIN s_articles_details rad ON ra.main_detail_id = rad.id
                    WHERE sa.articleID=a.id
                ) as crosselling,

                (SELECT GROUP_CONCAT(categoryID SEPARATOR '|') FROM s_articles_categories WHERE articleID=a.id) as categories,

                '' as categorypaths,

                (
                    SELECT GROUP_CONCAT(CONCAT('{$imagePath}',img,'.',extension) ORDER BY `main`,  `position`  SEPARATOR '|')
                    FROM `s_articles_img`
                    WHERE articleID=a.id
                ) as images,

                a.filtergroupID as filterGroupId,

                (
                    SELECT GROUP_CONCAT(id SEPARATOR '|' ) FROM s_filter_articles fa
                    LEFT JOIN s_filter_values fv ON fa.valueId = fv.id
                    WHERE fa.articleId = a.id
                ) as propertyValues,

                configurator_set_id as configuratorsetID,
                acs.type as configuratortype,
                (
                    SELECT GROUP_CONCAT(CONCAT_WS(':', cg.name, co.name) SEPARATOR '|') FROM s_articles_details ad
                    INNER JOIN s_article_configurator_option_relations cor ON cor.article_id = ad.id
                    INNER JOIN s_article_configurator_options co ON cor.option_id = co.id
                    INNER JOIN s_article_configurator_groups cg ON co.group_id = cg.id
                    WHERE ad.id = d.id
                    GROUP BY ad.id
                ) as configuratorOptions,

                {$selectAttributes}
                {$selectStatements}

            FROM s_articles a

            {$variantsSql}

            LEFT JOIN s_articles_attributes at
            ON at.articledetailsID=d.id

            LEFT JOIN `s_core_units` as u
            ON d.unitID = u.id

            LEFT JOIN s_core_tax as t
            ON a.taxID = t.id

            LEFT JOIN s_articles_supplier as s
            ON a.supplierID = s.id

            LEFT JOIN s_articles_esd e
            ON e.articledetailsID=d.id

            LEFT JOIN s_article_configurator_sets acs
            ON a.configurator_set_id = acs.id

            LEFT JOIN
            (
              SELECT articleId AS id, SUM(s.impressions) AS impressions
              FROM s_statistics_article_impression s
              GROUP BY articleId
            ) sai ON sai.id = a.id

            {$joinStatements}

            WHERE a.mode = 0
            ORDER BY a.id, d.kind, d.id

            LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);

        if ($format === 'csv') {
            $filename = 'export.articles.' . date('Y.m.d') . '.csv';
            $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $convert = new Shopware_Components_Convert_Csv();
            $first = true;
            $keys = [];
            while ($row = $stmt->fetch()) {
                $row = $this->prepareArticleRow($row, $languages, $translationFields);

                if ($first) {
                    $first = false;
                    $keys = array_keys($row);
                    echo "\xEF\xBB\xBF"; // UTF-8 BOM
                    echo $convert->_encode_line(array_combine($keys, $keys), $keys) . "\r\n";
                }

                echo $convert->_encode_line($row, $keys) . "\r\n";
            }

            return;
        }

        if ($format === 'excel') {
            $filename = 'export.articles.' . date('Y.m.d') . '.xls';
            $title = 'Articles Export';

            $this->Response()->setHeader('Content-Type', 'application/vnd.ms-excel;charset=UTF-8');
            $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $excel = new Shopware_Components_Convert_Excel();
            $excel->setTitle($title);

            $first = true;
            echo $excel->getHeader();
            while ($row = $stmt->fetch()) {
                $row = $this->prepareArticleRow($row, $languages, $translationFields);

                if ($first) {
                    $first = false;
                    echo $excel->encodeRow(array_keys($row));
                }

                echo $excel->encodeRow($row);
            }

            echo $excel->getFooter();

            return;
        }
    }

    /**
     * Helper method which creates images for variants based on the image mappings
     *
     * @param $articleId
     */
    protected function recreateVariantImages($articleId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $images = $builder->select(['images', 'mappings', 'rules', 'option'])
                ->from('Shopware\Models\Article\Image', 'images')
                ->innerJoin('images.mappings', 'mappings')
                ->leftJoin('mappings.rules', 'rules')
                ->leftJoin('rules.option', 'option')
                ->where('images.articleId = ?1')
                ->andWhere('images.parentId IS NULL')
                ->setParameter(1, $articleId)
                ->getQuery();

        $images = $images->execute();

        /** @var \Shopware\Models\Article\Image $image */
        foreach ($images as $image) {
            $query = $this->getArticleRepository()->getArticleImageDataQuery($image->getId());
            $imageData = $query->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $this->getArticleRepository()->getDeleteImageChildrenQuery($image->getId())->execute();

            foreach ($image->getMappings() as $mapping) {
                $options = [];

                foreach ($mapping->getRules() as $rule) {
                    $options[] = $rule->getOption();
                }

                $imageData['path'] = null;
                $imageData['parent'] = $image;

                $details = $this->getArticleRepository()->getDetailsForOptionIdsQuery($articleId, $options)->getResult();

                foreach ($details as $detail) {
                    $newImage = new \Shopware\Models\Article\Image();
                    $newImage->fromArray($imageData);
                    $newImage->setArticleDetail($detail);
                    Shopware()->Models()->persist($newImage);
                    Shopware()->Models()->flush();
                }
            }
        }
    }

    /**
     * Imports customers from XML file
     *
     * @param string $filePath
     */
    protected function importArticlesXml($filePath)
    {
        $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
        $results = $xml->articles;
        $results = $this->simplexml2array($results);

        $articleRepostiory = $this->getArticleRepository();
        $articleDetailRepostiory = $this->getArticleDetailRepository();

        /** @var \Shopware\Components\Api\Resource\Article $articleResource */
        $articleResource = \Shopware\Components\Api\Manager::getResource('article');

        $articleMetaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Article');
        $articleDetailMetaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Detail');

        $articleMapping = [];
        foreach ($articleMetaData->fieldMappings as $fieldMapping) {
            $articleMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $articleDetailMapping = [];
        foreach ($articleDetailMetaData->fieldMappings as $fieldMapping) {
            $articleDetailMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $counter = 0;
        $results = $this->prepareImportXmlData($results['article']);

        foreach ($results as $article) {
            if (empty($article['id']) && empty($article['mainDetail']['number'])) {
                continue;
            }

            try {
                ++$counter;

                if (isset($article['mainDetail']['number'])) {
                    /** @var \Shopware\Models\Article\Detail $articleDetailModel */
                    $articleDetailModel = $articleDetailRepostiory->findOneBy(['number' => $article['mainDetail']['number']]);
                    if ($articleDetailModel) {
                        /** @var \Shopware\Models\Article\Article $articleModel */
                        $articleModel = $articleDetailModel->getArticle();
                        if (!$articleModel) {
                            continue;
                        }
                    }
                } elseif (isset($article['id'])) {
                    $articleModel = $articleRepostiory->find($article['id']);
                    if (!$articleModel) {
                        continue;
                    }
                }

                $updateData = $this->mapFields($article, $articleMapping, ['taxId', 'tax', 'supplierId', 'supplier', 'propertyValues', 'propertyGroup', 'configuratorSet']);
                $detailData = $this->mapFields($article, $articleDetailMapping, ['mainDetail']);

                $updateData['mainDetail'] = $detailData['mainDetail'];

                if (isset($article['similar'])) {
                    $updateData['similar'] = $this->prepareImportXmlData($article['related']['similar']);
                }

                if (isset($article['propertyValues']) && !empty($article['propertyValues'])) {
                    $updateData['propertyValues'] = $this->prepareImportXmlData($article['propertyValues']['propertyValue']);
                } else {
                    unset($updateData['propertyValues']);
                }

                if (isset($article['related'])) {
                    $updateData['related'] = $this->prepareImportXmlData($article['related']['related']);
                }

                if (isset($article['categories'])) {
                    $updateData['categories'] = $this->prepareImportXmlData($article['categories']['category']);
                }

                if (isset($article['variants']) && !empty($article['variants'])) {
                    $updateData['variants'] = $this->prepareImportXmlData($article['variants']['variant']);
                    foreach ($article['variants'] as $key => $variant) {
                        if (isset($variant['prices'])) {
                            $updateData['variants'][$key]['prices'] = $this->prepareImportXmlData($variant['prices']['price']);
                        }
                    }
                }

                if (isset($article['configuratorSet']) && !empty($article['configuratorSet'])) {
                    foreach ($article['configuratorSet']['groups'] as $groupKey => $group) {
                        $updateData['configuratorSet']['groups'][$groupKey] = $group;
                        foreach ($group['options'] as $optionKey => $option) {
                            $updateData['configuratorSet']['groups'][$groupKey]['options'][$optionKey] = array_pop($option);
                        }
                    }
                }

                if (isset($article['prices'])) {
                    $updateData['mainDetail']['prices'] = $this->prepareImportXmlData($article['prices']['price']);
                }

                if (isset($article['mainDetail']['prices'])) {
                    $updateData['mainDetail']['prices'] = $this->prepareImportXmlData($article['mainDetail']['prices']['price']);
                }

                unset($article['images']);
                unset($article['variants']);

                if ($articleModel) {
                    $result = $articleResource->update($articleModel->getId(), $updateData);
                } else {
                    $result = $articleResource->create($updateData);
                }

                if ($result) {
                    $articleIds[] = $result->getId();
                }
            } catch (\Exception $e) {
                if ($e instanceof Shopware\Components\Api\Exception\ValidationException) {
                    $messages = [];
                    /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
                    foreach ($e->getViolations() as $violation) {
                        $messages[] = sprintf(
                            '%s: %s', $violation->getPropertyPath(), $violation->getMessage()
                        );
                    }
                    $errormessage = implode("\n", $messages);
                } else {
                    $errormessage = $e->getMessage();
                }

                if (!empty($article['name']) && !empty($article['id'])) {
                    $errors[] = 'Error with article: ' . $article['name'] . ' and articleID: ' . $article['id'];
                }
                $errors[] = "Error in line {$counter}: $errormessage";
            }
        }

        if (!empty($errors)) {
            $message = implode("<br>\n", $errors);
            echo json_encode([
                'success' => false,
                'message' => "Error: $message",
            ]);

            return;
        }

        echo json_encode([
        'success' => true,
        'message' => sprintf('Successfully saved: %s', count($articleIds)),
        ]);
    }

    /**
     * Imports customers from CSV file
     *
     * @param string $filePath
     */
    protected function importArticlesCsv($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $articleIds = [];
        $errors = [];
        $counter = 0;

        /** @var \Shopware\Components\Api\Resource\Article $articleResource */
        $articleResource = \Shopware\Components\Api\Manager::getResource('article');

        $articleMetaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Article');
        $articleDetailMetaData = $this->getManager()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Detail');

        $articleMapping = [];
        foreach ($articleMetaData->fieldMappings as $fieldMapping) {
            $articleMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $articleDetailMapping = [];
        foreach ($articleDetailMetaData->fieldMappings as $fieldMapping) {
            $articleDetailMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $this->getManager()->getConnection()->beginTransaction(); // suspend auto-commit

        $postInsertData = [];

        try {
            foreach ($results as $articleData) {
                ++$counter;

                // Prevent invalid records from being imported and throw a exception
                if (empty($articleData['name'])) {
                    throw new \Exception('Article name may not be empty');
                }
                if (empty($articleData['ordernumber'])) {
                    throw new \Exception('Article ordernumber may not be empty');
                }
                if (!empty($articleData['ordernumber'])) {
                    if (preg_match('/[^a-zA-Z0-9-_. ]/', $articleData['ordernumber']) !== 0) {
                        throw new \Exception("Invalid ordernumber: {$articleData['ordernumber']}");
                    }
                }

                $result = $this->saveArticle($articleData, $articleResource, $articleMapping, $articleDetailMapping);
                $result = $this->getArticleRepository()->find($result->getId());

                if (!$result instanceof \Shopware\Models\Article\Article) {
                    $errors[] = $result;
                    continue;
                }
                if ($result) {
                    $articleIds[] = $result->getId();

                    $updateData = [];

                    if (!empty($articleData['similar'])) {
                        $similars = explode('|', $articleData['similar']);
                        foreach ($similars as $similarId) {
                            $updateData['similar'][] = ['number' => $similarId];
                        }
                    }

                    if (!empty($articleData['crosselling'])) {
                        $crossSellings = explode('|', $articleData['crosselling']);
                        foreach ($crossSellings as $crosssellingId) {
                            $updateData['related'][] = ['number' => $crosssellingId];
                        }
                    }

                    // During the import each article creates a set with it own configruatorOptions as Options
                    // when persisting only these options will be set to be set-option relations
                    // in order to fix this, we set all options active which can be assigned to a given article
                    /** @var \Shopware\Models\Article\Configurator\Set $configuratorSet */
                    $configuratorSet = $result->getConfiguratorSet();
                    if ($configuratorSet !== null) {
                        $configuratorSet->getOptions()->clear();
                        $articleRepository = $this->getArticleRepository();
                        $ids = $articleRepository->getArticleConfiguratorSetOptionIds($result->getId());
                        if (!empty($ids)) {
                            $configuratorOptionRepository = Shopware()->Models()->getRepository('\Shopware\Models\Article\Configurator\Option');
                            $optionModels = $configuratorOptionRepository->findBy(['id' => $ids]);
                            $configuratorSet->setOptions($optionModels);
                        }
                    }

                    if (!empty($updateData)) {
                        $updateData['id'] = $result->getId();
                        $postInsertData[] = $updateData;
                    }
                }

                $this->getManager()->flush();
                $this->getManager()->clear();
            }

            foreach ($postInsertData as $updateData) {
                $result = $articleResource->update($updateData['id'], $updateData);
            }

            $this->insertPrices($results);

            $this->getManager()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getManager()->getConnection()->rollBack();

            if ($e instanceof Shopware\Components\Api\Exception\ValidationException) {
                $messages = [];
                /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
                foreach ($e->getViolations() as $violation) {
                    $messages[] = sprintf(
                        '%s: %s', $violation->getPropertyPath(), $violation->getMessage()
                    );
                }

                $errormessage = implode("\n", $messages);
            } else {
                $errormessage = $e->getMessage();
            }

            $errors[] = "Error in line {$counter}: $errormessage\n";

            $errors = $this->toUtf8($errors);
            $message = implode("<br>\n", $errors);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $message,
            ]);

            return;
        }

        if (!empty($errors)) {
            $errors = $this->toUtf8($errors);
            $message = implode("<br>\n", $errors);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $message,
            ]);

            return;
        }

        echo json_encode([
             'success' => true,
             'message' => sprintf('Successfully saved: %s', count($articleIds)),
        ]);
    }

    /**
     * Helper function which creates customerGroup-prices if available
     *
     * @param $results CSV Iterator
     */
    protected function insertPrices($results)
    {
        // create pricegroup array
        $customerPriceGroups = [];
        foreach ($results as $articleData) {
            foreach ($articleData as $key => $value) {
                if (strpos($key, 'price_') !== false) {
                    $customerPriceGroups[str_replace('price_', '', $key)] = $value;
                }
            }
        }

        $sql = 'SELECT `groupkey` as `key`, `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC';
        $localCustomerGroups = Shopware()->Db()->fetchAssoc($sql);

        // Iter all given articles
        foreach ($results as $articleData) {
            // get articleId and detailId by ordernumber
            $sql = '
                SELECT ad.id, articleID, t.tax
                FROM s_articles_details ad
                JOIN s_articles a
                ON a.id = ad.articleID
                JOIN s_core_tax t
                ON t.id = a.taxID
                WHERE ordernumber LIKE ?
            ';
            $stmt = Shopware()->Db()->query($sql, $articleData['ordernumber']);
            $result = $stmt->fetch();
            $tax = $result['tax'];

            // delete old and save new prices
            foreach ($customerPriceGroups as $customerGroup => $price) {
                $price = floatval(str_replace(',', '.', $price));

                // if customer group is a preTax group (taxinput=true), recalculate the price
                $isPreTax = $localCustomerGroups[$customerGroup]['taxinput'];
                if ($isPreTax) {
                    $price = $price / (100 + $tax) * 100;
                }

                // Delete old pricegroups, if detailID and 'from' match
                Shopware()->Db()->delete('s_articles_prices', [
                    'pricegroup = ?' => $customerGroup,
                    'articledetailsID = ?' => $result['id'],
                    'CAST(`from` AS UNSIGNED) >= ?' => 1,
                ]);

                Shopware()->Db()->insert('s_articles_prices', [
                    'articleID' => $result['articleID'],
                    'articledetailsID' => $result['id'],
                    'pricegroup' => $customerGroup,
                    'from' => 1,
                    'to' => 'beliebig',
                    'price' => $price,
                    'pseudoprice' => 0,
                    'percent' => 0,
                ]);
            }
        }
    }

    /**
     * @param array $articleData
     * @param $articleResource
     * @param array $articleMapping
     * @param array $articleDetailMapping
     *
     * @return \Shopware\Models\Article\Article
     */
    protected function saveArticle($articleData, $articleResource, $articleMapping, $articleDetailMapping)
    {
        $importImages = false;

        $articleData = $this->toUtf8($articleData);

        $articleRepostiory = $this->getArticleRepository();
        $articleDetailRepostiory = $this->getArticleDetailRepository();

        if (empty($articleData['ordernumber'])) {
            return false;
        }

        unset($articleData['articleID'], $articleData['articledetailsID']);

        $isOldConfigurator = false;
        if (isset($articleData['configurator']) && !empty($articleData['configurator']) && empty($articleData['configuratorsetID'])) {
            $isOldConfigurator = true;
            $configurator = $this->prepareLegacyConfiguratorImport($articleData['configurator']);
        }

        $isNewConfigurator = false;
        if (isset($articleData['configuratorOptions']) && !empty($articleData['configuratorOptions'])) {
            if (!isset($articleData['configuratorsetID']) || empty($articleData['configuratorsetID'])) {
                return sprintf('Article with ordernumber %s is a variant but has no configuratorSetID. It is probably broken and was skipped', $articleData['ordernumber']);
            }
            list($configuratorSet, $configuratorOptions) = $this->prepareNewConfiguratorImport($articleData['configuratorOptions']);
            $isNewConfigurator = true;
        }

        $articleData = $this->prepareTranslation($articleData);

        $isOldVariant = false;
        if (!empty($articleData['additionaltext']) && empty($articleData['mainnumber']) && empty($articleData['configuratorsetID'])) {
            $isOldVariant = true;
            $groupName = $articleData['ordernumber'] . '-Group';

            $configuratorSet = [
                'groups' => [[
                    'name' => $groupName,
                    'options' => [
                        ['name' => $articleData['additionaltext']],
                    ], ],
                ],
            ];

            $configuratorOptions = [[
                'group' => $groupName,
                'option' => $articleData['additionaltext'],
            ]];
        } elseif (!empty($articleData['mainnumber']) && empty($articleData['configurator']) && empty($articleData['configuratorsetID'])) {
            $isOldVariant = true;
            $groupName = $articleData['mainnumber'] . '-Group';
            $configuratorOptions = [[
                'group' => $groupName,
                'option' => $articleData['additionaltext'],
            ]];
        }

        // unset legacy attributes
        unset($articleData['attributegroupID']);
        unset($articleData['attributevalues']);

        // Check for legacy purchase price ('baseprice')
        if (isset($articleData['baseprice'])) {
            $articleData['purchaseprice'] = $this->sValFloat($articleData['baseprice']);
            unset($articleData['baseprice']);
        }

        $updateData = $this->mapFields($articleData, $articleMapping, ['taxId', 'tax', 'supplierId', 'supplier', 'whitelist', 'translations', 'pseudoprice']);
        $detailData = $this->mapFields($articleData, $articleDetailMapping);

        if (!empty($articleData['categorypaths'])) {
            $categoryIds = $this->createCategoriesByCategoryPaths($articleData['categorypaths']);

            unset($articleData['categories']);
            unset($articleData['categorypaths']);
            foreach ($categoryIds as $categoryId) {
                $updateData['categories'][] = ['id' => $categoryId];
            }
        }

        if (isset($articleData['tax']) && empty($articleData['tax'])) {
            $updateData['tax'] = 19;
        }

        $prices = [
            'price' => 'price',
            'pseudoprice' => 'pseudoPrice',
        ];
        $detailData['prices'] = [];
        foreach ($prices as $priceKey => $mappedName) {
            if (!empty($articleData[$priceKey])) {
                $detailData['prices'][0][$mappedName] = $articleData[$priceKey];
            }
        }
        if (empty($detailData['prices'])) {
            unset($detailData['prices']);
        }

        if (!empty($articleData['propertyValues'])) {
            $propertyValues = explode('|', $articleData['propertyValues']);
            foreach ($propertyValues as $propertyValue) {
                $updateData['propertyValues'][] = ['id' => $propertyValue];
            }
        }

        if ($importImages && !empty($articleData['images'])) {
            $images = explode('|', $articleData['images']);
            foreach ($images as $imageLink) {
                $updateData['images'][] = ['link' => $imageLink];
            }
        }

        if (!empty($articleData['categories'])) {
            $categories = explode('|', $articleData['categories']);
            foreach ($categories as $categoryId) {
                $updateData['categories'][] = ['id' => $categoryId];
            }
        }

        // unset similar and crosselling, will be inserted post insert
        unset($articleData['similar']);
        unset($articleData['crosselling']);

        $attribute = $this->prefixToArray($articleData, 'attr_');
        if (!empty($attribute)) {
            $detailData['attribute'] = $attribute;
        }

        if ($isOldVariant) {
            if (isset($configuratorSet)) {
                /** @var \Shopware\Models\Article\Detail $articleDetailModel */
                $articleDetailModel = $articleDetailRepostiory->findOneBy(['number' => $articleData['ordernumber']]);
                if ($articleDetailModel) {
                    /** @var \Shopware\Models\Article\Article $articleModel */
                    $articleModel = $articleDetailModel->getArticle();
                    if (!$articleModel) {
                        throw new \Exception('Article not Found');
                    }
                }

                $updateData['configuratorSet'] = $configuratorSet;
                $updateData['variants'][0] = $detailData;
                $updateData['variants'][0]['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][0]['standard'] = true;
                $updateData['mainDetail'] = $detailData;
//                $updateData['mainDetail']['number'] .= '_main';

                if ($articleModel) {
                    throw new \Exception(sprintf('Legacy variant article with ordernumber %s can only be imported once.', $articleData['ordernumber']));
                }
                $result = $articleResource->create($updateData);
            } else {
                /** @var \Shopware\Models\Article\Detail $articleDetailModel */
                $articleDetailModel = $articleDetailRepostiory->findOneBy(['number' => $articleData['mainnumber']]);
                if ($articleDetailModel) {
                    /** @var \Shopware\Models\Article\Article $articleModel */
                    $articleModel = $articleDetailModel->getArticle();
                    if (!$articleModel) {
                        throw new \Exception('Article not Found');
                    }
                }
                $updateData = [];
                $detailData['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][] = $detailData;

                if ($articleModel) {
                    $result = $articleResource->update($articleModel->getId(), $updateData);
                } else {
                    throw new \Exception('Parent variant not found');
                }
            }

            return $result;
        }

        // For old 3.x configurators
        if ($isOldConfigurator) {
            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->findOneBy(['number' => $articleData['ordernumber']]);
            if ($articleDetailModel) {
                /** @var \Shopware\Models\Article\Article $articleModel */
                $articleModel = $articleDetailModel->getArticle();
                if (!$articleModel) {
                    throw new \Exception('Article not Found');
                }
            }

            $updateData['configuratorSet'] = $configurator['configuratorSet'];
            $updateData['variants'] = $configurator['variants'];
            $updateData['mainDetail'] = $detailData;

            if ($articleModel) {
                throw new \Exception(sprintf('Legacy configurator article with ordernumber %s can only be imported once.', $articleData['ordernumber']));
            }
            $result = $articleResource->create($updateData);

            return $result;
        }

        // For configurators as used in SW 4
        if ($isNewConfigurator) {
            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->findOneBy(['number' => $articleData['mainnumber']]);
            if ($articleDetailModel) {
                /** @var \Shopware\Models\Article\Article $articleModel */
                $articleModel = $articleDetailModel->getArticle();
                if (!$articleModel) {
                    throw new \Exception('Article not Found');
                }
            }

            // update?
            if (isset($articleModel) && $articleModel !== null) {
                if (!isset($updateData)) {
                    $updateData = ['variants' => []];
                }
                if (!isset($updateData['variants'])) {
                    $updateData['variants'] = [];
                }
                $updateData['configuratorSet'] = $configuratorSet;

                $detailData['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][] = $detailData;
                $result = $articleResource->update($articleModel->getId(), $updateData);
            } else {
                $updateData['configuratorSet'] = $configuratorSet;
                $updateData['variants'][0] = $detailData;
                $updateData['variants'][0]['configuratorOptions'] = $configuratorOptions;
                $updateData['variants'][0]['standard'] = true;
                $updateData['mainDetail'] = $detailData;
                $result = $articleResource->create($updateData);
            }

            return $result;
        }

        /** @var \Shopware\Models\Article\Detail $articleDetailModel */
        $articleDetailModel = $articleDetailRepostiory->findOneBy(['number' => $articleData['ordernumber']]);
        if ($articleDetailModel) {
            /** @var \Shopware\Models\Article\Article $articleModel */
            $articleModel = $articleDetailModel->getArticle();
            if (!$articleModel) {
                throw new \Exception('Article not Found');
            }
        }

        if ($articleModel) {
            if ($articleDetailModel->getKind() == 1) {
                $updateData['mainDetail'] = $detailData;
            } elseif ($articleDetailModel->getKind() == 2) {
                $detailData['id'] = $articleDetailModel->getId();
                $updateData = [];
                $updateData['variants'][] = $detailData;
            }
            $result = $articleResource->update($articleModel->getId(), $updateData);
        } else {
            $updateData['mainDetail'] = $detailData;
            $result = $articleResource->create($updateData);
        }

        return $result;
    }

    /**
     * Takes a new style configurator and converts it into a proper array
     *
     * @param $configuratorData
     *
     * @return array
     */
    protected function prepareNewConfiguratorImport($configuratorData)
    {
        $configuratorGroups = [];
        $configuratorOptions = [];

        // split string into parts and recieve group and options this way
        $pairs = explode('|', $configuratorData);
        foreach ($pairs as $pair) {
            list($group, $option) = explode(':', $pair);

            $currentGroup = ['name' => $group, 'options' => [['name' => $option]]];
            $configuratorGroups[] = $currentGroup;

            $configuratorOptions[] = ['option' => $option, 'group' => $group];
        }

        return [
            ['groups' => $configuratorGroups],      // ConfiguratorSet
            $configuratorOptions,                         // ConfiguratorOptions
        ];
    }

    /**
     * Transforms legacy configurator data
     *
     * @param $configuratorData
     *
     * @return array
     */
    protected function prepareLegacyConfiguratorImport($configuratorData)
    {
        $variants = [];
        $values = explode("\n", $configuratorData);
        foreach ($values as $value) {
            $value = explode('|', trim($value));
            if (count($value) < 4) {
                continue;
            }

            $value[1] = explode(',', $value[1]);
            $value[3] = trim($value[3], ', ');
            $variant = [
                'additionalText' => $value[3],
                'number' => $value[0],
                'inStock' => $value[1][0],
            ];
            $value[3] = explode(',', $value[3]);

            if (isset($value[1][1])) {
                $variant['active'] = $value[1][1];
            }

            if (isset($value[1][2])) {
                $variant['standard'] = $value[1][2];
            }

            $variant['configuratorOptions'] = [];

            for ($i = 0, $c = count($value[3]); $i < $c; ++$i) {
                $value[3][$i] = explode(':', $value[3][$i]);

                $variant['configuratorOptions'][] = [
                    'group' => trim($value[3][$i][0]),
                    'option' => trim($value[3][$i][1]),
                ];
            }

            $variant['prices'] = [[
                'price' => $value[2],
            ]];

            $variants[] = $variant;
        }

        $groups = [];
        foreach ($variants as $variant) {
            foreach ($variant['configuratorOptions'] as $configuratorOption) {
                if (!isset($groups[$configuratorOption['group']])) {
                    $groups[$configuratorOption['group']] = [];
                }

                if (!in_array($configuratorOption['option'], $groups[$configuratorOption['group']])) {
                    $groups[$configuratorOption['group']][] = $configuratorOption['option'];
                }
            }
        }

        $configuratorGroups = [];
        foreach ($groups as $groupName => $options) {
            $configuratorGroup = [];
            $configuratorGroup['name'] = $groupName;
            $configuratorGroup['options'] = [];
            foreach ($options as $option) {
                $configuratorGroup['options'][] = [
                    'name' => $option,
                ];
            }
            $configuratorGroups[] = $configuratorGroup;
        }

        $configurator['variants'] = $variants;
        $configurator['configuratorSet'] = ['groups' => $configuratorGroups];

        return $configurator;
    }

    /**
     * Prepare an articles' translation. Needed to be called before the article mapping is done
     *
     * @param $data
     *
     * @return array
     */
    protected function prepareTranslation($data)
    {
        $translationByLanguage = [];

        $whitelist = [
            'name' => 'name',
            'additionaltext' => 'additionaltext',
            'description_long' => 'descriptionLong',
            'description' => 'description',
            'packUnit' => 'packunit',
            'keywords' => 'keywords',
        ];

        // first get a list of all available translation by language ID
        foreach ($data as $key => $value) {
            foreach ($whitelist as $translationKey => $translationMapping) {
                if (strpos($key, $translationKey . '_') !== false) {
                    $parts = explode('_', $key);
                    $language = array_pop($parts);
                    if (!is_numeric($language)) {
                        continue;
                    }

                    if (!isset($translationByLanguage[$language])) {
                        $translationByLanguage[$language] = [];
                        $translationByLanguage[$language]['shopId'] = $language;
                    }
                    $translationByLanguage[$language][$translationMapping] = $value;

                    // remove translation and whitelist entry in order not to double-set translations
                    unset($data[$key]);
                    unset($whitelist[$translationKey]);
                }
            }
        }

        $data['translations'] = $translationByLanguage;

        return $data;
    }

    /**
     * @param array $orderIds
     * @param int   $statusId
     *
     * @throws Exception
     */
    protected function updateOrderStatus($orderIds, $statusId)
    {
        $status = $this->getManager()->getRepository('Shopware\Models\Order\Status')->findOneBy([
            'id' => $statusId,
            'group' => 'state',
        ]);

        if (empty($status)) {
            throw new Exception(sprintf('OrderStatus by id %s not found', $statusId));
        }

        $builder = $this->getManager()->getRepository('Shopware\Models\Order\Order')
                ->createQueryBuilder('orders')
                ->update();

        $builder->set('orders.status', ':status');
        $builder->setParameter('status', $status);

        $builder->where('orders.id IN (:orderIds)');
        $builder->setParameter('orderIds', $orderIds);
        $builder->andWhere('orders.status != -1');

        $builder->getQuery()->execute();
    }

    /**
     * @return int
     */
    protected function deleteEmptyCategories()
    {
        $sql = '
                DELETE ac
                FROM s_articles_categories ac
                LEFT JOIN s_categories c
                    ON c.id = ac.categoryID
                LEFT JOIN s_articles a
                    ON  a.id = ac.articleID
                WHERE c.id IS NULL OR a.id IS NULL
            ';
        Shopware()->Db()->exec($sql);

        $sql = '
            SELECT c.id, COUNT(ac.articleID)
            FROM s_categories c
                LEFT JOIN s_articles_categories ac
                    ON ac.categoryID = c.id
            WHERE c.id != 1
            AND c.id NOT IN (SELECT category_id FROM s_core_shops)
            GROUP BY c.id
            HAVING articleCount = 0
         ';

        $emptyCategories = Shopware()->Db()->fetchCol($sql);
        $result = 0;
        if (count($emptyCategories)) {
            $result = Shopware()->Db()->delete('s_categories', ['id IN(?)' => $emptyCategories]);
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function deleteAllCategories()
    {
        $sql = 'SELECT category_id FROM s_core_shops';
        $result = Shopware()->Db()->fetchCol($sql);

        if (count($result)) {
            $result = Shopware()->Db()->delete('s_categories', ['id NOT IN(?)' => $result]);
        } else {
            $result = Shopware()->Db()->exec('TRUNCATE s_categories');
        }

        Shopware()->Db()->exec('TRUNCATE s_articles_categories');

        return $result;
    }

    /**
     * @param array  $input
     * @param string $prefix
     *
     * @return array
     */
    protected function prefixToArray(&$input, $prefix)
    {
        $output = [];
        foreach ($input as $key => $value) {
            if (stripos($key, $prefix) === 0) {
                $oldKey = $key;
                $key = substr($key, strlen($prefix));
                $output[$key] = $value;
                unset($input[$oldKey]);
            }
        }

        return $output;
    }

    /**
     * @param array $input
     * @param array $mapping
     * @param array $whitelist
     *
     * @return array
     */
    protected function mapFields($input, $mapping = [], $whitelist = [])
    {
        $output = [];

        $whitelist = $mapping + $whitelist;

        foreach ($input as $key => $value) {
            if (isset($mapping[$key])) {
                $output[$mapping[$key]] = $value;
            } elseif (in_array($key, $whitelist)) {
                $output[$key] = $value;
            }
                // fields we don't know we don't want
        }

        return $output;
    }

    /**
     * @param $input
     * @param string $keyname
     *
     * @return array
     */
    protected function prepareXmlArray($input, $keyname = 'variant')
    {
        $output = [];
        foreach ($input as &$item) {
            $output[$keyname][] = $item;
        }

        return $output;
    }

    /**
     * @param string $url          URL of the resource that should be loaded (ftp, http, file)
     * @param string $baseFilename Optional: Instead of creating a hash, create a filename based on the given one
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return bool|string returns the absolute path of the downloaded file
     */
    protected function load($url, $baseFilename = null)
    {
        $destPath = Shopware()->DocPath('media_' . 'temp');
        if (!is_dir($destPath)) {
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);

        if (!file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not exist.", $destPath)
            );
        } elseif (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not have write permissions.", $destPath)
            );
        }

        $urlArray = parse_url($url);
        $urlArray['path'] = explode('/', $urlArray['path']);
        switch ($urlArray['scheme']) {
            case 'ftp':
            case 'http':
            case 'https':
            case 'file':
                $counter = 1;
                if ($baseFilename === null) {
                    $filename = Random::getAlphanumericString(32);
                } else {
                    $filename = $baseFilename;
                }

                while (file_exists("$destPath/$filename")) {
                    if ($baseFilename) {
                        $filename = "$counter-$baseFilename";
                        ++$counter;
                    } else {
                        $filename = Random::getAlphanumericString(32);
                    }
                }

                if (!$put_handle = fopen("$destPath/$filename", 'w+')) {
                    throw new \Exception("Could not open $destPath/$filename for writing");
                }

                if (!$get_handle = fopen($url, 'r')) {
                    throw new \Exception("Could not open $url for reading");
                }
                while (!feof($get_handle)) {
                    fwrite($put_handle, fgets($get_handle, 4096));
                }
                fclose($get_handle);
                fclose($put_handle);

                return "$destPath/$filename";
        }
        throw new \InvalidArgumentException(
            sprintf("Unsupported schema '%s'.", $urlArray['scheme'])
        );
    }

    /**
     * @param array $input
     *
     * @return array
     */
    protected function toUtf8(array $input)
    {
        // detect whether the input is UTF-8 or ISO-8859-1
        array_walk_recursive($input, function (&$value) {
            // will fail, if special chars are encoded to latin-1
            // $isUtf8 = (utf8_encode(utf8_decode($value)) == $value);

            // might have issues with encodings other than utf-8 and latin-1
            $isUtf8 = (mb_detect_encoding($value, 'UTF-8', true) !== false);
            if (!$isUtf8) {
                $value = utf8_encode($value);
            }

            return $value;
        });

        return $input;
    }
}
