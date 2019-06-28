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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

class LegacyEventManager
{
    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(
        \Enlight_Event_EventManager $eventManager,
        ContextServiceInterface $contextService
    ) {
        $this->eventManager = $eventManager;
        $this->contextService = $contextService;
    }

    /**
     * Following events are deprecated and only implemented for backward compatibility to shopware 4
     * Removed with shopware 5.1
     *
     * @param int $categoryId
     */
    public function fireArticlesByCategoryEvents(
        array $result,
        $categoryId,
        \sArticles $module
    ) {
        foreach ($result['sArticles'] as &$product) {
            $product = Shopware()->Events()->filter(
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
     * Removed with shopware 5.1
     *
     * @return array|mixed
     */
    public function fireArticleByIdEvents(array $product, \sArticles $module)
    {
        $getArticle = $product;
        $context = $this->contextService->getShopContext();

        return Shopware()->Events()->filter(
            'Shopware_Modules_Articles_GetArticleById_FilterResult',
            $getArticle,
            [
                'subject' => $module,
                'id' => $getArticle['articleID'],
                'isBlog' => false,
                'customergroup' => $context->getCurrentCustomerGroup()->getKey(),
            ]
        );
    }
}
