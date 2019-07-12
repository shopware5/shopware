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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration1632 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->addDependencyTable();

        $privilegeMapping = $this->getPrivilegeMapping();
        $requirementMatrix = $this->getRequirementMatrix();

        $this->addPrivilegeRequirements($privilegeMapping, $requirementMatrix);
    }

    private function addDependencyTable()
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `s_core_acl_privilege_requirements` (
  `privilege_id` int(11) UNSIGNED NOT NULL,
  `required_privilege_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`privilege_id`,`required_privilege_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $this->addSql($sql);
    }

    private function getPrivilegeMapping()
    {
        $resources = $this->connection->query('SELECT r.name as `resource`, p.id as `privilegeId`, p.name as `privilege` from `s_core_acl_resources` r LEFT JOIN `s_core_acl_privileges` p ON r.id = p.resourceID')->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);

        $mapping = [];

        foreach ($resources as $key => $privileges) {
            if (!array_key_exists($key, $mapping)) {
                $mapping[$key] = [];
            }
            foreach ($privileges as $privilege) {
                $mapping[$key][$privilege['privilege']] = $privilege['privilegeId'];
            }
        }

        return $mapping;
    }

    private function getRequirementMatrix()
    {
        $matrix = [
            'analytics_read' => [
                'overview_read'
            ],
            'article_read' => [
                'category_read',
                'mediamanager_read',
                'emotion_read',
                'mediamanager_upload',
                'articlelist_read'
            ],
            'article_delete' => [
                'article_read',
            ],
            'article_save' => [
                'article_read',
                'mediamanager_upload',
            ],
            'articlelist_read' => [
                'article_read'
            ],
            'articlelist_createFilters' => [
                'articlelist_read',
            ],
            'articlelist_editFilters' => [
                'articlelist_read',
            ],
            'articlelist_deleteFilters' => [
                'articlelist_read',
            ],
            'articlelist_editSingleArticle' => [
                'article_save',
                'article_delete',
                'articlelist_read',
            ],
            'articlelist_doMultiEdit' => [
                'articlelist_read',
                'article_save',
                'articlelist_doBackup'
            ],
            'articlelist_doBackup' => [
                'articlelist_read',
                'articlelist_doMultiEdit',
                'articlelist_editSingleArticle',
            ],
            'attributes_update' => [
                'attributes_read'
            ],
            'banner_create' => [
                'category_read',
                'mediamanager_read',
                'mediamanager_create',
                'mediamanager_upload',
            ],
            'banner_read' => [
                'category_read',
                'mediamanager_read',
                'mediamanager_create',
                'mediamanager_upload',
                'banner_read'
            ],
            'banner_update' => [
                'banner_read'
            ],
            'banner_delete' => [
                'banner_read'
            ],
            'blog_read' => [
                'mediamanager_read',
                'category_read',
            ],
            'blog_delete' => [
                'blog_read'
            ],
            'blog_update' => [
                'blog_read',
            ],
            'category_create' => [
                'article_read',
                'article_save',
                'mediamanager_read',
                'mediamanager_upload',
                'mediamanager_update',
                'mediamanager_create'
            ],
            'category_read' => [
                'article_read',
                'mediamanager_read'
            ],
            'category_update' => [
                'category_read',
                'mediamanager_update',
            ],
            'category_delete' => [
                'category_read'
            ],
            'config_update' => [
                'config_read'
            ],
            'config_delete' => [
                'config_read'
            ],
            'contenttypemanager_edit' => [
                'contenttypemanager_read'
            ],
            'contenttypemanager_delete' => [
                'contenttypemanager_read'
            ],
            'customer_read' => [
                'mediamanager_read',
                'emotion_read',
                'customerstream_read',
                'customerstream_charts'
            ],
            'customer_update' => [
                'customer_read'
            ],
            'customer_delete' => [
                'customer_read'
            ],
            'customer_detail' => [
                'customer_read'
            ],
            'customerstream_read' => [
                'customer_read'
            ],
            'customerstream_save' => [
                'customerstream_read'
            ],
            'customerstream_delete' => [
                'customerstream_read'
            ],
            'customerstream_charts' => [
                'customerstream_read'
            ],
            'emotion_read' => [
                'article_read',
                'blog_read',
                'category_read',
                'mediamanager_read',
                'supplier_read'
            ],
            'emotion_create' => [
                'emotion_read',
                'mediamanager_create',
                'mediamanager_update',
                'mediamanager_upload',
            ],
            'emotion_update' => [
                'emotion_read',
            ],
            'emotion_delete' => [
                'emotion_read',
            ],
            'form_createupdate' => [
                'form_read'
            ],
            'form_delete' => [
                'form_read'
            ],
            'log_delete' => [
                'log_read'
            ],
            'log_system' => [
                'log_read'
            ],
            'mail_update' => [
                'mail_read'
            ],
            'mail_delete' => [
                'mail_read'
            ],
            'mediamanager_delete' => [
                'mediamanager_read'
            ],
            'mediamanager_upload' => [
                'mediamanager_read'
            ],
            'mediamanager_update' => [
                'mediamanager_read'
            ],
            'newslettermanager_delete' => [
                'newslettermanager_read'
            ],
            'newslettermanager_read' => [
                'customer_read',
                'mediamanager_read',
                'category_read'
            ],
            'newslettermanager_write' => [
                'newslettermanager_read'
            ],
            'notification_read' => [
                'customer_read'
            ],
            'order_read' => [
                'customer_read',
            ],
            'order_update' => [
                'order_read'
            ],
            'order_delete' => [
                'order_read'
            ],
            'partner_create' => [
                'customer_read'
            ],
            'partner_read' => [
                'customer_read'
            ],
            'partner_update' => [
                'partner_read'
            ],
            'partner_delete' => [
                'partner_read'
            ],
            'payment_create' => [
                'payment_read'
            ],
            'payment_update' => [
                'payment_read'
            ],
            'payment_delete' => [
                'payment_read'
            ],
            'performance_update' => [
                'performance_read'
            ],
            'performance_clear' => [
                'performance_read'
            ],
            'pluginmanager_upload' => [
                'pluginmanager_read'
            ],
            'pluginmanager_download' => [
                'pluginmanager_read'
            ],
            'pluginmanager_install' => [
                'pluginmanager_read'
            ],
            'pluginmanager_update' => [
                'pluginmanager_read'
            ],
            'pluginmanager_notification' => [
                'pluginmanager_read'
            ],
            'premium_update' => [
                'premium_read',
            ],
            'premium_delete' => [
                'premium_read'
            ],
            'productfeed_create' => [
                'article_read'
            ],
            'productfeed_read' => [
                'article_read'
            ],
            'productfeed_update' => [
                'productfeed_read'
            ],
            'productfeed_delete' => [
                'productfeed_read'
            ],
            'productfeed_generate' => [
                'productfeed_read'
            ],
            'productfeed_sqli' => [
                'productfeed_read'
            ],
            'riskmanagement_read' => [
                'premium_read',
                'config_read'
            ],
            'riskmanagement_save' => [
                'riskmanagement_read',
            ],
            'riskmanagement_delete' => [
                'riskmanagement_read',
            ],
            'shipping_create' => [
                'category_read',
                'payment_read',
            ],
            'shipping_read' => [
                'category_read',
                'payment_read',
            ],
            'shipping_update' => [
                'shipping_read',
            ],
            'shipping_delete' => [
                'shipping_read',
            ],
            'site_createGroup' => [
                'mediamanager_read'
            ],
            'site_read' => [
                'mediamanager_read'
            ],
            'site_createSite' => [
                'site_read'
            ],
            'site_updateSite' => [
                'site_read'
            ],
            'site_deleteSite' => [
                'site_read'
            ],
            'site_deleteGroup' => [
                'site_read'
            ],
            'snippet_update' => [
                'snippet_read'
            ],
            'snippet_delete' => [
                'snippet_read'
            ],
            'supplier_update' => [
                'supplier_read'
            ],
            'supplier_delete' => [
                'supplier_read'
            ],
            'swagupdate_update' => [
                'swagupdate_read',
                'swagupdate_notification',
            ],
            'swagupdate_notification' => [
                'swagupdate_read',
            ],
            'swagupdate_skipUpdate' => [
                'swagupdate_read',
                'swagupdate_notification',
            ],
            'theme_preview' => [
                'theme_read'
            ],
            'theme_changeTheme' => [
                'theme_read'
            ],
            'theme_createTheme' => [
                'theme_read'
            ],
            'theme_uploadTheme' => [
                'theme_read'
            ],
            'theme_configureTheme' => [
                'theme_read'
            ],
            'theme_configureSystem' => [
                'theme_read'
            ],
            'usermanager_update' => [
                'usermanager_read'
            ],
            'usermanager_delete' => [
                'usermanager_read'
            ],
            'vote_read' => [
                'article_read',
                'customer_read',
                'mediamanager_read',
            ],
            'vote_accept' => [
                'vote_read',
            ],
            'vote_comment' => [
                'vote_read',
            ],
            'vote_delete' => [
                'vote_read',
            ],
            'voucher_create' => [
                'config_read',
                'supplier_read',
                'article_read',
                'customerstream_read',
                'customer_read'
            ],
            'voucher_read' => [
                'config_read',
                'supplier_read',
                'article_read',
                'customerstream_read',
                'customer_read'
            ],
            'voucher_update' => [
                'voucher_read'
            ],
            'voucher_delete' => [
                'voucher_read'
            ],
            'voucher_export' => [
                'voucher_read'
            ],
            'voucher_generate' => [
                'voucher_read'
            ],
            'widgets_swag-upload-widget' => [
                'mediamanager_upload',
                'widgets_read'
            ]
        ];

        return $matrix;
    }

    private function addPrivilegeRequirements(array $privilegeMapping, array $requirementMatrix)
    {
        foreach ($requirementMatrix as $key => $requirements) {
            $mapping = explode('_', $key);
            $resource = $mapping[0];
            $resourcePrivilege = $mapping[1];

            foreach ($requirements as $requirement) {
                $requirementMapping = explode('_', $requirement);
                $requirementResource = $requirementMapping[0];
                $requirementPrivilege = $requirementMapping[1];

                $this->addSql('INSERT INTO `s_core_acl_privilege_requirements` (`privilege_Id`, `required_privilege_id`) VALUES (' . $privilegeMapping[$resource][$resourcePrivilege] . ', ' . $privilegeMapping[$requirementResource][$requirementPrivilege] . ')');
            }
        }
    }
}
