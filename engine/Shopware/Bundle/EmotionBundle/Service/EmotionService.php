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
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class EmotionService implements EmotionServiceInterface
{
    /**
     * @var EmotionGateway
     */
    private $gateway;

    /**
     * @var EmotionElementServiceInterface
     */
    private $elementService;

    /**
     * @var ShopGatewayInterface
     */
    private $shopGateway;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    public function __construct(
        EmotionGateway $gateway,
        EmotionElementServiceInterface $elementService,
        ShopGatewayInterface $shopGateway,
        CategoryServiceInterface $categoryService
    ) {
        $this->gateway = $gateway;
        $this->elementService = $elementService;
        $this->shopGateway = $shopGateway;
        $this->categoryService = $categoryService;
    }

    /**
     * @return Emotion[]
     */
    public function getList(array $emotionIds, ShopContextInterface $context)
    {
        $emotions = $this->gateway->getList($emotionIds, $context);
        $elements = $this->elementService->getList($emotionIds, $context);

        $this->resolveCategories($emotions, $context);
        $this->resolveShops($emotions);

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
     * @param Emotion[] $emotions
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
     * @param Emotion[] $emotions
     */
    private function resolveShops(array $emotions)
    {
        $shopIds = array_map(function (Emotion $emotion) {
            return $emotion->getShopIds();
        }, $emotions);

        $shopIds = array_keys(array_flip(array_merge(...$shopIds)));
        $shopIds = array_filter($shopIds);

        if (empty($shopIds)) {
            return;
        }

        $shops = $this->shopGateway->getList($shopIds);

        /** @var Emotion $emotion */
        foreach ($emotions as $emotion) {
            $emotion->setShops(
                array_intersect_key($shops, array_flip($emotion->getShopIds()))
            );
        }
    }
}
