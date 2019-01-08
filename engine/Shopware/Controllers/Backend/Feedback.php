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

use Shopware\Components\CacheManager;

/**
 * Empty controller due to the fact that we've no logic here. The Shopware_Controllers_Backend_ExtJs handles the rest.
 */
class Shopware_Controllers_Backend_Feedback extends Shopware_Controllers_Backend_ExtJs
{
    public function loadAction()
    {
        /** @var \Shopware\Components\ShopwareReleaseStruct $shopwareRelease */
        $shopwareRelease = $this->container->get('shopware.release');

        $this->View()->assign('SHOPWARE_VERSION', $shopwareRelease->getVersion());
        $this->View()->assign('SHOPWARE_VERSION_TEXT', $shopwareRelease->getVersionText());
        $this->View()->assign('SHOPWARE_REVISION', $shopwareRelease->getRevision());

        parent::loadAction();
    }

    public function disableInstallationSurveyAction()
    {
        $conn = $this->container->get('dbal_connection');
        $elementId = $conn->fetchColumn('SELECT id FROM s_core_config_elements WHERE name LIKE "installationSurvey"');
        $valueId = $conn->fetchColumn('SELECT id FROM s_core_config_values WHERE element_id = :elementId', ['elementId' => $elementId]);
        $data = [
            'element_id' => $elementId,
            'shop_id' => 1,
            'value' => serialize(false),
        ];
        if ($valueId) {
            $conn->update(
                's_core_config_values',
                $data,
                ['id' => $valueId]
            );
        } else {
            $conn->insert('s_core_config_values', $data);
        }
        /** @var CacheManager */
        $cacheManager = $this->get('shopware.cache_manager');
        $cacheManager->clearConfigCache();
    }
}
