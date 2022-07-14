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

class Migrations_Migration620 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->getConnection()->prepare('SHOW COLUMNS FROM `s_campaigns_mailings`;');
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_COLUMN);

        if (!\in_array('timed_delivery', $result)) {
            $this->addTimedDeliveryColumn();
        }
    }

    private function addTimedDeliveryColumn()
    {
        $sql = <<<EOD
            ALTER TABLE `s_campaigns_mailings` ADD COLUMN `timed_delivery` DATETIME DEFAULT NULL;
EOD;
        $this->addSql($sql);
    }
}
