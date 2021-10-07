<?php

declare(strict_types=1);
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

namespace Shopware\Bundle\ControllerBundle\Exceptions;

use Enlight_Controller_Exception as ControllerException;
use Enlight_Controller_Request_Request as Request;

class ResourceNotFoundException extends ControllerException
{
    public function __construct(string $exceptionMessagePrefix, Request $request)
    {
        $referer = $request->getHeader('referer');
        if (!\is_string($referer)) {
            $referer = 'notFound';
        }

        $message = sprintf(
            '%s. The request comes from: "%s". Module: "%s", Controller: "%s", Action: "%s",',
            $exceptionMessagePrefix,
            $referer,
            $request->getModuleName(),
            $request->getControllerName(),
            $request->getActionName()
        );

        parent::__construct($message, self::PROPERTY_NOT_FOUND);
    }
}
