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

use Shopware\Bundle\EmotionBundle\Service\StoreFrontEmotionDeviceConfiguration;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Index extends Enlight_Controller_Action
{
    public function preDispatch()
    {
        $this->View()->loadTemplate('frontend/home/index.tpl');
    }

    public function indexAction()
    {
        /** @var $context ShopContextInterface */
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /** @var StoreFrontEmotionDeviceConfiguration $service */
        $service = $this->get('shopware_emotion.store_front_emotion_device_configuration');
        $emotions = $service->getCategoryConfiguration($categoryId, $context);

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);

        $this->View()->assign([
            'emotions' => $emotions,
            'hasEmotion' => !empty($emotions),
            'sCategoryContent' => $categoryContent,
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
        ]);
    }
}
