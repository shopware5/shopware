<?php

declare(strict_types=1);
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

use Shopware\Components\CSRFTokenValidator;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Random;

class Shopware_Controllers_Backend_CSRFToken extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Loads auth and script renderer resource
     */
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'generate',
        ];
    }

    /**
     * Generates a token and fills the cookie and session
     *
     * @return void
     */
    public function generateAction()
    {
        /** @var Enlight_Components_Session_Namespace $session */
        $session = $this->container->get('backendsession');

        $token = $session->get(CSRFTokenValidator::CSRF_TOKEN_HEADER);
        if (!\is_string($token)) {
            $token = Random::getAlphanumericString(30);
            $session->set(CSRFTokenValidator::CSRF_TOKEN_HEADER, $token);
        }

        $this->Response()->headers->set(CSRFTokenValidator::CSRF_TOKEN_RESPONSE_HEADER, $token);
    }
}
