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

class Migrations_Migration308 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * Increase the lenght of all fields that contain the session id
     * to allow usage of secure/long session hashes like session.hash_function="sha512"
     */
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE s_core_sessions_backend MODIFY id VARCHAR(128);
ALTER TABLE s_core_sessions MODIFY id VARCHAR(128);
ALTER TABLE s_core_auth MODIFY sessionID VARCHAR(128);
ALTER TABLE s_user MODIFY sessionID VARCHAR(128);
ALTER TABLE s_emarketing_lastarticles MODIFY sessionID VARCHAR(128);
ALTER TABLE s_order_basket MODIFY sessionID VARCHAR(128);
ALTER TABLE s_order_comparisons MODIFY sessionID VARCHAR(128);
EOD;
        $this->addSql($sql);
    }
}
