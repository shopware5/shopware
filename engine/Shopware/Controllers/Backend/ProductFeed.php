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

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\ProductFeed\ProductFeed;
use Shopware\Models\Shop\Shop;

/**
 * Shopware Backend Controller for the Voucher Module
 *
 * Backend Controller for the product feed backend module.
 * Displays all feeds in an Ext.grid.Panel and allows to delete,
 * add and edit feeds. On the detail page the feeds data are displayed and can be edited
 */
class Shopware_Controllers_Backend_ProductFeed extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository;

    /**
     * @var \Shopware\Models\Shop\Repository
     */
    protected $shopRepository;

    /**
     * Returns a JSON string to the view containing all Product Feeds
     */
    public function getFeedsAction()
    {
        try {
            /** @var \Shopware\Models\ProductFeed\Repository $repository */
            $repository = Shopware()->Models()->getRepository(ProductFeed::class);
            $dataQuery = $repository->getListQuery(
                $this->Request()->getParam('sort', []),
                $this->Request()->getParam('start'),
                $this->Request()->getParam('limit')
            );

            $totalCount = Shopware()->Models()->getQueryCount($dataQuery);
            $feeds = $dataQuery->getArrayResult();

            $this->View()->assign(['success' => true, 'data' => $feeds, 'totalCount' => $totalCount]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Returns a JSON string to the view containing the detail information of an Product Feed
     */
    public function getDetailFeedAction()
    {
        $feedId = (int) $this->Request()->feedId;
        $feed = $this->getFeed($feedId);
        $this->View()->assign(['success' => true, 'data' => $feed]);
    }

    /**
     * Returns a JSON string to the view containing the supplier information of an Product Feed
     */
    public function getSuppliersAction()
    {
        $filter = $this->Request()->filter;
        $usedIds = $this->Request()->usedIds;

        $offset = $this->Request()->getParam('start');
        $limit = $this->Request()->getParam('limit', 20);

        $dataQuery = $this->getArticleRepository()
                          ->getSuppliersWithExcludedIdsQuery($usedIds, $filter, $offset, $limit);
        $total = Shopware()->Models()->getQueryCount($dataQuery);

        $data = $dataQuery->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * returns a JSON string to the view containing the shops information of an Product Feed we can't use the base
     * store because we need all shops and children
     */
    public function getShopsAction()
    {
        $shopQuery = $this->getShopRepository()->getBaseListQuery();
        $data = $shopQuery->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => count($data)]);
    }

    /**
     * Returns a JSON string to the view containing the article information of an Product Feed
     */
    public function getArticlesAction()
    {
        $filter = $this->Request()->filter;
        $usedIds = $this->Request()->usedIds;

        $offset = $this->Request()->getParam('start');
        $limit = $this->Request()->getParam('limit', 20);

        $dataQuery = $this->getArticleRepository()
                          ->getArticlesWithExcludedIdsQuery($usedIds, $filter, $offset, $limit);
        $total = Shopware()->Models()->getQueryCount($dataQuery);
        $data = $dataQuery->getArrayResult();

        // Return the data and total count
        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * Creates or updates a new Product Feed
     *
     * @throws RuntimeException
     */
    public function saveFeedAction()
    {
        $params = $this->Request()->getParams();

        $feedId = $params['id'];
        if (!empty($feedId)) {
            // Edit Product Feed
            /** @var ProductFeed $productFeed */
            $productFeed = Shopware()->Models()->getRepository(ProductFeed::class)->find($feedId);
            // Clear all previous associations
            $productFeed->getCategories()->clear();
            $productFeed->getSuppliers()->clear();
            $productFeed->getArticles()->clear();
        } else {
            // New Product Feed
            $productFeed = new ProductFeed();
            // To set this value initial
            $productFeed->setLastExport('now');
        }

        if (empty($params['shopId'])) {
            $params['shopId'] = null;
        }
        if (empty($params['categoryId'])) {
            $params['categoryId'] = null;
        }
        if (empty($params['customerGroupId'])) {
            $params['customerGroupId'] = null;
        }
        if (empty($params['languageId'])) {
            $params['languageId'] = null;
        }

        if (!$this->_isAllowed('sqli') && !empty($params['ownFilter'])) {
            unset($params['ownFilter']);
        }

        // Save data of the category tree
        $params['categories'] = $this->prepareAssociationDataForSaving('categories', Category::class, $params);

        // Save data of the supplier filter
        $params['suppliers'] = $this->prepareAssociationDataForSaving('suppliers', Supplier::class, $params);

        // Save data of the article filter
        $params['articles'] = $this->prepareAssociationDataForSaving('articles', Article::class, $params);

        $productFeed = $this->setDirty($productFeed, $params);
        $params['fileName'] = basename($params['fileName']);
        $productFeed->fromArray($params);

        // Just for future use
        $productFeed->setExpiry(new DateTime());
        $productFeed->setLastChange(new DateTime());

        // Clear feed cache
        $cacheDir = $this->container->getParameter('kernel.cache_dir');
        $cacheDir .= '/productexport/';
        if (!is_dir($cacheDir)) {
            if (@mkdir($cacheDir, 0777, true) === false) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'Productexport', $cacheDir));
            }
        } elseif (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'Productexport', $cacheDir));
        }

        $fileName = $productFeed->getHash() . '_' . $productFeed->getFileName();
        $filePath = $cacheDir . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $productFeed->setCacheRefreshed('2000-01-01');

        try {
            Shopware()->Models()->persist($productFeed);
            Shopware()->Models()->flush();

            $data = $this->getFeed($productFeed->getId());
            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Deletes a Feed from the database
     */
    public function deleteFeedAction()
    {
        try {
            /** @var \Shopware\Models\ProductFeed\ProductFeed $model */
            $model = Shopware()->Models()->getRepository(ProductFeed::class)->find($this->Request()->id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * helper method to prepare the association request data to save it directly
     * into the model via fromArray
     *
     * @param string $paramString
     * @param string $modelName
     * @param array  $params
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function prepareAssociationDataForSaving($paramString, $modelName, $params)
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection();
        if (!empty($params[$paramString])) {
            foreach ($params[$paramString] as $param) {
                $model = Shopware()->Models()->find($modelName, $param['id']);
                $collection->add($model);
            }
        }

        return $collection;
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * Permission to list all feeds
         */
        $this->addAclPermission('getFeedsAction', 'read', 'Insufficient Permissions');

        /*
         * Permission to show detail information of a feed
         */
        $this->addAclPermission('getDetailFeedAction', 'read', 'Insufficient Permissions');

        /*
         * Permission to delete the feed
         */
        $this->addAclPermission('deleteFeedAction', 'delete', 'Insufficient Permissions');
    }

    /**
     * Helper function to get access to the shop repository.
     *
     * @return \Shopware\Models\Shop\Repository
     */
    private function getShopRepository()
    {
        if ($this->shopRepository === null) {
            $this->shopRepository = Shopware()->Models()->getRepository(Shop::class);
        }

        return $this->shopRepository;
    }

    /**
     * Helper function to get access to the article repository.
     *
     * @return \Shopware\Models\Article\Repository
     */
    private function getArticleRepository()
    {
        if ($this->articleRepository === null) {
            $this->articleRepository = Shopware()->Models()->getRepository(Article::class);
        }

        return $this->articleRepository;
    }

    /**
     * Returns an array with feed data for the passed feed id.
     *
     * @param int $id
     */
    private function getFeed($id)
    {
        /** @var \Shopware\Models\ProductFeed\Repository $repository */
        $repository = Shopware()->Models()->getRepository(ProductFeed::class);
        $dataQuery = $repository->getDetailQuery($id);
        $feed = $dataQuery->getArrayResult();

        return $feed[0];
    }

    /**
     * Determines if a feed needs to be marked as dirty
     * New feeds, feeds whose header, body or footer have been
     * changed are marked as dirty
     *
     * @param ProductFeed $productFeed
     * @param array       $params
     *
     * @return ProductFeed
     */
    private function setDirty($productFeed, $params)
    {
        if ($productFeed->isDirty()) {
            return $productFeed;
        }

        if (!$productFeed->getId()) {
            $productFeed->setDirty(true);
        } elseif (isset($params['header']) && $params['header'] != $productFeed->getHeader()) {
            $productFeed->setDirty(true);
        } elseif (isset($params['body']) && $params['body'] != $productFeed->getBody()) {
            $productFeed->setDirty(true);
        } elseif (isset($params['footer']) && $params['footer'] != $productFeed->getFooter()) {
            $productFeed->setDirty(true);
        }

        return $productFeed;
    }
}
