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
use Shopware\Components\OptinServiceInterface;
use Symfony\Component\HttpFoundation\Cookie;

class Shopware_Controllers_Frontend_Index extends Enlight_Controller_Action
{
    public function preDispatch()
    {
        $this->View()->loadTemplate('frontend/home/index.tpl');
    }

    public function indexAction()
    {
        if ($this->handleThemeHash()) {
            return;
        }

        /** @var ShopContextInterface $context */
        $context = Shopware()->Container()->get('shopware_storefront.context_service')->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /** @var StoreFrontEmotionDeviceConfiguration $service */
        $service = $this->get('shopware_emotion.store_front_emotion_device_configuration');
        $emotions = $service->getCategoryConfiguration($categoryId, $context);

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);

        $this->View()->assign([
            'hasCustomerStreamEmotion' => $this->container->get('shopware.customer_stream.repository')->hasCustomerStreamEmotions($categoryId),
            'emotions' => $emotions,
            'hasEmotion' => !empty($emotions),
            'sCategoryContent' => $categoryContent,
            'sBanner' => Shopware()->Modules()->Marketing()->sBanner($categoryId),
        ]);
    }

    /**
     * Handle theme preview hash
     *
     * @return bool
     */
    private function handleThemeHash()
    {
        $hash = $this->Request()->getParam('themeHash');

        if (!$hash) {
            return false;
        }

        $optinService = $this->container->get('shopware.components.optin_service');

        $data = $optinService->get(OptinServiceInterface::TYPE_THEME_PREVIEW, $hash);

        if (!$data) {
            return false;
        }

        $optinService->delete(OptinServiceInterface::TYPE_THEME_PREVIEW, $hash);

        $this->Response()->headers->setCookie(new Cookie($data['sessionName'], $data['sessionValue'], 0, $this->Request()->getBaseUrl(), null, $this->Request()->isSecure(), true));

        // Disable http cache for this Request
        $this->Response()->headers->set('cache-control', 'private', true);

        $this->redirect(['controller' => 'index', 'action' => 'index']);

        return true;
    }
}
