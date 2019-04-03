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

namespace Shopware\Components\HttpCache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;

/**
 * Black Hole Storage for the Config
 */
class BlackHoleStore implements StoreInterface
{
    /**
     * {@inheritdoc}
     */
    public function lookup(Request $request)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Request $request, Response $response)
    {
        return 'dummy';
    }

    /**
     * {@inheritdoc}
     */
    public function invalidate(Request $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lock(Request $request)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function unlock(Request $request)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked(Request $request)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function purge($url)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
    }
}
