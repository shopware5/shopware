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
  `privilege_id` int(11) unsigned NOT NULL,
  `required_privilege_id` int(11) unsigned NOT NULL,
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
            'article_read' => [
                'category_read',
                'mediamanager_read',
            ],
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
