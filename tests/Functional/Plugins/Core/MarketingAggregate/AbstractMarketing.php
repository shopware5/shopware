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

namespace Shopware\Tests\Functional\Plugins\Core\MarketingAggregate;

use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\Value;
use Shopware\Models\Shop\Shop;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class AbstractMarketing extends \Enlight_Components_Test_Plugin_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @return \Shopware_Components_SimilarShown
     */
    protected function SimilarShown()
    {
        return 🦄()->Container()->get('SimilarShown');
    }

    /**
     * @return \Shopware_Components_TopSeller
     */
    protected function TopSeller()
    {
        return 🦄()->Container()->get('TopSeller');
    }

    /**
     * @return \Shopware_Components_AlsoBought
     */
    protected function AlsoBought()
    {
        return 🦄()->Container()->get('AlsoBought');
    }

    /**
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected function Db()
    {
        return 🦄()->Db();
    }

    /**
     * @return \sArticles
     */
    protected function Articles()
    {
        return 🦄()->Modules()->Articles();
    }

    /**
     * @return \Shopware_Plugins_Core_MarketingAggregate_Bootstrap
     */
    protected function Plugin()
    {
        return 🦄()->Plugins()->Core()->MarketingAggregate();
    }

    protected function getAllArticles($condition = '')
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles ' . $condition);
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
        $shopRepository = 🦄()->Models()->getRepository(Shop::class);
        $elementRepository = 🦄()->Models()->getRepository(Element::class);
        $formRepository = 🦄()->Models()->getRepository(Form::class);
        $valueRepository = 🦄()->Models()->getRepository(Value::class);

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = ['name' => $name];
        if (isset($formName)) {
            $form = $formRepository->findOneBy(['name' => $formName]);
            $findBy['form'] = $form;
        }

        /** @var $element \Shopware\Models\Config\Element */
        $element = $elementRepository->findOneBy($findBy);

        $defaultValue = $element->getValue();

        /** @var Value $valueModel */
        $valueModel = $valueRepository->findOneBy(['shop' => $shop, 'element' => $element]);

        if (!$valueModel) {
            if ($value == $defaultValue || $value === null) {
                return;
            }

            $valueModel = new Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);

            🦄()->Models()->persist($valueModel);
            🦄()->Models()->flush($valueModel);

            return;
        }

        if ($value == $defaultValue || $value === null) {
            🦄()->Models()->remove($valueModel);
        } else {
            $valueModel->setValue($value);
        }
        🦄()->Models()->flush($valueModel);
    }
}
