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
use Doctrine\Common\Collections\ArrayCollection;

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
        $this->subscribeEvents();
        $this->createForm();

        return true;
    }

    /**
     * @return array
     */
    public function enable()
    {
        return [
            'success' => true,
            'invalidateCache' => ['template', 'theme']
        ];
    }

    private function subscribeEvents()
    {
        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Less',
            'onCollectLessFiles'
        );

        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Javascript',
            'onCollectJavascriptFiles'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend',
            'onPostDispatch'
        );
    }

    private function createForm()
    {
        $form = $this->Form();

        $parent = $this->Forms()->findOneBy(array('name' => 'Frontend'));
        $form->setParent($parent);

        $form->setElement('checkbox', 'show', array(
            'label' => 'Menü anzeigen',
            'value' => 1,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('text', 'levels', array(
            'label' => 'Anzahl Ebenen',
            'value' => 3,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $form->setElement('boolean', 'caching', array(
            'label' => 'Caching aktivieren',
            'value' => 1
        ));

        $form->setElement('number', 'cachetime', array(
            'label' => 'Cachezeit',
            'value' => 86400
        ));

        $form->setElement(
            'select',
            'columnAmount',
            array(
                'label' => 'Breite des Teasers',
                'store' => array(
                    array(0, '0%'),
                    array(1, '25%'),
                    array(2, '50%'),
                    array(3, '75%'),
                    array(4, '100%')
                ),
                'value' => 2,
            )
        );

        $this->translateForm($form);
    }

    private function translateForm(Shopware\Models\Config\Form $form)
    {
        $translations = array(
            'en_GB' => array(
                'show' => array('label' => 'Show menu'),
                'levels' => array('label' => 'Category levels'),
                'caching' => array('label' => 'Enable caching'),
                'cachetime' => array('label' => 'Caching time'),
                'columnAmount' => array('label' => 'Teaser width')
            )
        );

        if ($this->assertMinimumVersion('4.2.2')) {
            $this->addFormTranslations($translations);
        } else {
            $form->translateForm($translations);
        }
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => $this->getLabel()
        );
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Erweitertes Menü';
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectLessFiles()
    {
        $lessDir = __DIR__ . '/Views/frontend/_public/src/less/';

        $less = new \Shopware\Components\Theme\LessDefinition(
            array(),
            array(
                $lessDir . 'advanced-menu.less'
            )
        );

        return new ArrayCollection(array($less));
    }

    /**
     * @return ArrayCollection
     */
    public function onCollectJavascriptFiles()
    {
        $jsDir = __DIR__ . '/Views/frontend/_public/src/js/';

        return new ArrayCollection(array(
            $jsDir . 'jquery.advanced-menu.js'
        ));
    }

    /**
     * @var sCategories
     */
    protected $module;

    /**
     * Event listener method
     *
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $config = $this->Config();
        $view = $args->getSubject()->View();
        $parent = Shopware()->Shop()->get('parentID');
        $sCategory = $args->getRequest()->get('sCategory');

        if (empty($config->show) && $config->show !== null) {
            return;
        }

        $this->module = $this->Application()->Modules()->Categories();

        $view->assign('sAdvancedMenu', $this->getAdvancedMenu(
            $parent,
            !empty($sCategory) ? $sCategory : $parent,
            (int) $config->levels
        ));

        $view->assign('columnAmount', $config->columnAmount);

        $view->addTemplateDir($this->Path() . 'Views');
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

        foreach ($tree as &$category) {
            $activeCategories = 0;

            foreach ($category['sub'] as $subCategory) {
                if ($subCategory['active']) {
                    $activeCategories++;
                }
            }

            $category['activeCategories'] = $activeCategories;
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
