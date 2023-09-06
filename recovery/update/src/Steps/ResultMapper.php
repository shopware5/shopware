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

namespace Shopware\Recovery\Update\Steps;

use Exception;

class ResultMapper
{
    /**
     * @param ValidResult|FinishResult|ErrorResult $result
     *
     * @throws Exception
     *
     * @return array
     */
    public function toExtJs($result)
    {
        if ($result instanceof ValidResult) {
            return [
                'valid' => true,
                'offset' => $result->getOffset(),
                'total' => $result->getTotal(),
                'success' => true,
            ];
        }

        if ($result instanceof FinishResult) {
            return [
                'valid' => false,
                'offset' => $result->getOffset(),
                'total' => $result->getTotal(),
                'success' => true,
            ];
        }

        if ($result instanceof ErrorResult) {
            return [
                'valid' => false,
                'errorMsg' => $result->getMessage(),
            ];
        }

        throw new Exception(sprintf('Result type %s can not be mapped.', \get_class($result)));
    }
}
