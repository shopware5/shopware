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
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Frontend_ListingTest extends Enlight_Components_Test_Controller_TestCase
{

    protected $category;


    public function testCategoryDetailLink()
    {
        $default = Shopware()->Config()->categoryDetailLink;

        $this->saveConfig('categoryDetailLink', true);

        $this->removeDemoData();
        $this->insertDemoData();

        $this->Request()
            ->setMethod('POST');

        $this->dispatch('/cat/index/sCategory/80');
        $this->assertTrue($this->Response()->isRedirect());

        $this->removeDemoData();
        $this->saveConfig('categoryDetailLink', $default);
    }


    /**
     * Helper function to initial the test case
     * demo data.
     */
    protected function insertDemoData()
    {
        $category = array(
            'parent' => '3',
            'path' => '|3|',
            'description' => 'ListingTest',
            'active' => '1'
        );
        Shopware()->Db()->insert('s_categories', $category);

        $this->category = $this->getDemoCategory();

        $categoryArticles = array(
            array('articleID' => '3','categoryID' => '3','parentCategoryID' => $this->category['id']),
            array('articleID' => '3','categoryID' => $this->category['id'],'parentCategoryID' => $this->category['id'])
        );

        foreach($categoryArticles as $article) {
            Shopware()->Db()->insert('s_articles_categories_ro', $article);
        }

        Shopware()->Db()->insert('s_articles_categories', array('articleID' => '3','categoryID' => $this->category['id']));
    }


    /**
     * Helper function to clean up the test case demo data.
     */
    protected function removeDemoData()
    {
        $category = $this->getDemoCategory();

        $sql = "DELETE FROM s_categories WHERE description = 'ListingTest'";
        Shopware()->Db()->query($sql);

        if (!empty($category)) {
            $sql = "DELETE FROM s_articles_categories_ro WHERE parentCategoryID = ?";
            Shopware()->Db()->query($sql, array($category['id']));
        }
    }
    /**
     * Helper function to get the test case demo category.
     * @return array
     */
    protected function getDemoCategory()
    {
        return Shopware()->Db()->fetchRow("SELECT * FROM s_categories WHERE description = 'ListingTest' LIMIT 1");
    }

    /**
     * Helper method to persist a given config value
     */
    protected function saveConfig($name, $value)
    {
        $shopRepository    = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
        $formRepository    = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = array('name' => $name);
        if (isset($formName)) {
            $form = $formRepository->findOneBy(array('name' => $formName));
            $findBy['form'] = $form;
        }

        /** @var $element Shopware\Models\Config\Element */
        $element = $elementRepository->findOneBy($findBy);

        // If the element is empty, the given setting does not exists. This might be the case for some plugins
        // Skip those values
        if (empty($element)) {
            return;
        }

        foreach ($element->getValues() as $valueModel) {
            Shopware()->Models()->remove($valueModel);
        }

        $values = array();
        // Do not save default value
        if ($value !== $element->getValue()) {
            $valueModel = new Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues($values);
        Shopware()->Models()->flush($element);
    }

}
