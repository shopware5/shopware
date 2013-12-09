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
 * Shopware ControllerBase Plugin
 */
class Shopware_Plugins_Core_ControllerBase_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
	/**
	 * Install plugin method
	 *
	 * @return bool
	 */
	public function install()
	{
		$this->subscribeEvent(
	 		'Enlight_Controller_Action_PostDispatch',
	 		'onPostDispatch',
	 		100
	 	);
		return true;
	}

	/**
	 * Event listener method
	 *
	 * Read base controller data
	 *
	 * @param Enlight_Event_EventArgs $args
	 */
	public function onPostDispatch(Enlight_Event_EventArgs $args)
	{
		$request = $args->getSubject()->Request();
		$response = $args->getSubject()->Response();
		$view = $args->getSubject()->View();


		if(!$request->isDispatched() || $response->isException()
          || $request->getModuleName() != 'frontend'
          || !$view->hasTemplate()) {
            return;
        }

        $shop = Shopware()->Shop();
		$view->Controller = $args->getSubject()->Request()->getControllerName();
        $view->Shopware = Shopware();

        if(!$shop->get('esi')) {
            $view->sBasketQuantity = Shopware()->Modules()->Basket()->sCountBasket();
            $view->sBasketAmount = $this->getBasketAmount();
            $view->sNotesQuantity = Shopware()->Modules()->Basket()->sCountNotes();
            $view->sUserLoggedIn = Shopware()->Modules()->Admin()->sCheckUser();
            $categoryContent = $view->sCategoryContent;

            $topSellerActive = $this->Application()->Config()->get(
                'topSellerActive',
                true
            );

            if(!empty($categoryContent) && $categoryContent['level'] <= 2 && $topSellerActive) {
                $view->sCharts = Shopware()->Modules()->Articles()->sGetArticleCharts(
                    $categoryContent['id']
                );
            }
            if(!empty($view->sCompareShow)) {
                $view->sComparisons = Shopware()->Modules()->Articles()->sGetComparisons();
            }
            if(!empty($view->sLastArticlesShow)) {
                $view->sLastArticles = Shopware()->Modules()->Articles()->sGetLastArticles();
            }
            if(!empty($view->sCloudShow)) {
                $view->sCloud = Shopware()->Modules()->Marketing()->sBuildTagCloud();
            }
            if(empty($view->sBlog) && $view->Controller == "index") {
                $view->sBlog = $this->getBlog();
            }

            $view->sLanguages = $this->getLanguages();
            $view->sCurrencies = $this->getCurrencies();
        } else {
            $view->sBasketQuantity = $view->sBasketQuantity ?: 0;
            $view->sBasketAmount = $view->sBasketAmount ?: 0;
            $view->sNotesQuantity = $view->sNotesQuantity ?: 0;
            $view->sUserLoggedIn = $view->sUserLoggedIn ?: false;
        }

        $view->Shop = $shop;
        $view->Locale = $shop->getLocale()->getLocale();

        $view->sCategoryStart = $shop->getCategory()->getId();
        $view->sCategoryCurrent = $this->getCategoryCurrent($view->sCategoryStart);
        $view->sCategories = $this->getCategories($view->sCategoryCurrent);
        $view->sMainCategories = $view->sCategories;
        $view->sOutputNet = Shopware()->Session()->sOutputNet;

        $activePage = isset($view->sCustomPage['id']) ? $view->sCustomPage['id'] : null;
        $view->sMenu = $this->getMenu($shop->getId(), $activePage);

        if(!Shopware()->Shop()->get('esi')) {
           $view->sCampaigns = $this->getCampaigns($view->sCategoryCurrent);
        }
        $view->sShopname = Shopware()->Config()->shopName;
	}

	/**
	 * Returns basket amount
	 *
	 * @return float
	 */
	public function getBasketAmount()
	{
		$amount = Shopware()->Modules()->Basket()->sGetAmount();
		return empty($amount) ? 0 : array_shift($amount);
	}

    /**
     * Returns current category id
     *
     * @param $default
     * @return int
     */
	public function getCategoryCurrent($default)
	{
		if(!empty(Shopware()->System()->_GET['sCategory'])) {
			return (int) Shopware()->System()->_GET['sCategory'];
		} elseif(Shopware()->Front()->Request()->getQuery('sCategory')) {
			return (int) Shopware()->Front()->Request()->getQuery('sCategory');
		} else {
			return (int) $default;
		}
	}

    /**
     * Return current categories
     *
     * @param $parentId
     * @return array
     */
	public function getCategories($parentId)
	{
		return Shopware()->Modules()->Categories()->sGetCategories($parentId);
	}

    /**
	 * Return shop languages
	 *
	 * @return array
	 */
	public function getLanguages()
	{
        $shops = Shopware()->System()->sSubShop['switchLanguages'];
		if (empty($shops)) {
			return false;
		}
        $shops = Shopware()->Db()->quote(explode('|', $shops));
		$sql = '
			SELECT c.*, IF(id=?,1,0) as flag FROM s_core_multilanguage c
			WHERE id IN ( ' . $shops.')
		';
		return Shopware()->Db()->fetchAll($sql, array(
            Shopware()->System()->sLanguage
        ));
	}

	/**
	 * Return shop currencies
	 *
	 * @return array
	 */
	public function getCurrencies()
	{
        $currencies = Shopware()->System()->sSubShop['switchCurrencies'];
        if (empty($currencies)) {
            return false;
        }
        $currencies = Shopware()->Db()->quote(explode('|', $currencies));
		$sql = '
			SELECT c.*, IF(id=?, 1, 0) as flag FROM s_core_currencies c
			WHERE id IN (' . $currencies . ')
			ORDER BY position ASC
		';
		return Shopware()->Db()->fetchAll($sql, array(
            Shopware()->System()->sCurrency['id']
        ));
	}

    /**
     * Return cms menu items
     *
     * @param   null|int $shopId
     * @param   null|int $activePageId
     * @return  array
     */
	public function getMenu($shopId = null, $activePageId = null)
	{
        if($shopId === null) {
            $shopId = Shopware()->Shop()->getId();
        }
        $sql = "
            SELECT
              p.id, p.description, p.link, p.target,
              g.key as `group`, m.key as mapping,
              (SELECT COUNT(*) FROM s_cms_static WHERE parentID=p.id) as childrenCount

            FROM s_cms_static p, s_cms_static_groups g

            LEFT JOIN s_cms_static_groups m
            ON m.id=g.mapping_id

            LEFT JOIN s_core_shop_pages s
            ON s.group_id=g.id
            AND s.shop_id=?

            WHERE g.active=1 AND parentID=0
            AND CONCAT('|', p.grouping, '|') LIKE CONCAT('%|', g.key, '|%')
            AND (m.id IS NULL OR s.shop_id IS NOT NULL)
            AND (m.id IS NULL OR m.active=1)

            ORDER BY `mapping`, p.position, p.description
        ";
        $links = Shopware()->Db()->fetchAll($sql, array($shopId));

        $menu = array();
		foreach ($links as $link) {
            if($activePageId !== null) {
                $link['active'] = $activePageId == $link['id'];
            }
            if(!empty($link['childrenCount'])) {
                $sql = "
                    SELECT p.id, p.description, p.link, p.target
                    FROM s_cms_static p
                    WHERE p.parentID = ?
                    ORDER BY p.position
                ";
                $link['subPages'] = Shopware()->Db()->fetchAll($sql, array($link['id']));
                if($activePageId !== null) {
                    foreach($link['subPages'] as $subKey => $subPage) {
                        $active = $activePageId == $subPage['id'];
                        $link['subPages'][$subKey]['active'] = $active;
                        if($active) {
                            $link['active'] = true;
                        }
                    }
                }
            }
            $group = !empty($link['mapping']) ? $link['mapping'] : $link['group'];
            if(!isset($menu[$group]) || (!empty($link['mapping']) && empty($menu[$group][0]['mapping']))) {
                $menu[$group] = array($link);
            } else {
                $menu[$group][] = $link;
            }
		}

		return $menu;
	}

    /**
     * Return box campaigns items
     *
     * @param $parentId
     * @return array
     */
	public function getCampaigns($parentId)
	{
		$campaigns = array('leftTop'=>array(), 'leftMiddle'=>array(), 'leftBottom'=>array(), 'rightMiddle'=>array(),'rightBottom'=>array());

        foreach ($campaigns as $position => $content){

            $campaigns[$position] = Shopware()->Modules()->Marketing()->sCampaignsGetList(
                $parentId, $position
            );
        }
        return $campaigns;
	}

    /**
     * Gets the Blog articles for the index page
     *
     * @return Array | blog article array
     */
    public function getBlog()
    {
        $blog = null;

        if(!empty(Shopware()->Config()->BlogCategory)) {
            /** @var $repository \Shopware\Models\Blog\Repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Blog\Blog');
            $blogArticlesQuery = $repository->getListQuery(array(Shopware()->Config()->BlogCategory), 0, Shopware()->Config()->BlogLimit+1);

            $blogArticleData = $blogArticlesQuery->getArrayResult();

            //adding thumbnails to the blog article
            foreach ($blogArticleData as $key => $blogArticle) {
                /** @var $mediaModel \Shopware\Models\Media\Media */
                if(!empty($blogArticle["media"][0]['mediaId'])) {
                    $mediaModel = Shopware()->Models()->find('Shopware\Models\Media\Media', $blogArticle["media"][0]['mediaId']);
                    if($mediaModel != null) {
                        $blogArticleData[$key]["preview"]["thumbNails"] = array_values($mediaModel->getThumbnails());
                    }
                }

                //adding vote average
                $avgVoteQuery = $repository->getAverageVoteQuery($blogArticle["id"]);
                $blogArticleData[$key]["sVoteAverage"] = $avgVoteQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);

                //adding number of comments to the blog article
                $blogArticleData[$key]["numberOfComments"] = count($blogArticle["comments"]);
            }
            $blog["sArticles"] = $blogArticleData;
        }

        return $blog;
    }


	/**
	 * Returns capabilities
	 *
	 * @return array
	 */
	public function getCapabilities()
    {
        return array(
    		'install' => false,
            'enable' => false,
    		'update' => true
    	);
    }
}
