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

class Shopware_Components_SeoIndex extends Enlight_Class
{
    /**
     * The old 'refreshIndex' method from the RouterRewrite Plugin
     *
     * This method ist used, if the SEO index needs to be build in *one* request - e.g. CronJob or Live
     *
     * @throws \Zend_Db_Adapter_Exception
     */
    public function refreshSeoIndex()
    {
        list($cachedTime, $elementId, $shopId) = $this->getCachedTime();

        $cache = (int) Shopware()->Config()->routerCache;
        $cache = $cache < 360 ? 86400 : $cache;
        $currentTime = date('Y-m-d H:i:s');

        if (strtotime($cachedTime) < time() - $cache) {
            $this->setCachedTime($currentTime, $elementId, $shopId);

            $resultTime = Shopware()->Modules()->RewriteTable()->sCreateRewriteTable($cachedTime);
            if ($resultTime === $cachedTime) {
                $resultTime = $currentTime;
            }
            if ($resultTime !== $currentTime) {
                $this->setCachedTime($resultTime, $elementId, $shopId);
            }
        }
    }

    /**
     * Read the exact time of the last SEO url update. Will also return elementId and shopId
     * in order to be able to update that option later
     *
     * todo@dn: Taken from RouterRewrite plugin - clean up
     *
     * @return array
     */
    public function getCachedTime()
    {
        // Get elementId in order to read/write config later
        $sql = "SELECT `id` FROM `s_core_config_elements` WHERE `name` LIKE 'routerlastupdate'";
        $elementId = Shopware()->Db()->fetchOne($sql);
        $shopId = Shopware()->Shop()->getId();

        // Read config
        $sql = '
            SELECT v.value
            FROM s_core_config_elements e, s_core_config_values v
            WHERE v.element_id=e.id AND e.id=? AND v.shop_id=?
        ';
        $cachedTime = Shopware()->Db()->fetchOne($sql, [$elementId, $shopId]);
        if (!empty($cachedTime)) {
            $cachedTime = unserialize($cachedTime, ['allowed_classes' => false]);
        }
        if (empty($cachedTime)) {
            $cachedTime = '0000-00-00 00:00:00';
        }

        return [$cachedTime, $elementId, $shopId];
    }

    /**
     * Helper function to reset the cached time. Moved here from the router engine
     *
     * @param string $resultTime
     * @param int    $elementId
     * @param int    $shopId
     *
     * @throws \Zend_Db_Adapter_Exception
     */
    public function setCachedTime($resultTime, $elementId, $shopId)
    {
        $sql = '
            DELETE FROM s_core_config_values
            WHERE element_id=? AND shop_id=?
        ';
        Shopware()->Db()->query($sql, [$elementId, $shopId]);

        $sql = '
            INSERT INTO s_core_config_values (element_id, shop_id, value)
            VALUES (?, ?, ?)
        ';
        Shopware()->Db()->query($sql, [$elementId, $shopId, serialize($resultTime)]);
    }

    /**
     * Register a shop in order to be able to use the sRewriteTable core class
     *
     * @param int $shopId
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function registerShop($shopId)
    {
        /** @var \Shopware\Models\Shop\Repository $repository */
        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class);

        $shop = $repository->getById($shopId);

        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);

        return $shop;
    }

    /**
     * The following count methods will return the number of items for each resource.
     *
     * They are used by the backend controllers and allow us to calculate, how often the seo link generation
     * needs to be triggered until it is done
     */

    /**
     * Count categories for the current shop
     *
     * @param int $shopId
     */
    public function countCategories($shopId)
    {
        if (empty(Shopware()->Config()->routerCategoryTemplate)) {
            return 0;
        }

        $shop = $this->registerShop($shopId);
        $parentId = $shop->getCategory()->getId();

        return Shopware()->Db()->fetchOne(
            'SELECT COUNT(id) FROM s_categories WHERE path LIKE :path',
            ['path' => '%|' . $parentId . '|%']
        );
    }

    /**
     * Count blog articles
     *
     * @param int $shopId
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     *
     * @return int
     */
    public function countBlogs($shopId)
    {
        $this->registerShop($shopId);

        // Get blog categories
        /** @var \Doctrine\ORM\Query $query */
        $query = Shopware()->Models()->getRepository(\Shopware\Models\Category\Category::class)->getBlogCategoriesByParentQuery(Shopware()->Shop()->get('parentID'));
        $blogCategories = $query->getArrayResult();

        // Get list of blogCategory ids
        $blogCategoryIds = [];
        foreach ($blogCategories as $blogCategory) {
            $blogCategoryIds[] = $blogCategory['id'];
        }

        // Count total number of associated blog articles
        $builder = Shopware()->Models()->getRepository(\Shopware\Models\Blog\Blog::class)->getListQueryBuilder(
            $blogCategoryIds, null
        );
        $numResults = $builder->select('COUNT(blog)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $numResults;
    }

    /**
     * Count the number of articles which need an update
     *
     * @param int $shopId
     *
     * @return string
     */
    public function countArticles($shopId)
    {
        $this->registerShop($shopId);

        // Calculate the number of articles which have been update since the last update time
        $sql = '
            SELECT COUNT(DISTINCT a.id)
            FROM s_articles a

            INNER JOIN s_articles_categories_ro ac
                ON  ac.articleID = a.id
                AND ac.categoryID = ?
            INNER JOIN s_categories c
                ON  c.id = ac.categoryID
                AND c.active = 1

            JOIN s_articles_details d
                ON d.id = a.main_detail_id

            LEFT JOIN s_articles_attributes at
                ON at.articledetailsID=d.id

            LEFT JOIN s_articles_translations atr
                ON atr.articleID=a.id
                AND atr.languageID=?

            LEFT JOIN s_articles_supplier s
                ON s.id=a.supplierID

            WHERE a.active=1
            ORDER BY a.changetime, a.id
        ';

        return (int) Shopware()->Db()->fetchOne($sql, [
            Shopware()->Shop()->get('parentID'),
            Shopware()->Shop()->getId(),
        ]);
    }

    /**
     * Get the number of emotion landing pages which will be updated
     *
     * @return int
     */
    public function countEmotions()
    {
        /** @var \Shopware\Models\Emotion\Repository $repo */
        $repo = Shopware()->Models()->getRepository(\Shopware\Models\Emotion\Emotion::class);
        $builder = $repo->getListingQuery();

        $builder
            ->andWhere('emotions.is_landingpage = 1 ')
            ->andWhere('emotions.parent_id IS NULL')
            ->andWhere('emotions.active = 1');

        $builder->select('COUNT(DISTINCT emotions.id)')
            ->resetQueryPart('groupBy')
            ->resetQueryPart('orderBy');

        $statement = $builder->execute();
        $count = $statement->fetch(PDO::FETCH_COLUMN);

        return (int) $count;
    }

    /**
     * Count CMS/ticket system
     *
     * These four items are all created in sCreateRewriteTableContent. As the queries are quite simple,
     * we just return the number of items for the resource with the most items.
     * When setting the batchSize/limit for this resource, keep in mind, the the actual number of links generated
     * might be four times higher than the batchSize (as four resources are handled).
     *
     * @param int $shopId
     *
     * @return float|int
     */
    public function countContent($shopId)
    {
        $this->registerShop($shopId);

        $counts = [
            Shopware()->Db()->fetchOne('SELECT COUNT(id) FROM `s_cms_support`'),
            Shopware()->Db()->fetchOne('SELECT COUNT(id) FROM `s_cms_static` WHERE link=\'\''),
        ];

        return array_sum($counts);
    }

    /**
     * Count Static routes
     *
     * @param int $shopId
     *
     * @return int
     */
    public function countStatic($shopId)
    {
        $this->registerShop($shopId);
        $urls = Shopware()->Config()->seoStaticUrls;

        if (empty($urls)) {
            return 0;
        }
        $static = [];

        if (!empty($urls)) {
            foreach (explode("\n", $urls) as $url) {
                list($key, $value) = explode(',', trim($url));
                if (empty($key) || empty($value)) {
                    continue;
                }
                $static[$key] = $value;
            }
        }

        return count($static);
    }

    /**
     * Get the number of supplier which friendly url will be updated
     *
     * @param int $shopId
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int
     */
    public function countSuppliers($shopId)
    {
        $seoSupplierConfig = Shopware()->Config()->get('sSEOSUPPLIER');
        if ($seoSupplierConfig === null || $seoSupplierConfig === false) {
            return 0;
        }

        $repository = Shopware()->Models()->getRepository(\Shopware\Models\Article\Supplier::class);

        $numResults = $repository->getFriendlyUrlSuppliersCountQueryBuilder()->getQuery()->getSingleScalarResult();

        return (int) $numResults;
    }

    public function countContentTypes(): int
    {
        return (int) count(Shopware()->Container()->get(\Shopware\Bundle\ContentTypeBundle\Services\TypeProvider::class)->getTypes());
    }
}
