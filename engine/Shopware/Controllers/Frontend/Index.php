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

use Shopware\Bundle\EmotionBundle\Service\StoreFrontEmotionDeviceConfiguration;
use Shopware\Bundle\EmotionBundle\Service\StoreFrontEmotionDeviceConfigurationInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\OptinServiceInterface;
use Shopware\Models\CustomerStream\CustomerStreamRepositoryInterface;
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
        $context = Shopware()->Container()->get(ContextServiceInterface::class)->getShopContext();
        $categoryId = $context->getShop()->getCategory()->getId();

        /** @var StoreFrontEmotionDeviceConfiguration $service */
        $service = $this->get(StoreFrontEmotionDeviceConfigurationInterface::class);
        $emotions = $service->getCategoryConfiguration($categoryId, $context);

        $categoryContent = Shopware()->Modules()->Categories()->sGetCategoryContent($categoryId);

        $this->View()->assign([
            'hasCustomerStreamEmotion' => $this->container->get(CustomerStreamRepositoryInterface::class)->hasCustomerStreamEmotions($categoryId),
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

        $optinService = $this->container->get(OptinServiceInterface::class);

        $data = $optinService->get(OptinServiceInterface::TYPE_THEME_PREVIEW, $hash);

        if (!$data) {
            return false;
        }

        $optinService->delete(OptinServiceInterface::TYPE_THEME_PREVIEW, $hash);

        $this->Response()->headers->setCookie(
            new Cookie(
                $data['sessionName'],
                $data['sessionValue'],
                0,
                $this->Request()->getBaseUrl(),
                null,
                $this->Request()->isSecure()
            )
        );

        // Disable http cache for this Request
        $this->Response()->headers->set('cache-control', 'private');

        $this->redirect(['controller' => 'index', 'action' => 'index']);

        return true;
    }
}
