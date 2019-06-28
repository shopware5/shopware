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

namespace Shopware\Bundle\ContentTypeBundle\FieldResolver;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;

class ProductResolver extends AbstractResolver
{
    /**
     * @var ListProductServiceInterface
     */
    private $listProductService;

    /**
     * @var LegacyStructConverter
     */
    private $converter;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(ListProductServiceInterface $listProductService, LegacyStructConverter $converter, ContextServiceInterface $contextService)
    {
        $this->listProductService = $listProductService;
        $this->converter = $converter;
        $this->contextService = $contextService;
    }

    public function resolve(): void
    {
        if (!empty($this->resolveIds)) {
            $products = $this->listProductService->getList($this->resolveIds, $this->contextService->getShopContext());

            foreach ($products as $product) {
                $this->storage[$product->getNumber()] = $this->converter->convertListProductStruct($product);
            }

            $this->resolveIds = [];
        }
    }
}
