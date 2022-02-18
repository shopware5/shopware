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

namespace Shopware\Components\Compatibility;

use sArticles;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\ContainerAwareEventManager;

/**
 * @deprecated - Will be removed with shopware 5.8 without replacement
 * @phpstan-import-type ListingArray from \sArticles
 */
class LegacyEventManager
{
    private ContainerAwareEventManager $eventManager;

    private ContextServiceInterface $contextService;

    public function __construct(
        ContainerAwareEventManager $eventManager,
        ContextServiceInterface $contextService
    ) {
        $this->eventManager = $eventManager;
        $this->contextService = $contextService;
    }

    /**
     * Following events are deprecated and only implemented for backward compatibility to shopware 4
     *
     * @deprecated - Will be removed with shopware 5.8
     *
     * @param ListingArray $result
     * @param int|null     $categoryId
     *
     * @return ListingArray
     */
    public function fireArticlesByCategoryEvents(
        array $result,
        $categoryId,
        sArticles $module
    ) {
        foreach ($result['sArticles'] as &$product) {
            $product = $this->eventManager->filter(
                'Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopEnd',
                $product,
                [
                    'subject' => $module,
                    'id' => $categoryId,
                ]
            );
        }

        return $this->eventManager->filter(
            'Shopware_Modules_Articles_sGetArticlesByCategory_FilterResult',
            $result,
            [
                'subject' => $module,
                'id' => $categoryId,
            ]
        );
    }

    /**
     * Following events are deprecated and only implemented for backward compatibility to shopware 4
     *
     * @deprecated - Will be removed with shopware 5.8
     *
     * @param array<string, mixed> $product
     *
     * @return array<string, mixed>
     */
    public function fireArticleByIdEvents(array $product, sArticles $module)
    {
        $context = $this->contextService->getShopContext();

        return $this->eventManager->filter(
            'Shopware_Modules_Articles_GetArticleById_FilterResult',
            $product,
            [
                'subject' => $module,
                'id' => $product['articleID'],
                'isBlog' => false,
                'customergroup' => $context->getCurrentCustomerGroup()->getKey(),
            ]
        );
    }
}
