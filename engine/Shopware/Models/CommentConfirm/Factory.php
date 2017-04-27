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

/**
 * Simple comment confirmation factory for reducing the amount of typed code 
 * when fetching comment confirm instances.
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

namespace Shopware\Models\CommentConfirm;

class Factory
{

    /**
     * Build a CommentConfirm Object from a hash. 
     * 
     * @param string $hash An optin comment confirmation hash
     * @throws \InvalidArgumentException If the optin is not found with the given hash
     * @return Shopware\MOdels\CommentConfirm\CommentConfirm
     */

    public static function getInstanceByHash($hash){

        $optin = Shopware()
        ->Models()
        ->getRepository('Shopware\Models\CommentConfirm\CommentConfirm')
        ->getConfirmationByHashQuery($hash)
        ->getOneOrNullResult();

        if(!$optin){

            throw new \InvalidArgumentException("Comment not found for the given hash");

        }

        return $optin;

    }

}
