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

namespace Shopware\Bundle\EmotionBundle\Service;

use Shopware\Bundle\EmotionBundle\Service\Gateway\EmotionGateway;
use Shopware\Bundle\EmotionBundle\Struct\Emotion;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use Shopware\Bundle\StoreFrontBundle\Shop\ShopGateway;

class EmotionService implements EmotionServiceInterface
{
    /**
     * @var EmotionGateway
     */
    private $gateway;

    /**
     * @var EmotionElementService
     */
    private $elementService;

    /**
     * @var ShopGateway
     */
    private $shopGateway;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Category\CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @param EmotionGateway                                                      $gateway
     * @param EmotionElementServiceInterface                                      $elementService
     * @param ShopGateway                                                         $shopGateway
     * @param \Shopware\Bundle\StoreFrontBundle\Category\CategoryServiceInterface $categoryService
     */
    public function __construct(
        EmotionGateway $gateway,
        EmotionElementServiceInterface $elementService,
        ShopGateway $shopGateway,
        \Shopware\Bundle\StoreFrontBundle\Category\CategoryServiceInterface $categoryService
    ) {
        $this->gateway = $gateway;
        $this->elementService = $elementService;
        $this->shopGateway = $shopGateway;
        $this->categoryService = $categoryService;
    }

    /**
     * @param array                                                          $emotionIds
     * @param \Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface $context
     *
     * @return Emotion[]
     */
    public function getList(array $emotionIds, ShopContextInterface $context)
    {
        $emotions = $this->gateway->getList($emotionIds, $context);
        $elements = $this->elementService->getList($emotionIds, $context);

        $this->resolveCategories($emotions, $context);
        $this->resolveShops($emotions, $context->getTranslationContext());

        $result = [];
        foreach ($emotionIds as $emotionId) {
            if (!array_key_exists($emotionId, $emotions)) {
                continue;
            }

            $emotion = $emotions[$emotionId];

            if (array_key_exists($emotionId, $elements)) {
                $emotion->setElements($elements[$emotionId]);
            }

            $result[$emotionId] = $emotion;
        }

        return $result;
    }

    /**
     * @param Emotion[]            $emotions
     * @param ShopContextInterface $context
     */
    private function resolveCategories(array $emotions, ShopContextInterface $context)
    {
        $categoryIds = array_map(function (Emotion $emotion) {
            return $emotion->getCategoryIds();
        }, $emotions);

        $categoryIds = array_keys(array_flip(array_merge(...$categoryIds)));
        $categoryIds = array_filter($categoryIds);

        if (empty($categoryIds)) {
            return;
        }

        $categories = $this->categoryService->getList($categoryIds, $context);

        /** @var Emotion $emotion */
        foreach ($emotions as $emotion) {
            $emotion->setCategories(
                array_intersect_key($categories, array_flip($emotion->getCategoryIds()))
            );
        }
    }

    /**
     * @param Emotion[]                                                    $emotions
     * @param \Shopware\Bundle\StoreFrontBundle\Context\TranslationContext $context
     */
    private function resolveShops(array $emotions, TranslationContext $context)
    {
        $shopIds = array_map(function (Emotion $emotion) {
            return $emotion->getShopIds();
        }, $emotions);

        $shopIds = array_keys(array_flip(array_merge(...$shopIds)));
        $shopIds = array_filter($shopIds);

        if (empty($shopIds)) {
            return;
        }

        $shops = $this->shopGateway->getList($shopIds, $context);

        /** @var Emotion $emotion */
        foreach ($emotions as $emotion) {
            $emotion->setShops(
                array_intersect_key($shops, array_flip($emotion->getShopIds()))
            );
        }
    }
}
