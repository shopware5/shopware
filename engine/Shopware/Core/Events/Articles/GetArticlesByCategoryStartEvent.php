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

class GetArticlesByCategoryStartEvent extends Enlight_Event_EventArgs
{
    public const EVENT_NAME = 'Shopware_Modules_Articles_sGetArticlesByCategory_Start';

    public function getSubject(): sArticles
    {
        return $this->get('subject');
    }

    /**
     * @return int|string|null
     *
     * @deprecated use @see \Shopware\Core\Events\Articles\GetArticlesByCategoryStart::getCategoryId
     */
    public function getId()
    {
        return $this->get('id');
    }

    public function getCategoryId(): ?int
    {
        $categoryId = $this->get('categoryId');

        return $categoryId !== null ? (int) $categoryId : null;
    }
}
