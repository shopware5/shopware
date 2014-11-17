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

namespace ShopwarePlugins\SwagUpdate\Components;

use ShopwarePlugins\SwagUpdate\Components\Steps\ErrorResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;

/**
 * @category  Shopware
 * @package   ShopwarePlugins\SwagUpdate\Components;
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ExtJsResultMapper
{
    /**
     * @param $result ValidResult|FinishResult|ErrorResult
     *
     * @return array
     * @throws \Exception
     */
    public function toExtJs($result)
    {
        if ($result instanceof ValidResult) {
            return array(
                'valid'   => true,
                'offset'  => $result->getOffset(),
                'total'   => $result->getTotal(),
                'success' => true,
            );
        }

        if ($result instanceof FinishResult) {
            return array(
                'valid'   => false,
                'offset'  => $result->getOffset(),
                'total'   => $result->getTotal(),
                'success' => true
            );
        }

        if ($result instanceof ErrorResult) {
            return array(
                'valid'    => false,
                'errorMsg' => $result->getMessage(),
            );
        }

        throw new \Exception(sprintf("Result type %s can not be mapped.", get_class($result)));
    }
}
