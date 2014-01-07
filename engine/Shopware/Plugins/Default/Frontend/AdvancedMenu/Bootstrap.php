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
 * Shopware AdvancedMenu Plugin
 */
class Shopware_Plugins_Frontend_AdvancedMenu_Bootstrap extends Shopware_Components_Plugin_Bootstrap
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
            'onPostDispatch'
        );

        $form = $this->Form();

        $parent = $this->Forms()->findOneBy(array('name' => 'Frontend'));
        $form->setParent($parent);

        $form->setElement('checkbox', 'show', array(
            'label' => 'Menü anzeigen', 'value' => 1,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'levels', array(
            'label' => 'Anzahl Ebenen', 'value' => 2,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('boolean', 'caching', array(
            'label' => 'Caching aktivieren', 'value' => 1
        ));
        $form->setElement('number', 'cachetime', array(
            'label' => 'Cachezeit', 'value' => 86400
        ));

        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => 'Erweitertes Menü'
        );
    }

    /**
     * @var sCategories
     */
    protected $module;

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched() || $response->isException()
          || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }

        $parent = Shopware()->Shop()->get('parentID');
        $category = empty(Shopware()->System()->_GET['sCategory']) ? $parent : Shopware()->System()->_GET['sCategory'];
        $config = $this->Config();

        if (empty($config->show) && $config->show !== null) {
            return;
        }

        $this->module = $this->Application()->Modules()->Categories();

        $view->assign('sAdvancedMenu', $this->getAdvancedMenu(
            $parent,
            $category,
            (int) $config->levels
        ));
        $view->extendsTemplate('frontend/plugins/advanced_menu/index.tpl');
    }

    /**
     * Returns the complete menu with category path.
     *
     * @param int $category
     * @param int $categoryFlag
     * @param int $depth
     * @return array
     */
    public function getAdvancedMenu($category, $categoryFlag = null, $depth = null)
    {
        $shopID = $this->Application()->Shop()->getId();
        $config = $this->Config();
        $id = 'Shopware_AdvancedMenu_Tree_' . $shopID . '_' . $category . '_' . Shopware()->System()->sUSERGROUPDATA['id'];
        $cache = Shopware()->Cache();

        if (!empty($config->caching)) {
            if (!$cache->test($id)) {
                $tree = $this->getCategoryTree($category, $depth);
                $cache->save($tree, $id, array('Shopware_Plugin'), $config->cachetime);
            } else {
                $tree = $cache->load($id);
            }
        } else {
            $tree = $this->getCategoryTree($category, $depth);
        }

        $path = $this->getCategoryPath($categoryFlag, $category);

        $ref =& $tree;
        foreach ($path as $categoryId) {
            foreach ($ref as $categoryKey => $category) {
                if ($category['id'] == $categoryId) {
                    $ref[$categoryKey]['flag'] = true;
                    $ref =& $ref[$categoryKey]['sub'];
                    continue 2;
                }
            }
            break;
        }
        return $tree;
    }

    /**
     * Returns a category tree.
     *
     * @param int $category
     * @param int $depth
     * @return array
     */
    public function getCategoryTree($category, $depth = null)
    {
        return $this->module->sGetWholeCategoryTree($category, $depth);
    }

    /**
     * Returns a category path by category id.
     *
     * @param int $category
     * @param int $end
     * @return array
     */
    public function getCategoryPath($category, $end)
    {
        return $this->module->sGetCategoryPath($category, $end);
    }
}
