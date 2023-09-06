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

namespace Shopware\Tests\Functional\Plugins\Core\MarketingAggregate;

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Enlight_Components_Test_Plugin_TestCase;
use sArticles;
use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Config\Value;
use Shopware\Models\Shop\Shop;
use Shopware_Components_AlsoBought;
use Shopware_Components_SimilarShown;
use Shopware_Components_TopSeller;
use Shopware_Plugins_Core_MarketingAggregate_Bootstrap;

class AbstractMarketing extends Enlight_Components_Test_Plugin_TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return Shopware_Components_SimilarShown
     */
    protected function SimilarShown()
    {
        return Shopware()->Container()->get('similarshown');
    }

    /**
     * @return Shopware_Components_TopSeller
     */
    protected function TopSeller()
    {
        return Shopware()->Container()->get('topseller');
    }

    /**
     * @return Shopware_Components_AlsoBought
     */
    protected function AlsoBought()
    {
        return Shopware()->Container()->get('alsobought');
    }

    /**
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected function Db()
    {
        return Shopware()->Db();
    }

    /**
     * @return sArticles
     */
    protected function Articles()
    {
        return Shopware()->Modules()->Articles();
    }

    /**
     * @return Shopware_Plugins_Core_MarketingAggregate_Bootstrap
     */
    protected function Plugin()
    {
        return Shopware()->Plugins()->Core()->MarketingAggregate();
    }

    protected function getAllArticles($condition = '')
    {
        return $this->Db()->fetchAll('SELECT * FROM s_articles ' . $condition);
    }

    protected function assertArrayEquals(array $expected, array $result, array $properties)
    {
        foreach ($properties as $property) {
            static::assertEquals($expected[$property], $result[$property]);
        }
    }

    /**
     * Helper method to persist a given config value
     */
    protected function saveConfig($name, $value)
    {
        $shopRepository = Shopware()->Models()->getRepository(Shop::class);
        $elementRepository = Shopware()->Models()->getRepository(Element::class);
        $formRepository = Shopware()->Models()->getRepository(Form::class);
        $valueRepository = Shopware()->Models()->getRepository(Value::class);

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = ['name' => $name];
        if (isset($formName)) {
            $form = $formRepository->findOneBy(['name' => $formName]);
            $findBy['form'] = $form;
        }

        /** @var \Shopware\Models\Config\Element $element */
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

            Shopware()->Models()->persist($valueModel);
            Shopware()->Models()->flush($valueModel);

            return;
        }

        if ($value == $defaultValue || $value === null) {
            Shopware()->Models()->remove($valueModel);
        } else {
            $valueModel->setValue($value);
        }
        Shopware()->Models()->flush($valueModel);
    }
}
