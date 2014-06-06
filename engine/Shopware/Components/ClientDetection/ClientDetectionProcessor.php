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

namespace Shopware\Components\ClientDetection;

use Detection\MobileDetect;
use Symfony\Component\HttpFoundation\Request;

class ClientDetectionProcessor
{
    /**
     * White list of allowed device types
     *
     * @var array
     */
    private static $whiteList = array(
        'desktop',
        'tablet',
        'mobile',
    );

    /**
     * If not already present, injects a header into the Request
     * containing information about the client
     *
     * Use multiple detection strategies:
     * 1- Look for a X-UA-Device-force cookie (i.e.: use forces desktop on mobile)
     * 2- Look for header from external proxy
     * 3- Detect ourselves
     *
     * @param Request $request
     */
    public static function parseRequest(Request $request)
    {
        $device = null;
        if ($request->cookies->get('X-UA-Device-force')) {
            $device = $request->cookies->get('X-UA-Device-force');
        } elseif ($request->headers->has("X-UA-Device")) {
            $device = $request->headers->get("X-UA-Device");
        }

        if (!$device || !in_array($device, self::$whiteList)) {
            $detectionLib = new MobileDetect();
            if ($detectionLib->isTablet()) {
                $device = 'tablet';
            } elseif ($detectionLib->isMobile()) {
                $device = 'mobile';
            } else {
                $device = 'desktop';
            }
        }

        $request->headers->set('X-UA-Device', $device);
    }
}