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

namespace Shopware\Tests\Plugins\Core\MarketingAggregate;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class AbstractMarketing extends \Enlight_Components_Test_Plugin_TestCase
{
    /**
     * @return \Shopware_Components_SimilarShown
     */
    protected function SimilarShown()
    {
        return Shopware()->Container()->get('SimilarShown');
    }

    /**
     * @return \Shopware_Components_TopSeller
     */
    protected function TopSeller()
    {
        return Shopware()->Container()->get('TopSeller');
    }

    /**
     * @return \Shopware_Components_AlsoBought
     */
    protected function AlsoBought()
    {
        return Shopware()->Container()->get('AlsoBought');
    }

    /**
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected function Db()
    {
        return Shopware()->Db();
    }

    /**
     * @return \sArticles
     */
    protected function Articles()
    {
        return Shopware()->Modules()->Articles();
    }

    /**
     * @return \Shopware_Plugins_Core_MarketingAggregate_Bootstrap
     */
    protected function Plugin()
    {
        return Shopware()->Plugins()->Core()->MarketingAggregate();
    }

    protected function getAllArticles($condition = '')
    {
        return $this->Db()->fetchAll("SELECT * FROM s_articles " . $condition);
    }

    public function setUp()
    {
        parent::setUp();
    }



    protected function assertArrayEquals(array $expected, array $result, array $properties)
    {
        foreach ($properties as $property) {
            $this->assertEquals($expected[$property], $result[$property]);
        }
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

        /** @var $element \Shopware\Models\Config\Element */
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
            $valueModel = new \Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues($values);
        Shopware()->Models()->flush($element);
    }
}
