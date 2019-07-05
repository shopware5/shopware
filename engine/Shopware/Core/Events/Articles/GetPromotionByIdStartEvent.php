<?php declare(strict_types=1);
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

namespace Shopware\Core\Events\Articles;

use Enlight_Event_EventArgs;
use sArticles;

class GetPromotionByIdStartEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Articles_GetPromotionById_Start';

    public function getSubject(): sArticles
    {
        return $this->get('subject');
    }

    public function getMode(): string
    {
        return $this->get('mode');
    }

    /**
     * @deprecated use @see \Shopware\Core\Events\Articles\GetPromotionByIdStart::getCategoryId
     */
    public function getCategory(): int
    {
        return $this->get('category');
    }

    public function getCategoryId(): int
    {
        return $this->get('categoryId');
    }

    /**
     * returns the product id or product number
     *
     * @return int|string
     *
     * @deprecated use @see \Shopware\Core\Events\Articles\GetPromotionByIdStart::getProduct
     */
    public function getValue()
    {
        return $this->get('value');
    }

    /**
     * returns the product id or product number
     *
     * @return int|string
     */
    public function getProduct()
    {
        return $this->get('product');
    }
}
