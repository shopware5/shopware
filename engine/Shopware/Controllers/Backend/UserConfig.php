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

class Shopware_Controllers_Backend_UserConfig extends Shopware_Controllers_Backend_ExtJs
{
    public function getAction()
    {
        $identity = (int) $this->container->get('auth')->getIdentity()->id;

        $name = $this->Request()->getParam('name');

        $config = $this->container->get('dbal_connection')->fetchColumn(
            'SELECT config FROM s_core_auth_config WHERE user_id = :id AND `name` = :name',
            [':id' => $identity, ':name' => $name]
        );

        $this->View()->assign(json_decode($config, true));
    }

    public function saveAction()
    {
        $identity = (int) $this->container->get('auth')->getIdentity()->id;

        $name = $this->Request()->getParam('name');

        $config = $this->Request()->getParam('config');

        $this->container->get('dbal_connection')->executeUpdate(
            'INSERT INTO s_core_auth_config (user_id, `name`, `config`) 
             VALUES (:id, :name, :config) 
             ON DUPLICATE KEY UPDATE `config`= :config',
            [':id' => $identity, ':name' => $name, ':config' => $config]
        );

        $this->View()->assign('success', true);
    }
}
