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

class Migrations_Migration1463 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
-- backend controller renamed
UPDATE IGNORE s_core_acl_resources SET `name` = 'cache' WHERE `name` = 'performance';

-- add missing acls for api endpoints
INSERT INTO s_core_acl_resources (`name`) SELECT 'address' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'address');
SET @addressResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'address' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @addressResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @addressResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @addressResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @addressResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @addressResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @addressResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @addressResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @addressResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'cache' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'cache');
SET @cacheResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'cache' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @cacheResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @cacheResourceId AND `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @cacheResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @cacheResourceId AND `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'country' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'country');
SET @countryResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'country' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @countryResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @countryResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @countryResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @countryResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @countryResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @countryResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @countryResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @countryResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'customergroup' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'customergroup');
SET @customergroupResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'customergroup' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @customergroupResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @customergroupResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @customergroupResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @customergroupResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @customergroupResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @customergroupResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @customergroupResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @customergroupResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'manufacturer' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'manufacturer');
SET @manufacturerResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'manufacturer' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @manufacturerResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @manufacturerResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @manufacturerResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @manufacturerResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @manufacturerResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @manufacturerResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @manufacturerResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @manufacturerResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'media' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'media');
SET @mediaResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'media' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @mediaResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @mediaResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @mediaResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @mediaResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @mediaResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @mediaResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @mediaResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @mediaResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'propertygroup' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'propertygroup');
SET @propertygroupResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'propertygroup' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @propertygroupResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @propertygroupResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @propertygroupResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @propertygroupResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @propertygroupResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @propertygroupResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @propertygroupResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @propertygroupResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'shop' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'shop');
SET @shopResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'shop' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @shopResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @shopResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @shopResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @shopResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @shopResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @shopResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @shopResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @shopResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'translation' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'translation');
SET @translationResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'translation' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @translationResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @translationResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @translationResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @translationResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @translationResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @translationResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @translationResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @translationResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'variant' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'variant');
SET @variantResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'variant' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @variantResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @variantResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @variantResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @variantResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @variantResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @variantResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @variantResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @variantResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'paymentmethods' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'paymentmethods');
SET @paymentmethodsResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'paymentmethods' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @paymentmethodsResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @paymentmethodsResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @paymentmethodsResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @paymentmethodsResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @paymentmethodsResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @paymentmethodsResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @paymentmethodsResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @paymentmethodsResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'emotionpreset' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'emotionpreset');
SET @emotionpresetResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'emotionpreset' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @emotionpresetResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @emotionpresetResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @emotionpresetResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @emotionpresetResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @emotionpresetResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @emotionpresetResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @emotionpresetResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @emotionpresetResourceId AND  `name` = 'delete');

INSERT INTO s_core_acl_resources (`name`) SELECT 'user' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_resources WHERE `name` = 'user');
SET @userResourceId = (SELECT id FROM s_core_acl_resources WHERE `name` = 'user' LIMIT 1);
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @userResourceId, 'create' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @userResourceId AND `name` = 'create');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @userResourceId, 'read' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @userResourceId AND  `name` = 'read');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @userResourceId, 'update' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @userResourceId AND  `name` = 'update');
INSERT INTO s_core_acl_privileges (`resourceID`, `name`) SELECT @userResourceId, 'delete' FROM dual WHERE NOT EXISTS (SELECT * FROM s_core_acl_privileges WHERE resourceID = @userResourceId AND  `name` = 'delete');
SQL;
        $this->addSql($sql);
    }
}
