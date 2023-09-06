<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

/**
 * Shopware class that handle static shop pages and dynamic content
 * Used to handle pages such as "Help", etc
 *
 * Used by Frontend_Custom and Frontend_Content controllers
 */
class sCms implements \Enlight_Hook
{
    /**
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * The Front controller object
     *
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @var Shopware_Components_Translation
     */
    private $translationComponent;

    public function __construct(
        ?Enlight_Components_Db_Adapter_Pdo_Mysql $db = null,
        ?Enlight_Controller_Front $front = null,
        ?Shopware_Components_Translation $translationComponent = null
    ) {
        $this->db = $db ?: Shopware()->Db();
        $this->front = $front ?: Shopware()->Front();
        $this->translationComponent = $translationComponent ?: Shopware()->Container()->get(\Shopware_Components_Translation::class);
    }

    /**
     * Read a specific, static page (E.g. terms and conditions, etc.)
     *
     * @param int|null $staticId The page id
     * @param int|null $shopId   Id of the shop
     *
     * @return array|false Page data, or false if none found by given id
     */
    public function sGetStaticPage($staticId = null, $shopId = null)
    {
        if (empty($staticId)) {
            $staticId = (int) $this->front->Request()->getQuery('sCustom', $staticId);
        }
        if (empty($staticId)) {
            return false;
        }

        $fallbackId = null;
        $translations = null;

        $sql = 'SELECT * FROM s_cms_static WHERE id = :pageId and active = 1';
        $params = ['pageId' => $staticId];

        if ($shopId) {
            $sql .= ' AND (shop_ids IS NULL OR shop_ids LIKE :shopId)';
            $params['shopId'] = '%|' . $shopId . '|%';

            if (Shopware()->Shop()->getFallback()) {
                $fallbackId = Shopware()->Shop()->getFallback()->getId();
            }

            $translations = $this->translationComponent->readWithFallback($shopId, $fallbackId, 'page', $staticId);
        }

        // Load static page data from database
        $staticPage = $this->db->fetchRow(
            $sql,
            $params
        );
        if ($staticPage === false) {
            return false;
        }

        // load attributes
        $staticPage['attribute'] = Shopware()->Container()->get(\Shopware\Bundle\AttributeBundle\Service\DataLoader::class)->load('s_cms_static_attributes', $staticId);

        if ($translations) {
            foreach ($translations as $property => $translation) {
                if ($translation !== '') {
                    if (strpos($property, '__attribute_') === 0) {
                        $property = str_replace('__attribute_', '', $property);
                        $staticPage['attribute'][$property] = $translation;
                        continue;
                    }
                    $staticPage[$property] = $translation;
                }
            }
        }

        /*
         * Add support for sub pages
         */
        if (!empty($staticPage['parentID'])) {
            $staticPage = $this->getRelatedForSubPage($staticPage, $shopId);
        } else {
            $staticPage = $this->getRelatedForPage($staticPage, $shopId);
        }

        return $staticPage;
    }

    /**
     * List all static page children's and their childrenCount by Id and groupKey
     *
     * @param int    $pageId
     * @param string $groupKey
     *
     * @return array
     */
    public function sGetStaticPageChildrensById($pageId = 0, $groupKey = 'left')
    {
        $menu = [];

        // Fetch parent if exists
        if ($pageId) {
            $sql = '
                SELECT
                p.id, p.description, p.link, p.target, p.parentID,
                (SELECT COUNT(*) FROM s_cms_static WHERE parentID = p.id) as childrenCount
                FROM s_cms_static p
                WHERE p.id = :parentId and p.active = 1
            ';

            $menu['parent'] = Shopware()->Db()->fetchRow($sql, ['parentId' => $pageId]);
        }

        // Fetch children
        $sql = "
            SELECT
            p.id, p.description, p.link, p.target, p.parentID,
            (SELECT COUNT(*) FROM s_cms_static WHERE parentID = p.id) as childrenCount
            FROM s_cms_static p
            WHERE p.parentID = :parentId
            AND CONCAT('|', p.grouping, '|') LIKE CONCAT('%|', :groupKey, '|%') and p.active = 1
        ";

        $menu['children'] = Shopware()->Db()->fetchAll($sql, ['parentId' => $pageId, 'groupKey' => $groupKey]);

        return $menu;
    }

    /**
     * Gets related pages for the given sub-page
     * If a shop id is provided, only content for that shop is displayed
     *
     * @param array    $staticPage
     * @param int|null $shopId
     */
    private function getRelatedForSubPage($staticPage, $shopId = null)
    {
        $andWhere = '';
        $siblingsParams = [
            'pageId' => $staticPage['id'],
            'parentId' => $staticPage['parentID'],
        ];
        $parentParams = [
            'parentId' => $staticPage['parentID'],
        ];

        $translations = null;
        $fallbackId = null;
        if ($shopId) {
            $andWhere .= ' AND (p.shop_ids IS NULL OR p.shop_ids LIKE :shopId)';
            $siblingsParams['shopId'] = '%|' . $shopId . '|%';
            $parentParams['shopId'] = '%|' . $shopId . '|%';

            if (Shopware()->Shop()->getFallback()) {
                $fallbackId = Shopware()->Shop()->getFallback()->getId();
            }

            $translations = $this->translationComponent->readWithFallback($shopId, $fallbackId, 'page', $staticPage['parentID']);
        }

        $siblingsSql = '
                SELECT p.id, p.description, p.link, p.target, IF(p.id=:pageId, 1, 0) as active, p.page_title
                FROM s_cms_static p
                WHERE p.parentID = :parentId AND p.active = 1
                ' . $andWhere . '
                ORDER BY p.position
            ';
        $staticPage['siblingPages'] = $this->db->fetchAll($siblingsSql, $siblingsParams);

        $parentSql = '
                SELECT p.id, p.description, p.link, p.target, p.page_title
                FROM s_cms_static p
                WHERE p.id = :parentId and p.active = 1
                ' . $andWhere;
        $parent = $this->db->fetchRow($parentSql, $parentParams);

        if ($translations) {
            foreach ($translations as $property => $translation) {
                if ($translation !== '') {
                    $parent[$property] = $translation;
                }
            }
        }

        $staticPage['parent'] = $parent;
        $staticPage['parent'] = $staticPage['parent'] ?: [];

        return $staticPage;
    }

    /**
     * Gets related pages for the given page
     *
     * @param array    $staticPage
     * @param int|null $shopId
     */
    private function getRelatedForPage($staticPage, $shopId = null)
    {
        $andWhere = '';
        $params = [
            'pageId' => $staticPage['id'],
        ];

        if ($shopId) {
            $andWhere .= ' AND (p.shop_ids IS NULL OR p.shop_ids LIKE :shopId)';
            $params['shopId'] = '%|' . $shopId . '|%';
        }

        $sql = '
                SELECT p.id, p.description, p.link, p.target, p.page_title
                FROM s_cms_static p
                WHERE p.parentID = :pageId and p.active = 1
                ' . $andWhere . '
                ORDER BY p.position
            ';
        $staticPage['subPages'] = $this->db->fetchAll($sql, $params);

        return $staticPage;
    }
}
