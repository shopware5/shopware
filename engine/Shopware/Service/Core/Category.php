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

use Shopware\Gateway;
use Shopware\Service;
use Shopware\Struct;

/**
 * @package Shopware\Service\Core
 */
class Category implements Service\Category
{
    /**
     * @var Gateway\Category
     */
    private $categoryGateway;

    /**
     * @param Gateway\Category $categoryGateway
     */
    function __construct(Gateway\Category $categoryGateway)
    {
        $this->categoryGateway = $categoryGateway;
    }

    /**
     * @inheritdoc
     */
    public function get($id, Struct\Context $context)
    {
        $categories = $this->getList(array($id), $context);
        return array_shift($categories);
    }

    /**
     * @inheritdoc
     */
    public function getList($ids, Struct\Context $context)
    {
        return $this->categoryGateway->getList($ids, $context);
    }
}
