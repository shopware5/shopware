<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Controllers
 * @subpackage ImportExport
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 * @author     Benjamin Cremer
 */

/**
 * Backend Controller for the Import/Export backend module
 */
class Shopware_Controllers_Backend_ImportExport extends Shopware_Controllers_Backend_ExtJs
{
	protected function initAcl()
	{
		$this->addAclPermission('exportCustomers', 'export', 'Insufficient Permissions');
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
     * @var string path to termporary uploaded file for import
     */
    protected $uploadedFilePath;

    /**
     * Exports list of customers as CSV
     */
    public function exportCustomersAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $metaDataFactory = Shopware()->Models()->getMetadataFactory();
        $billingAttributeFields = $metaDataFactory->getMetadataFor('Shopware\Models\Attribute\CustomerBilling')->getFieldNames();
        $billingAttributeFields = array_flip($billingAttributeFields);
        unset($billingAttributeFields['id']);
        unset($billingAttributeFields['customerBillingId']);
        $billingAttributeFields = array_flip($billingAttributeFields);

        $shippingAttributeFields = $metaDataFactory->getMetadataFor('Shopware\Models\Attribute\CustomerShipping')->getFieldNames();
        $shippingAttributeFields = array_flip($shippingAttributeFields);
        unset($shippingAttributeFields['id']);
        unset($shippingAttributeFields['customerShippingId']);
        $shippingAttributeFields = array_flip($shippingAttributeFields);

        $customerAttributeFields = $metaDataFactory->getMetadataFor('Shopware\Models\Attribute\Customer')->getFieldNames();
        $customerAttributeFields = array_flip($customerAttributeFields);
        unset($customerAttributeFields['id']);
        unset($customerAttributeFields['customerId']);
        $customerAttributeFields = array_flip($customerAttributeFields);

        $selectBillingAttributes = array();
        foreach ($billingAttributeFields as $field) {
            $selectBillingAttributes[] = 'billingAttribute.' . $field . ' as billing_attr_' . $field;
        }

        $selectShippingAttributes = array();
        foreach ($shippingAttributeFields as $field) {
            $selectShippingAttributes[] = 'shippingAttribute.' . $field . ' as shipping_attr_' . $field;
        }

        $selectCustomerAttributes = array();
        foreach ($customerAttributeFields as $field) {
            $selectCustomerAttributes[] = 'attribute.' . $field . ' as attr_' . $field;
        }

        $select = array(
            'billing.number as customernumber',
            'customer.email',
            'customer.hashPassword as password',
            'customer.hashPassword as md5_password',
            'billing.company as billing_company',
            'billing.department as billing_department',
            'billing.salutation as billing_salutation',
            'billing.firstName as billing_firstname',
            'billing.lastName as billing_lastname',
            'billing.street as billing_street',
            'billing.streetNumber as billing_streetnumber',
            'billing.zipCode as billing_zipcode',
            'billing.city as billing_city',
            'billing.phone',
            'billing.fax',
            'billing.countryId as billing_countryID',
            'billing.vatId as ustid',
        );

        $select = array_merge($select, $selectBillingAttributes);
        $select = array_merge($select, array(
            'shipping.company as shipping_company',
            'shipping.department as shipping_department',
            'shipping.salutation as shipping_salutation',
            'shipping.firstName as shipping_firstname',
            'shipping.lastName as shipping_lastname',
            'shipping.street as shipping_street',
            'shipping.streetNumber as shipping_streetnumber',
            'shipping.zipCode as shipping_zipcode',
            'shipping.city as shipping_city',
            'shipping.countryId as shipping_countryID',
        ));

        $select = array_merge($select, $selectShippingAttributes);
        $select = array_merge($select, array(
            'customer.paymentId as paymentID',
            'customer.newsletter ',
            'customer.affiliate ',
            'customer.groupKey as customergroup',
            'customer.languageIso as language',
            'customer.shopId as subshopID',
            'customer.email as email',
            'count(orders.id) as orders_count',
            'SUM(orders.invoiceAmount) as invoice_amount',
        ));
        $select = array_merge($select, $selectCustomerAttributes);

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select($select)
                ->from('\Shopware\Models\Customer\Customer', 'customer')
                ->join('customer.billing', 'billing')
                ->leftJoin('customer.shipping', 'shipping')
                ->leftJoin('customer.orders', 'orders', 'WITH', 'orders.status <> -1 AND orders.status <> 4' )
                ->leftJoin('billing.attribute', 'billingAttribute')
                ->leftJoin('shipping.attribute', 'shippingAttribute')
                ->leftJoin('customer.attribute', 'attribute')
                ->groupBy('customer.id');

        $builder->getQuery()->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($builder->getQuery());

        $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.customers.'.date("Y.m.d").'.csv"');
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $convert = new Shopware_Components_Convert_Csv();
        $first   = true;
        $keys    = array();
        foreach ($paginator as $row) {
            if ($first) {
                $first = false;
                $keys = array_keys($row);
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                echo $convert->_encode_line(array_combine($keys, $keys), $keys) . "\r\n";
            }

            $row['password'] = '';
            echo $convert->_encode_line($row, $keys) . "\r\n";
        }
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

        echo json_encode(array(
            'success' => false,
            'message' => sprintf("Exportformat '%s' invalid", $format)
        ));
        return;
    }

    /**
     * Export articles as XML file
     */
    protected function exportArticleXml()
    {
        $exportVariants  = (bool) $this->Request()->getParam('exportVariants', false);

        $limit  = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        /** @var \Shopware\Models\Article\Repository $articleRepostiory */
        $articleRepostiory = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');

        $builder = $articleRepostiory->createQueryBuilder('article');

        $builder->select(array(
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
                ))
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
            $builder->addSelect(array(
                 'details',
                 'detailsAttribute',
                 'detailsPrices',
            ));
            $builder->leftJoin('article.details', 'details', 'WITH', 'details.kind = 2')
                    ->leftJoin('details.prices', 'detailsPrices')
                    ->leftJoin('details.attribute', 'detailsAttribute');
        }

        $builder->setFirstResult($offset);
        $builder->setMaxResults($limit);

        $query = $builder->getQuery();
        $query = $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

        $result = $paginator->getIterator()->getArrayCopy();
        foreach ($result as $key => &$article) {
            foreach ($result[$key]['details'] as &$variant) {
                $variant['prices'] = $this->prepareXmlArray($variant['prices'], 'price');
            }
            $article['variants'] = $this->prepareXmlArray($article['details'], 'variant');
            unset($article['details']);

            $article['categories'] = $this->prepareXmlArray($article['categories'], 'category');
            $article['related']    = $this->prepareXmlArray($article['related'], 'related');
            $article['similar']    = $this->prepareXmlArray($article['similar'], 'similar');

            $article['mainDetail']['prices'] = $this->prepareXmlArray($article['mainDetail']['prices'], 'pricee');

        }

        array_walk_recursive($result, function (&$value) {
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
        });

        $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.articles.'.date("Y.m.d").'.xml"');
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $convert = new Shopware_Components_Convert_Xml();
        $xmlmap = array("shopware" => array("articles" => array("article" => $result)));

        echo $convert->encode($xmlmap);
        return;
    }

    /**
     * @param $format
     */
    protected function exportArticlesFlat($format)
    {
        $exportVariants  = (bool) $this->Request()->getParam('exportVariants', false);

        $exportArticleTranslations  = (bool) $this->Request()->getParam('exportArticleTranslations', false);
        $exportCustomergroupPrices  = (bool) $this->Request()->getParam('exportCustomergroupPrices', false);

        $limit  = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $metaDataFactory = Shopware()->Models()->getMetadataFactory();
        $attributeFields = $metaDataFactory->getMetadataFor('Shopware\Models\Attribute\Article')->getFieldNames();
        $attributeFields = array_flip($attributeFields);
        unset($attributeFields['id']);
        unset($attributeFields['articleDetailId']);
        unset($attributeFields['articleId']);
        $attributeFields = array_flip($attributeFields);

        $selectAttributes = array();
        foreach ($attributeFields as $field) {
            $selectAttributes[] = 'at.' . $field . ' as attr_' . $field;
        }
        $selectAttributes = implode(",\n", $selectAttributes);

        $joinStatements = '';
        $selectStatements = array();

        if ($exportArticleTranslations)
        {
            $sql = '
                SELECT DISTINCT isocode
                FROM s_core_multilanguage
                WHERE skipbackend=0
	        ';

            $languages = Shopware()->Db()->fetchCol($sql);

            $translationFields = array(
                "txtArtikel"          => "name",
                "txtzusatztxt"        => "additionaltext",
                "txtshortdescription" => "description",
                "txtlangbeschreibung" => "description_long"
            );

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
            $sql = "SELECT `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC";
        } else {
            $sql = "SELECT `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE `groupkey`='EK' AND mode=0";
        }
        $customergroups = Shopware()->Db()->fetchAll($sql) ;

        $sqlAddSelectP = '';
        if (!empty($customergroups)) {
            foreach ($customergroups as $cg) {
                if ($cg['groupkey']=='EK') {
                    $cg['id'] = '';
                }

                $joinStatements[] = "
                    LEFT JOIN `s_articles_prices` p{$cg['id']}
                    ON p{$cg['id']}.articledetailsID=d.id
                    AND p{$cg['id']}.pricegroup='{$cg['groupkey']}'
                    AND p{$cg['id']}.`from`=1
                ";

                if (empty($cg['taxinput'])) {
                    $sqlAddSelectP .= "REPLACE(ROUND(p{$cg['id']}.price,2),'.',',')";
                } else {
                    $sqlAddSelectP .= "REPLACE(ROUND(p{$cg['id']}.price*(100+t.tax)/100,2),'.',',')";
                }

                if ($cg['groupkey']=='EK') {
                    $sqlAddSelectP .= " as price, ";
                } else {
                    $sqlAddSelectP .= " as price_{$cg['groupkey']}, ";
                }
            }
        }

        $joinStatements = implode(" \n ", $joinStatements);

        if ($exportVariants) {
            $variantsSql = "
            INNER JOIN s_articles_details d
            ON d.articleID = a.id AND d.kind <> 3

            INNER JOIN s_articles_details d2
            ON d2.id = a.main_detail_id
            ";
        } else {
            $variantsSql = "
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1

            INNER JOIN s_articles_details d2
            ON d2.id = a.main_detail_id
            ";
        }

        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $imagePath = 'http://'. $shop->getHost() . $shop->getBasePath()  . '/media/image/';

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
                REPLACE(ROUND(p.baseprice,2),'.',',') as baseprice,
                a.active,
                d.instock,
                d.stockmin,
                a.description,
                a.description_long,
                a.shippingtime,
                IF(a.datum='0000-00-00','',a.datum) as added,
                IF(a.changetime='0000-00-00 00:00:00','',a.changetime) as `changed`,
                IF(d.releasedate='0000-00-00','',d.releasedate) as releasedate,
                d.shippingfree,
                a.topseller,
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
                d.impressions,
                d.sales,
                IF(e.file IS NULL,0,1) as esd,
                d.weight,
                d.width,
                d.height,
                d.length,
                d.ean,
                u.unit,
                (SELECT GROUP_CONCAT(relatedarticle SEPARATOR '|') FROM s_articles_similar WHERE articleID=a.id) as similar,
                (SELECT GROUP_CONCAT(relatedarticle SEPARATOR '|') FROM s_articles_relationships WHERE articleID=a.id) as crosselling,
            	(SELECT GROUP_CONCAT(categoryID SEPARATOR '|') FROM s_articles_categories WHERE articleID=a.id) as categories,
                (
                    SELECT GROUP_CONCAT(CONCAT('{$imagePath}',img,'.',extension) ORDER BY `main`,  `position`  SEPARATOR '|')
                    FROM `s_articles_img`
                    WHERE articleID=a.id
                ) as images,

                a.filtergroupID as attributegroupID,
                '' as attributevalues,

                {$selectAttributes},
                acs.type as configuratortype,
            	IF(acs.id,1,NULL) as configurator
            	{$selectStatements}

            FROM s_articles a

            {$variantsSql}

            INNER JOIN s_articles_attributes at
            ON at.articledetailsID=d.id

            LEFT JOIN `s_core_units` as u
            ON d.unitID = u.id

            LEFT JOIN s_core_tax as t
            ON a.taxID = t.id

            LEFT JOIN s_articles_supplier as s
            ON a.supplierID = s.id

            LEFT JOIN s_articles_esd e
            ON e.articledetailsID=d.id

            LEFT JOIN s_article_configurator_sets acs ON a.configurator_set_id = acs.id

            {$joinStatements}

            WHERE a.mode = 0
            ORDER BY a.id, d.kind, d.id

            LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);

        if ($format === 'csv') {
            $filename = 'export.articles.'.date("Y.m.d").'.csv';
            $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $convert = new Shopware_Components_Convert_Csv();
            $first   = true;
            $keys    = array();
            while ($row = $stmt->fetch()) {
                if ($exportArticleTranslations) {
                    $row = $this->prepareArticleRow($row, $languages, $translationFields);
                }

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
            $filename = 'export.articles.'.date("Y.m.d").'.xls';
            $title = 'Articles Export';

            $this->Response()->setHeader('Content-Type', 'application/vnd.ms-excel;charset=UTF-8');
            $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $excel = new Shopware_Components_Convert_Excel();
            $excel->setTitle($title);

            $first = true;
            echo $excel->getHeader();
            while ($row = $stmt->fetch()) {
                if ($exportArticleTranslations) {
                    $row = $this->prepareArticleRow($row, $languages, $translationFields);
                }

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
     * @param array $row
     * @param array $languages
     * @param array $translationFields
     * @return array
     */
    public function prepareArticleRow($row, $languages, $translationFields)
    {
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
                    foreach ($objectdata as $key=>$value) {
                        if(isset($translationFields[$key])) {
                            $row[$translationFields[$key] . '_' . $language] = $value;
                        }
                    }
                }
            }

            foreach ($languages as $language) {
                unset($row['article_translation_'.$language]);
                unset($row['detail_translation_'.$language]);
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

        $limit  = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $exportVariants  = (bool) $this->Request()->getParam('exportVariants', false);
        if ($exportVariants) {
            $variantsSql = "
            INNER JOIN s_articles_details d
                ON d.articleID=a.id AND d.kind <> 3
            ";
        } else {
            $variantsSql = "
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1
            ";
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

            ORDER BY a.id, d.kind, ordernumber

            LIMIT {$offset},{$limit}
        ";

        $stmt = Shopware()->Db()->query($sql);
        $this->sendCsv($stmt, 'export.instock.'.date("Y.m.d").'.csv');
    }

    /**
     * Exports articles with no stock
     */
    public function exportNotInStockAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $limit  = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $exportVariants  = (bool) $this->Request()->getParam('exportVariants', false);
        if ($exportVariants) {
            $variantsSql = "
            INNER JOIN s_articles_details d
                ON d.articleID=a.id AND d.kind <> 3
            ";
        } else {
            $variantsSql = "
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1
            ";
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
        $this->sendCsv($stmt, 'export.noinstock.'.date("Y.m.d").'.csv');
    }

    /**
     * Exports article-prices as CSV
     */
    public function exportPricesAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $limit  = $this->Request()->getParam('limit', 500000);
        if (!is_numeric($limit)) {
            $limit = 500000;
        }

        $offset = $this->Request()->getParam('offset', 0);
        if (!is_numeric($offset)) {
            $offset = 0;
        }

        $exportVariants  = (bool) $this->Request()->getParam('exportVariants', false);
        if ($exportVariants) {
            $variantsSql = "
            INNER JOIN s_articles_details d
                ON d.articleID=a.id AND d.kind <> 3
            ";
        } else {
            $variantsSql = "
            INNER JOIN s_articles_details d
            ON d.id = a.main_detail_id AND d.kind = 1
            ";
        }
        $sql = "
            SELECT
            d.ordernumber as ordernumber,
            REPLACE(ROUND(p.price*(100+t.tax)/100,2),'.',',') as price,
            p.pricegroup as pricegroup,
            IF(p.`from`=1,NULL,p.`from`) as `from`,
            REPLACE(ROUND(p.pseudoprice*(100+t.tax)/100,2),'.',',') as pseudoprice,
            REPLACE(ROUND(p.baseprice,2),'.',',') as baseprice,
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
        $this->sendCsv($stmt, 'export.prices.'.date("Y.m.d").'.csv');
    }

    /**
     * Exports newsletter subscriptions as CSV
     */
    public function exportNewsletterAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $sql = "
            SELECT
            cm.email,
            cg.name as `group`,
            IFNULL(ub.salutation, nd.salutation) as salutation,
            IFNULL(ub.firstname, nd.firstname) as firstname,
            IFNULL(ub.lastname, nd.lastname) as lastname,
            IFNULL(ub.street, nd.street) as street,
            IFNULL(ub.streetnumber, nd.streetnumber) as streetnumber,
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
        ";

        $stmt = Shopware()->Db()->query($sql);
        $this->sendCsv($stmt, 'export.newsletter.'.date("Y.m.d").'.csv');
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
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.'.date("Y.m.d").'.xls"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $excel = new Shopware_Components_Convert_Excel();
            $excel->setTitle('Categories Export');
            $excel->addRow(array_keys(reset($categories)));
            $excel->addArray($categories);
            echo $excel->getAll();
        }

        if ($format === 'xml') {
            $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.'.date("Y.m.d").'.xml"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            $convert = new Shopware_Components_Convert_Xml();
            $xmlmap = array("shopware" => array("categories" => array("category" => $categories)));
            echo $convert->encode($xmlmap);
        }

        if ($format === 'csv') {
            $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.'.date("Y.m.d").'.csv"');
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

        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $path = 'http://'. $shop->getHost() . $shop->getBasePath()  . '/media/image/';

        $sqlDetail = "
            SELECT
                d.ordernumber,
                CONCAT('$path', ai.img,'.',ai.extension) as image,
                ai.main,
                ai.description,
                ai.position,
                ai.width,
                ai.height,
                GROUP_CONCAT(CONCAT(im.id, '|', mr.option_id, '|' , co.name)) as rules

            FROM s_articles_img ai
            INNER JOIN s_articles a ON ai.articleId = a.id
            INNER JOIN s_articles_details d ON a.main_detail_id = d.id

            LEFT JOIN s_article_img_mappings im ON im.image_id = ai.id
            LEFT JOIN s_article_img_mapping_rules mr ON mr.mapping_id = im.id
            LEFT JOIN s_article_configurator_options co ON mr.option_id = co.id

            WHERE ai.parent_id is NULL
            AND ai.article_detail_id IS NULL
            GROUP BY ai.id
            ORDER BY mr.mapping_id
        ";

        $stmt = Shopware()->Db()->query($sqlDetail);
        $result = $stmt->fetchAll();

        foreach ($result as &$image) {
            if (empty($image['rules'])) {
                continue;
            }

            $rules = explode(',', $image['rules']);

            $out = array();
            foreach($rules as $rule) {
                $split = explode('|', $rule);
                $ruleId   = $split[0];
                $optionId = $split[1];
                $name     = $split[2];

                $out[$ruleId][] = $name;
            }

            $rules = '';
            foreach ($out as $group) {
                $rules .= "(" . implode(',', $group) . ")";
            }

            $image['rules'] = $rules;
        }

        $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.images.'.date("Y.m.d").'.csv"');
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

        /** @var \Shopware\Models\Order\Repository $repository  */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
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
            $selectFields = array(
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
            );
        }

        if ($format == 'csv' || $format == 'excel') {
            $selectFields = array(
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

                'orders.referer as referer',
                'orders.clearedDate as cleareddate',
                'orders.trackingCode as trackingcode',
                'orders.languageIso as language',
                'orders.currency as currency',

                // dummy for count positions
                'orders.id as count_positions',

                'details.id as orderdetailsID',
                'details.articleId as articleID',
                'details.articleNumber as articleordernumber',
                'details.articleName as name',
                'details.price as price',
                'details.quantity as quantity',

                'details.price * details.quantity as invoice',

                'details.releaseDate as releasedate',
                'taxes.tax as tax',
                'details.esdArticle as esd',
                'details.mode as modus',

                'customerBilling.number as customernumber',

                'billing.company as billing_company',
                'billing.department as billing_department',
                'billing.salutation as billing_salutation',
                'billing.firstName as billing_firstname',
                'billing.lastName as billing_lastname',
                'billing.street as billing_street',
                'billing.streetNumber as billing_streetnumber',
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
                'shipping.streetNumber as shipping_streetnumber',
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
            );
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

        $query  = $builder->getQuery();
        $result = $query->getArrayResult();

        $updateStateId = $this->Request()->getParam('updateOrderstate');
        if (!empty($updateStateId)) {
            $orderIds = array();

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
            $builder->select(array(
                'count(details.id) as count_positions',
            ));

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
                $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.categories.'.date("Y.m.d").'.xls"');
                $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

                $excel = new Shopware_Components_Convert_Excel();
                $excel->setTitle('Orders Export');
                $excel->addRow(array_keys(reset($result)));
                $excel->addArray($result);
                echo $excel->getAll();
            }

            if ($format == 'csv') {
                $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
                $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.orders.'.date("Y.m.d").'.csv"');
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

            $orders = array("shopware" => array("orders" => array("order"=> $result)));
            $convert = new Shopware_Components_Convert_Xml();

            $this->Response()->setHeader('Content-Type', 'text/xml;charset=utf-8');
            $this->Response()->setHeader('Content-Disposition', 'attachment; filename="export.orders.'.date("Y.m.d").'.xml"');
            $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

            echo $convert->encode($orders);
        }
    }

    /**
     * Import snippet action
     */
    public function importAction()
    {
        @set_time_limit(0);
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $type = strtolower(trim($this->Request()->getParam('type')));
        if (!$type) {
            echo json_encode(array(
                'success' => false,
                'message' => "No Importtype given",
            ));
            return;
        }

        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(array(
                'success' => false,
                'message' => "Could not upload file",
            ));
            return;
        }

        $fileName  = basename($_FILES['file']['name']);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($extension, array('csv', 'xml'))) {
            echo json_encode(array(
                'success' => false,
                'message' => 'Unknown Extension',
            ));
            return;
        }

        $destPath = Shopware()->DocPath('media_' . 'temp');
        if (!is_dir($destPath)) {
            // Try to create directory with write permissions
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);
        if (!file_exists($destPath)) {
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Destination directory '%s' does not exist.", $destPath),
            ));
            return;
        }

        if (!is_writable($destPath)) {
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Destination directory '%s' does not have write permissions.", $destPath)
            ));
            return;
        }

        $filePath = tempnam($destPath, 'import_');

        if (false === move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Could not move %s to %s.", $_FILES['file']['tmp_name'], $filePath)
            ));
            return;
        }
        $this->uploadedFilePath = $filePath;
        chmod($filePath, 0644);

        if ($type === 'instock') {
            $this->importInStock($filePath);
            return;
        }

        if ($type === 'customers') {
            $this->importCustomers($filePath);
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

        echo json_encode(array(
            'success' => false,
            'message' => sprintf("Could not handle upload of type: %s.", $type)
        ));
        return;
    }

    /**
     * @param $filePath
     */
    public function importImages($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $counter = 0;
        $total = 0;
        $errors = array();

        /** @var \Shopware\Models\Article\Repository $articleDetailRepostiory */
        $articleDetailRepostiory = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

        foreach ($results as $imageData) {
            if (empty($imageData['ordernumber']) || empty($imageData['image'])) {
                continue;
            }
            $counter++;

            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $imageData['ordernumber']));
            if (!$articleDetailModel) {
                continue;
            }

            /** @var \Shopware\Models\Article\Article $article */
            $article = $articleDetailModel->getArticle();

            $image = new \Shopware\Models\Article\Image();

            try {
                $path = $this->load($imageData['image']);
            } catch (\Exception $e) {
                $errors[] = "Could load image";
                continue;
            }

            $file = new \Symfony\Component\HttpFoundation\File\File($path);

            $media = new \Shopware\Models\Media\Media();
            $media->setAlbumId(-1);
            $media->setAlbum(Shopware()->Models()->find('Shopware\Models\Media\Album', -1));

            $media->setFile($file);
            $media->setDescription('');
            $media->setCreated(new \DateTime());
            $media->setUserId(0);

            try { //persist the model into the model manager
                Shopware()->Models()->persist($media);
                Shopware()->Models()->flush();
            }
            catch (\Doctrine\ORM\ORMException $e) {
                $errors[] = "Could not move image";
                continue;
            }

            if (!empty($imageData['main'])) {
                $imageData['main'] = 1;
            }

            $image->setArticle($article);
            $image->setDescription($imageData['description']);
            $image->setPosition($imageData['position']);
            $image->setPath($media->getName());
            $image->setExtension($media->getExtension());
            $image->setMedia($media);

            Shopware()->Models()->persist($image);
            Shopware()->Models()->flush($image);

            $total++;
        }

        if (!empty($errors)) {
            $message = implode("<br>\n", $errors);
                echo json_encode(array(
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'message' => sprintf("Successfully uploaded %s of %s Images", $total, $counter)
        ));

        return;
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
            $headers = $results->getHeader();
        }

        /** @var \Shopware\Models\Category\Repository $categoryRepository  */
        $categoryRepository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');
        $metaData = Shopware()->Models()->getMetadataFactory()->getMetadataFor('Shopware\Models\Category\Category');

        // $originalIdGenerator = $metaData->idGenerator;
        $metaData->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $counter = 0;
        $total = 0;

        $models = array();

        foreach ($results as $category) {
            $counter++;
            $total++;

            $category = (array) $category;
            $category['id']     = $category['categoryID'];
            $category['parent'] = $categoryRepository->find($category['parentID']);
            $models[] = $this->saveCategory($category, $categoryRepository, $metaData);
            Shopware()->Models()->flush();
        }

        try {
            Shopware()->Models()->flush();
            Shopware()->Models()->getRepository('Shopware\Models\Category\Category')->recover();
        } catch (\Exception $e) {
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Error: %s", $e->getMessage())
            ));
            return;
        }

        $categoryIds = array();

        /** @var $categoryModel \Shopware\Models\Category\Category */
        foreach ($models as $categoryModel) {
            $categoryIds[] = $categoryModel->getId();
        }

        echo json_encode(array(
            'success' => true,
            'message' => sprintf("Successfully updated %s of %s categories", $total, $counter)
        ));

        return;
    }

    /**
     * @param array $category
     * @param \Shopware\Models\Category\Repository $categoryRepository
     * @param $metaData
     * @return Shopware\Models\Category\Category
     */
    public function saveCategory($category, \Shopware\Models\Category\Repository $categoryRepository, $metaData)
    {
        $updateData = array();

        $mapping = array();
        foreach ($metaData->fieldMappings as $fieldMapping) {
            $mapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $updateData = $this->mapFields($category, $mapping);
        $updateData['parent'] = $updateData['parentId'];

        $attribute = $this->prefixToArray($category, 'attribute_');
        if (!empty($attribute)) {
            $updateData['attribute'] = $attribute;
        }

        /** @var $categoryModel \Shopware\Models\Category\Category */
        $categoryModel = $categoryRepository->find($category['id']);

        if (!$categoryModel) {
            $categoryModel = new \Shopware\Models\Category\Category();
            $categoryModel->setPrimaryIdentifier($category['id']);
            Shopware()->Models()->persist($categoryModel);
        }

        $categoryModel->fromArray($updateData);

        return $categoryModel;
    }

    /**
     * @param $filePath
     */
    public function importNewsletter($filePath)
    {
        $newsletterRepository = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Address');
        $newsletterGroupRepository = Shopware()->Models()->getRepository('Shopware\Models\Newsletter\Group');

        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $insertCount = 0;
        $updateCount = 0;

        $errors = array();

        $emailValidator = new Zend_Validate_EmailAddress();

        foreach ($results as $newsletterData) {
            if (empty($newsletterData['email'])) {
                continue;
            }

            if (!$emailValidator->isValid($newsletterData['email'])) {
                continue;
            }

            if ($newsletterData['group']) {
                $group = $newsletterGroupRepository->findOneBy(array('name' => $newsletterData['group']));
            }

            $existingRecipient = $newsletterRepository->findOneBy(array('email' => $newsletterData['email']));
            if (!$existingRecipient) {
                $recipient = new Shopware\Models\Newsletter\Address();
                $recipient->setEmail($newsletterData['email']);
                $recipient->setIsCustomer(false);

                if ($group) {
                    $recipient->setGroup($group);
                }
                Shopware()->Models()->persist($recipient);
                Shopware()->Models()->flush();

                $insertCount++;
            }

            if ($group && !empty($newsletterData['firstname'])) {
                $sql = "INSERT INTO s_campaigns_maildata (groupId, email, firstname, lastname) VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE groupId = ?, email = ?, firstname = ?, lastname = ?";

                $values = array(
                    $group->getId(),
                    $newsletterData['email'],
                    $newsletterData['firstname'],
                    $newsletterData['lastname'],
                );

                Shopware()->Db()->query($sql, array_merge(array_values($values), array_values($values)));

                if ($existingRecipient) {
                    $updateCount++;
                }
            }
        }

        if (!empty($errors)) {
            $message = implode("<br>\n", $errors);
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'message' => sprintf("Imported: %s. Updated: %s.", $insertCount, $updateCount)
        ));

        return;
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
            $counter++;

            $result = Shopware()->Db()->update(
                's_articles_details',
                array('instock' => (int) $articleData['instock']),
                array('ordernumber = ?' => $articleData['ordernumber'])
            );

            $total += $result;
        }

        echo json_encode(array(
            'success' => true,
            'message' => sprintf("Successfully updated %s of %s artticles", $total, $counter)
        ));
        return;
    }

    /**
     * @param string $filePath
     */
    public function importPrices($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $counter = 0;
        $total = 0;

        $sql = "SELECT `groupkey` as `key`, `id`, `groupkey`, `taxinput` FROM s_core_customergroups WHERE mode=0 ORDER BY id ASC";
        $customergroups = Shopware()->Db()->fetchAssoc($sql);

        foreach ($results as $articleData) {
            if (empty($articleData['ordernumber'])) {
                continue;
            }

            $counter++;

            $sql = "SELECT id as detailId, ordernumber, articleId FROM s_articles_details WHERE ordernumber = ?";
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

            $articleData['price']       = floatval(str_replace(',' , '.', $articleData['price']));
            $articleData['pseudoprice'] = floatval(str_replace(',' , '.', $articleData['pseudoprice']));
            $articleData['baseprice']   = floatval(str_replace(',' , '.', $articleData['baseprice']));

            if (!empty($customergroups[$articleData['pricegroup']]['taxinput'])) {
                $articleData['price'] = $articleData['price']/(100+$tax)*100;

                if (isset($articleData['pseudoprice'])) {
                    $articleData['pseudoprice'] = $articleData['pseudoprice']/(100+$tax)*100;
                } else {
                    $articleData['pseudoprice'] = 0;
                }
            }

            if (isset($articleData['baseprice'])) {
                $articleData['baseprice'] = $this->sValFloat($articleData['baseprice']);
            } else {
                $articleData['baseprice'] = 0;
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

            Shopware()->Db()->delete('s_articles_prices', array(
                'pricegroup = ?'                => $articleData['pricegroup'],
                'articledetailsID = ?'          => $article['detailId'],
                'CAST(`from` AS UNSIGNED) >= ?' => $articleData['from']
            ));

            if ($articleData['from'] != 1) {
                Shopware()->Db()->update(
                    's_articles_prices',
                    array('to' => $articleData['from'] - 1),
                    array(
                        'pricegroup = ?'       => $articleData['pricegroup'],
                        'articleId = ?'        => $article['articleId'],
                        'articledetailsID = ?' => $article['detailId'],
                        '`to` LIKE ?'          => 'beliebig',
                    )
                );
            }

            Shopware()->Db()->insert('s_articles_prices', array(
                'articleID'        => $article['articleId'],
                'articledetailsID' => $article['detailId'],
                'pricegroup'       => $articleData['pricegroup'],
                'from'             => $articleData['from'],
                'to'               => 'beliebig',
                'price'            => $articleData['price'],
                'pseudoprice'      => $articleData['pseudoprice'],
                'baseprice'        => $articleData['baseprice'],
                'percent'          => $articleData['percent'],
            ));
            $total++;
        }

        echo json_encode(array(
            'success' => true,
            'message' => sprintf("Successfully updated %s of %s artticles", $total, $counter)
        ));

        return;
    }

    /**
     * Imports customers from XML file
     * @param string $filePath
     */
    protected function importArticlesXml($filePath)
    {
        $xml      = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
        $results  = $xml->articles;
        $results  = $this->simplexml2array($results);

        /** @var \Shopware\Models\Article\Repository $articleRepostiory */
        $articleRepostiory = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');

        /** @var \Shopware\Components\Api\Resource\Article $articleResource */
        $articleResource = \Shopware\Components\Api\Manager::getResource('article');

        $counter = 0;
        $results = $this->prepareImportXmlData($results['article']);

        Shopware()->Models()->getConnection()->beginTransaction(); // suspend auto-commit

        foreach ($results as $article) {
            if (empty($article['id'])) {
                continue;
            }

            try {
                $counter++;
                $articleModel = $articleRepostiory->find($article['id']);
                if (!$articleModel) {
                    continue;
                }

                if (isset($article['similar'])) {
                    $article['similar'] = $this->prepareImportXmlData($article['related']['similar']);
                }

                if (isset($article['related'])) {
                    $article['related'] = $this->prepareImportXmlData($article['related']['related']);
                }

                if (isset($article['categories'])) {
                    $article['categories'] = $this->prepareImportXmlData($article['categories']['category']);
                }

                if (isset($article['variants'])) {
                    $article['variants'] = $this->prepareImportXmlData($article['variants']['variant']);
                    foreach ($article['variants'] as $key => $variant) {
                        if (isset($variant['prices'])) {
                            $article['variants'][$key]['prices'] = $this->prepareImportXmlData($variant['prices']['price']);
                        }
                    }
                }

                if (isset($article['mainDetail']['prices'])) {
                    $article['mainDetail']['prices'] = $this->prepareImportXmlData($article['mainDetail']['prices']['price']);
                }

                $article = $this->array_filter_recursive($article);
                $result  = $articleResource->update($articleModel->getId(), $article);
                if ($result) {
                    $articleIds[] = $result->getId();
                    if (($counter % 5) == 0) {
                        Shopware()->Models()->clear();
                    }
                }
            } catch (\Exception $e) {
                if ($e instanceof Shopware\Components\Api\Exception\ValidationException) {
                    $messages = array();
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

                $errors[] = "Error in line {$counter}: $errormessage";
            }
        }

        if (!empty($errors)) {
            Shopware()->Models()->getConnection()->rollback();
            $message = implode("<br>\n", $errors);
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ));
            return;
        }

        Shopware()->Models()->getConnection()->commit();

        echo json_encode(array(
        'success' => true,
        'message' => sprintf("Successfully saved: %s", count($articleIds))
        ));
    }

    /**
     * Imports customers from CSV file
     * @param string $filePath
     */
    protected function importArticlesCsv($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $articleIds = array();
        $errors = array();
        $counter = 0;

        /** @var \Shopware\Components\Api\Resource\Article $articleResource */
        $articleResource = \Shopware\Components\Api\Manager::getResource('article');

        /** @var \Shopware\Models\Article\Repository $articleRepostiory */
        $articleRepostiory = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');

        /** @var \Shopware\Models\Article\Repository $articleDetailRepostiory */
        $articleDetailRepostiory = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail');

        $articleMetaData       = Shopware()->Models()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Article');
        $articleDetailMetaData = Shopware()->Models()->getMetadataFactory()->getMetadataFor('Shopware\Models\Article\Detail');


        $articleMapping = array();
        foreach ($articleMetaData->fieldMappings as $fieldMapping) {
            $articleMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        $articleDetailMapping = array();
        foreach ($articleDetailMetaData->fieldMappings as $fieldMapping) {
            $articleDetailMapping[$fieldMapping['columnName']] = $fieldMapping['fieldName'];
        }

        Shopware()->Models()->getConnection()->beginTransaction(); // suspend auto-commit

        foreach ($results as $articleData) {
            try {
                $counter++;
                $result = $this->saveArticle($articleData, $articleResource, $articleRepostiory, $articleDetailRepostiory, $articleMapping, $articleDetailMapping);
                if ($result) {
                    $articleIds[] = $result->getId();
                    if (($counter % 5) == 0) {
                        Shopware()->Models()->clear();
                    }
                }
            } catch (\Exception $e) {
                if ($e instanceof Shopware\Components\Api\Exception\ValidationException) {
                    $messages = array();
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

                $errors[] = "Error in line {$counter}: $errormessage";
            }
        }

        if (!empty($errors)) {
            Shopware()->Models()->getConnection()->rollback();
            $message = implode("<br>\n", $errors);
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ));
            return;
        }

        Shopware()->Models()->getConnection()->commit();

        echo json_encode(array(
             'success' => true,
             'message' => sprintf("Successfully saved: %s", count($articleIds))
        ));
    }

    /**
     * @param array $articleData
     * @param $articleResource
     * @param $articleRepostiory
     * @param $articleDetailRepostiory
     * @param array $articleMapping
     * @param array $articleDetailMapping
     * @return \Shopware\Models\Article\Article
     */
    protected function saveArticle($articleData, $articleResource, $articleRepostiory, $articleDetailRepostiory, $articleMapping, $articleDetailMapping)
    {
        $importImages = false;

        if (empty($articleData['ordernumber']) && empty($articleData['articleID']) && empty($articleData['articledetailsID'])) {
            return false;
        }

        $updateData = $this->mapFields($articleData, $articleMapping, array('taxId', 'tax', 'supplierId', 'supplier'));
        $detailData = $this->mapFields($articleData, $articleDetailMapping);

        if (!empty($articleData['price'])) {
            $detailData['prices'] = array(array(
                'price' => $articleData['price'],
            ));
        }

        if ($importImages && !empty($articleData['images'])) {
            $images = explode('|', $articleData['images']);
            foreach ($images as $imageLink) {
                $updateData['images'][] = array('link' => $imageLink);
            }
        }

        if (!empty($articleData['categories'])) {
            $categories = explode('|', $articleData['categories']);
            foreach ($categories as $categoryId) {
                $updateData['categories'][] = array('id' => $categoryId);
            }
        }

        if (!empty($articleData['similar'])) {
            $similars = explode('|', $articleData['similar']);
            foreach ($similars as $similarId) {
                $updateData['similar'][] = array('id' => $similarId);
            }
        }

        if (!empty($articleData['crosselling'])) {
            $crossSellings = explode('|', $articleData['crosselling']);
            foreach ($crossSellings as $crosssellingId) {
                $updateData['related'][] = array('id' => $crosssellingId);
            }
        }

        if (!empty($articleData['ordernumber'])) {
            unset($articleData['articleID'], $articleData['articledetailsID']);
        }

        $attribute = $this->prefixToArray($articleData, 'attr_');
        if (!empty($attribute)) {
            $detailData['attribute'] = $attribute;
        }

        if (!empty($articleData['articledetailsID'])) {
            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->find($articleData['articledetailsID']);
            if (!$articleDetailModel) {
                return;
            }

            /** @var \Shopware\Models\Article\Article $articleModel */
            $articleModel = $articleDetailModel->getArticle();

        } elseif (!empty($articleData['ordernumber'])) {
            /** @var \Shopware\Models\Article\Detail $articleDetailModel */
            $articleDetailModel = $articleDetailRepostiory->findOneBy(array('number' => $articleData['ordernumber']));
            if ($articleDetailModel) {

                /** @var \Shopware\Models\Article\Article $articleModel */
                $articleModel = $articleDetailModel->getArticle();
            }

        } elseif (!empty($articleData['articleID'])) {
            /** @var \Shopware\Models\Article\Article $articleModel */
            $articleModel = $articleRepostiory->find($articleData['articleID']);
            if (!$articleModel) {
                return;
            }

            $articleDetailModel = $articleModel->getMainDetail();
        }

        if ($articleModel) {
            if ($articleDetailModel->getKind() == 1) {
                $updateData['mainDetail'] = $detailData;
            } elseif ($articleDetailModel->getKind() == 2) {
                $detailData['id'] = $articleDetailModel->getId();

                $updateData = array();
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
     * Imports customers from CSV file
     * @param string $filePath
     */
    protected function importCustomers($filePath)
    {
        $results = new Shopware_Components_CsvIterator($filePath, ';');

        $customerIds = array();
        $errors = array();
        $counter = 0;

        Shopware()->Models()->getConnection()->beginTransaction(); // suspend auto-commit

        foreach ($results as $customerData) {
            try {
                $counter++;
                $result = $this->saveCustomer($customerData);
                if ($result) {
                    $customerIds[] = $result->getId();
                } else {
                }


            } catch (\Exception $e) {
                if ($e instanceof Shopware\Components\Api\Exception\ValidationException) {
                    $messages = array();
                    /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
                    foreach ($e->getViolations() as $violation) {
                        $messages[] = sprintf(
                            '%s: %s',
                            $violation->getPropertyPath(),
                            $violation->getMessage()
                        );
                    }

                    $errormessage = implode("\n", $messages);
                } else {
                    $errormessage = $e->getMessage();
                }

                $errors[] = "Error in line {$counter}: $errormessage";
            }
        }

        if (!empty($errors)) {
            Shopware()->Models()->getConnection()->rollback();
            $message = implode("<br>\n", $errors);
            echo json_encode(array(
                'success' => false,
                'message' => sprintf("Errors: $message"),
            ));
            return;
        } else {
            Shopware()->Models()->getConnection()->commit();
        }

        echo json_encode(array(
            'success' => true,
            'message' => sprintf("Successfully saved: %s", count($customerIds))
        ));
    }

    /**
     * @param array $customerData
     * @return Shopware\Models\Customer\Customer
     */
    protected function saveCustomer($customerData)
    {
        /** @var \Shopware\Models\Customer\Repository $customerRepository */
        $customerRepository = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');

        /** @var \Shopware\Components\Api\Resource\Customer $customerResource */
        $customerResource = \Shopware\Components\Api\Manager::getResource('customer');

        if (empty($customerData['userID']) && empty($customerData['email'])) {
            return false;
        }

        if (!empty($customerData['userID'])) {
            /** \Shopware\Models\Customer\Customer $customerModel */
            $customerModel = $customerRepository->find($customerData['userID']);
            if (!$customerModel) {
                return false;
            }
        } elseif (!empty($customerData['email']) && empty($customerData['subshopID'])) {
            /** \Shopware\Models\Customer\Customer $customerModel */
            $customerModel = $customerRepository->findOneBy(array('email' => $customerData['email'], 'shopId' => $customerData['subshopID']));

        } elseif (!empty($customerData['email'])) {
            /** \Shopware\Models\Customer\Customer $customerModel */
            $customerModel = $customerRepository->findOneBy(array('email' => $customerData['email']));
        }

        if (!$customerModel) {
            /** \Shopware\Models\Customer\Customer $customerModel */
            $customerModel = new \Shopware\Models\Customer\Customer();
        }

        $customerData = $this->prepareCustomerData($customerData);

        if ($customerModel->getId() > 0) {
            $result = $customerResource->update($customerModel->getId(), $customerData);
        } else {
            $result = $customerResource->create($customerData);
        }

        return $result;
    }

    /**
     * @param array $customerData
     * @return array
     */
    public function prepareCustomerData($customerData)
    {
        $customerMapping = array(
            'customergroup' => 'groupKey',
            'md5_password'  => 'rawPassword',
            'phone'         => 'billing_phone',
            'fax'           => 'billing_fax',
        );
        $customerData = $this->mapFields($customerData, $customerMapping) + $customerData;

        $attribute = $this->prefixToArray($customerData, 'attr_');
        if (!empty($attribute)) {
            $customerData['attribute'] = $attribute;
        }

        $billing = $this->prefixToArray($customerData, 'billing_');
        if (!empty($billing)) {
            $customerData['billing'] = $billing;

            $billingMapping = array(
                'firstname' => 'firstName',
                'lastname'  => 'lastName',
            );
            $customerData['billing'] = $this->mapFields($customerData['billing'], $billingMapping) + $customerData['billing'];

            $billingAttribute = $this->prefixToArray($customerData['billing'], 'attr_');

            if (!empty($billingAttribute)) {
                $customerData['billing']['attribute'] = $billingAttribute;
            }
        }

        $shipping = $this->prefixToArray($customerData, 'shipping_');
        if (!empty($shipping)) {
            $customerData['shipping'] = $shipping;

            $shippingMapping = array(
                'firstname' => 'firstName',
                'lastname'  => 'lastName',
            );
            $customerData['shipping'] = $this->mapFields($customerData['shipping'], $shippingMapping) + $customerData['shipping'];

            $shippingAttribute = $this->prefixToArray($customerData['shipping'], 'attr_');

            if (!empty($shippingAttribute)) {
                $customerData['shipping']['attribute'] = $shippingAttribute;
            }
        }

        return $customerData;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $limit  = $this->Request()->getParam('limit', 500000);
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
            $attributesSelect = array();
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
			    c.showfiltergroups,
			    c.external,
			    c.hidefilter
                $attributesSelect
			FROM s_categories c
			LEFT JOIN s_categories_attributes attr ON attr.categoryID = c.id
			WHERE c.id != 1
			ORDER BY c.left, c.position

			 LIMIT {$offset},{$limit}
		";

        $stmt = Shopware()->Db()->query($sql);
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * @param array $orderIds
     * @param int $statusId
     * @throws Exception
     */
    protected function updateOrderStatus($orderIds, $statusId)
    {
        $status = Shopware()->Models()->getRepository('Shopware\Models\Order\Status')->findOneBy(array(
            'id'    => $statusId,
            'group' => 'state'
        ));

        if (empty($status)) {
            throw new Exception(sprintf("OrderStatus by id %s not found", $statusId));
        }

        $builder = Shopware()->Models()->getRepository('Shopware\Models\Order\Order')
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
     */
    function deleteOtherArticleImages ($articleId, $imageIds = null)
    {
        if (empty($articleId)) {
            return false;
        }

        if (!empty($imageIds)) {
            $sql = 'DELETE FROM s_articles_img WHERE id NOT IN (?) AND articleID = ?';
        } else {
            $sql = 'DELETE FROM s_articles_img WHERE articleID = ?';
        }

        $result = $this->sDB->Execute($sql);
        if($result === false) {
            return false;
        }

        return true;
    }

    /**
     * @return int
     */
    protected function deleteEmptyCategories()
    {
        $sql = "
                DELETE ac
                FROM s_articles_categories ac
                LEFT JOIN s_categories c
                    ON c.id = ac.categoryID
                LEFT JOIN s_articles a
                    ON  a.id = ac.articleID
                WHERE c.id IS NULL OR a.id IS NULL
		    ";
        Shopware()->Db()->exec($sql);

        $sql = "
                SELECT
                c.id,
                (
                    SELECT COUNT(ac.id)
                    FROM s_categories c2

                    INNER JOIN s_articles_categories ac
                    ON ac.categoryID = c2.id

                    WHERE c2.left >= c.left
                    AND c2.right <= c.right
                ) as articleCount

                FROM s_categories c
                HAVING articleCount = 0
                AND c.id <> 1
                AND c.id NOT IN (SELECT category_id FROM s_core_shops)
         ";

        $emptyCategories = Shopware()->Db()->fetchCol($sql);
        $result = 0;
        if (count($emptyCategories)) {
            $result = Shopware()->Db()->delete('s_categories', array('id IN(?)' => $emptyCategories));
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function deleteAllCategories()
    {
        $sql = "SELECT category_id FROM s_core_shops";
        $result = Shopware()->Db()->fetchCol($sql);

        if (count($result)) {
            $result = Shopware()->Db()->delete('s_categories', array('id NOT IN(?)' => $result));
        } else {
            $result = Shopware()->Db()->exec('TRUNCATE s_categories');
        }

        Shopware()->Db()->exec("TRUNCATE s_articles_categories");
        Shopware()->Db()->exec("TRUNCATE s_emarketing_banners");
        Shopware()->Db()->exec("TRUNCATE s_emarketing_promotions");

        return $result;
    }

    /**
     * @param Zend_Db_Statement_Interface $stmt
     * @param string $filename
     */
    public function sendCsv(Zend_Db_Statement_Interface $stmt, $filename)
    {
        $this->Response()->setHeader('Content-Type', 'text/x-comma-separated-values;charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $this->Response()->setHeader('Content-Transfer-Encoding', 'binary');

        $convert = new Shopware_Components_Convert_Csv();
        $first   = true;
        $keys    = array();
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
     * @param string $filename
     * @param string $title
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
     * @param array $input
     * @param string $prefix
     * @return array
     */
    protected function prefixToArray(&$input, $prefix)
    {
        $output = array();
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
     * @return array
     */
    protected function mapFields($input, $mapping = array(), $whitelist = array())
    {
        $output = array();

        $whitelist = $mapping + $whitelist;

        foreach ($input as $key => $value) {
            if (isset($mapping[$key])) {
                $output[$mapping[$key]] = $value;
            } elseif (in_array($key, $whitelist)) {
                $output[$key] = $value;
            } else {
                // fields we don't know we don't want
            }
        }

        return $output;
    }

    /**
     * Replace dot with comma as decimal point
     *
     * @param string $value
     * @return float
     */
    public function sValFloat($value)
    {
        return floatval(str_replace(",", ".", $value));
    }


    /**
     * @param $input
     * @param string $keyname
     * @return array
     */
    protected function prepareXmlArray($input, $keyname = 'variant')
    {
        $output = array();
        foreach ($input as &$item) {
            $output[$keyname][] = $item;
        }
        return $output;
    }

    /**
     * @param $input
     * @return array
     */
    public function prepareImportXmlData($input)
    {
        $isIndexed = array_values($input) === $input;

        if ($isIndexed) {
            return $input;
        } else {
            return array($input);
        }
    }

    /**
     * @param SimpleXMLElement
     * @return array|string
     */
    public function simplexml2array($xml)
    {
        if (get_class($xml) == 'SimpleXMLElement') {
            $attributes = $xml->attributes();
            foreach($attributes as $k=>$v) {
                if ($v) $a[$k] = (string) $v;
            }
            $x = $xml;
            $xml = get_object_vars($xml);
        }
        if (is_array($xml)) {
            if (count($xml) == 0) return (string) $x; // for CDATA
            foreach($xml as $key=>$value) {
                $r[$key] = $this->simplexml2array($value);
            }
            if (isset($a)) $r['@attributes'] = $a;    // Attributes
            return $r;
        }
        return (string) $xml;
    }

    /**
     * @param array $input
     * @return array
     */
    public function array_filter_recursive($input)
    {
        foreach ($input as &$value) {
            if (is_array($value)) {
                $value = $this->array_filter_recursive($value);
            }
        }

        return array_filter($input);
    }

    /**
     * @param string $url URL of the resource that should be loaded (ftp, http, file)
     * @return bool|string returns the absolute path of the downloaded file
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function load($url)
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
        } else if (!is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Destination directory '%s' does not have write permissions.", $destPath)
            );
        }

        $urlArray = parse_url($url);
        $urlArray['path'] = explode("/",$urlArray['path']);
        switch ($urlArray['scheme']) {
            case "ftp":
            case "http":
            case "file":
                $hash = "";
                while (empty($hash)) {
                    $hash = md5(uniqid(rand(), true));
                    if (file_exists("$destPath/$hash")) {
                        $hash = "";
                    }
                }

                if (!$put_handle = fopen("$destPath/$hash", "w+")) {
                    throw new \Exception("Could not open $destPath/$hash for writing");
                }

                if (!$get_handle = fopen($url, "r")) {
                    return false;
                }
                while (!feof($get_handle)) {
                    fwrite($put_handle, fgets($get_handle, 4096));
                }
                fclose($get_handle);
                fclose($put_handle);

                return "$destPath/$hash";
        }
        throw new \InvalidArgumentException(
            sprintf("Unsupported schema '%s'.", $urlArray['scheme'])
        );
    }

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
}
