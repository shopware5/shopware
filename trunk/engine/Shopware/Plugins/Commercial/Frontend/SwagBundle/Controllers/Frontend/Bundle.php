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
 */

/**
 * Shopware Frontend Controller
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Controllers\Frontend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Bundle extends Enlight_Controller_Action
{
    /**
     * Internal helper property to make the backend controller unit test possible without mocking the whole shopware project.
     *
     * @var \Shopware\Models\Article\Repository
     */
    protected $articleRepository = null;

    /**
     * The getArticleRepository function is an internal helper function to make the backend controller unit test
     * possible without mocking the whole shopware project.
     * @return \Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
    	if ($this->articleRepository === null) {
    		$this->articleRepository = Shopware()->Models()->getRepository('Shopware\Models\Article\Article');
    	}
    	return $this->articleRepository;
    }


    /**
     * Global interface to configure a configurator article of a bundle.
     * The function expects the unique bundleArticleId which defined in the
     * s_articles_bundles_articles table. The bundleArticleId passed from the getArticleConfigurationAction
     * in this controller when the customer hover an article position of the bundle.
     */
    public function configureArticleAction()
    {
        $bundleArticleId = $this->Request()->getParam('bundleArticleId');

        /**@var $bundleArticle \Shopware\CustomModels\Bundle\Article*/
        $bundleArticle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Article', $bundleArticleId);

        /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
        $bundle = $bundleArticle->getBundle();

        $groups = $this->getArticleRepository()->getConfiguratorGroupsQuery()->getResult();

        $configuration = array();
        /**@var $group \Shopware\Models\Article\Configurator\Group*/
        foreach($groups as $group) {
            if ($this->Request()->has('group-' . $group->getId())) {
                $configuration[$group->getId()] = $this->Request()->get('group-' . $group->getId());
            }
        }

        Shopware()->Session()->bundleConfiguration[$bundle->getId()][$bundleArticleId] = $configuration;

        $this->View()->setTemplate();
    }

    /**
     * Global interface to add a single bundle to the basket
     */
    public function addBundleToBasketAction()
    {
        $bundleId = (int) $this->Request()->getParam('bundleId');

        if (!$bundleId > 0) {
            return;
        }

        /**@var $bundle \Shopware\CustomModels\Bundle\Bundle*/
        $bundle = Shopware()->Models()->find('Shopware\CustomModels\Bundle\Bundle', $bundleId);

        $selection = new \Doctrine\Common\Collections\ArrayCollection();

        if ($bundle->getType() === Shopware_Components_Bundle::SELECTABLE_BUNDLE) {
            /**@var $bundleArticle \Shopware\CustomModels\Bundle\Article*/
            foreach($bundle->getArticles() as $bundleArticle) {
                if ($this->Request()->has('bundle-article-' . $bundleArticle->getId())) {
                    $selection->add($bundleArticle);
                }
            }
            if ($selection->count() === 0) {
                $this->redirect(array('controller' => 'detail', 'sArticle' => $bundle->getArticle()->getId()));
                return;
            }
        }

        $result = Shopware()->Bundle()->addBundleToBasket(
            $bundleId,
            $selection
        );

        if ($result['success'] === false) {
            $this->redirect(array('controller' => 'detail', 'sArticle' => $bundle->getArticle()->getId()));
            return;
        }

        $this->redirect(array('controller' => 'checkout'));
    }
}