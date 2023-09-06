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

namespace Shopware\Tests\Functional\Components\DependencyInjection;

/**
 * Just a test service
 */
class DecoratingService extends OriginalService
{
    /**
     * @var OriginalService
     */
    private $originalService;

    public function __construct(OriginalService $originalService)
    {
        $this->originalService = $originalService;
    }

    public function getOriginalClass(): string
    {
        return \get_class($this->originalService);
    }

    public function getName(): string
    {
        return self::class;
    }
}
