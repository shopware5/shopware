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

class Shopware_Controllers_Frontend_Media extends Enlight_Controller_Action
{
    /**
     * Disable any output
     */
    public function init()
    {
        $this->Front()->Plugins()->ScriptRenderer()->setRender(false);
        $this->Front()->Plugins()->ViewRenderer()->setNoRender(true);
        $this->Front()->Plugins()->Json()->setRenderer(false);
    }

    public function fallbackAction()
    {
        $mediaService = $this->get('shopware_media.media_service');
        $requestedImage = $this->Request()->getRequestUri();

        if (!$mediaService->has($requestedImage)) {
            $this->Response()->setStatusCode(404);

            return;
        }

        $url = $mediaService->getUrl($requestedImage);
        $logger = $this->get('corelogger');
        $logger->info('Legacy media url detected.', ['requestedUrl' => $requestedImage, 'redirectedTo' => $url]);

        $this->redirect($url, ['code' => 301]);
    }
}
