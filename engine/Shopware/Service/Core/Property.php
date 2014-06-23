<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

/**
 * @package Shopware\Service\Core
 */
class Property implements Service\Property
{
    /**
     * @var Gateway\ProductProperty
     */
    private $productPropertyGateway;

    /**
     * @param Gateway\ProductProperty $productPropertyGateway
     */
    function __construct(Gateway\ProductProperty $productPropertyGateway)
    {
        $this->productPropertyGateway = $productPropertyGateway;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $properties = $this->getList(array($product), $context);

        return array_shift($properties);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products, Struct\Context $context)
    {
        $properties = $this->productPropertyGateway->getList($products, $context);

        return $properties;
    }
}
