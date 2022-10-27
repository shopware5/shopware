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

use Shopware\Components\CSRFTokenValidator;
use Shopware\Components\Random;

class Shopware_Controllers_Frontend_Csrftoken extends Enlight_Controller_Action
{
    /**
     * Loads auth and script renderer resource
     */
    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
    }

    /**
     * Generates a token and fills the cookie and session
     *
     * @return void
     */
    public function indexAction()
    {
        $token = Random::getAlphanumericString(30);

        $this->Response()->headers->set(CSRFTokenValidator::CSRF_TOKEN_RESPONSE_HEADER, $token);
    }
}
