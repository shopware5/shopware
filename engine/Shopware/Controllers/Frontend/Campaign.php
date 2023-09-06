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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;

class Shopware_Controllers_Frontend_Campaign extends Enlight_Controller_Action
{
    /**
     * Renders a shopping world as a landingpage.
     *
     * @throws Exception
     * @throws Enlight_Controller_Exception
     */
    public function indexAction()
    {
        $emotionId = (int) $this->Request()->getParam('emotionId');
        $shopContext = $this->get(ContextServiceInterface::class)->getShopContext();

        $result = $this->get('shopware.emotion.emotion_landingpage_loader')->load(
            $emotionId,
            $shopContext
        );

        $this->View()->assign(json_decode(json_encode($result), true));
    }
}
