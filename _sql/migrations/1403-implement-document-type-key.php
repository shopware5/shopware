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
class Migrations_Migration1403 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add the standard document keys as well as a fallback naming for documents added by plugins.
        $sql = <<<'EOF'
ALTER TABLE `s_core_documents`
    ADD COLUMN `key` varchar(255) COLLATE utf8_unicode_ci;
UPDATE s_core_documents as doc
	SET `key` = (CASE WHEN id = 1 THEN 'invoice'
					  WHEN id = 2 THEN 'delivery_note'
					  WHEN id = 3 THEN 'credit'
					  WHEN id = 4 THEN 'cancellation'
					  ELSE CONCAT(doc.name, "_", doc.id)
                 END
	);
ALTER TABLE `s_core_documents`
    MODIFY COLUMN `key` varchar(255) COLLATE utf8_unicode_ci NULL UNIQUE;
EOF;
        // Column `key` can initially be null due to blue/green deployment concerns

        $this->addSql($sql);
    }
}
