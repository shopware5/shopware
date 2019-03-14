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

namespace ShopwarePlugins\SwagUpdate\Components\Checks;

use Doctrine\DBAL\Connection;
use Enlight_Components_Snippet_Namespace as SnippetNamespace;
use ShopwarePlugins\SwagUpdate\Components\CheckInterface;
use ShopwarePlugins\SwagUpdate\Components\Validation;

class MySQLVersionCheck implements CheckInterface
{
    const CHECK_TYPE = 'mysqlversion';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SnippetNamespace
     */
    private $namespace;

    public function __construct(Connection $connection, SnippetNamespace $namespace)
    {
        $this->connection = $connection;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function canHandle($requirement)
    {
        return $requirement['type'] == self::CHECK_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function check($requirement)
    {
        $conn = Shopware()->Container()->get('dbal_connection');
        $version = $conn->fetchColumn('SELECT VERSION()');

        $minMySQLVersion = $requirement['value'];

        $validVersion = (version_compare($version, $minMySQLVersion) >= 0);

        $successMessage = $this->namespace->get('controller/check_mysqlversion_success', 'Min MySQL Version: %s, your version %s');
        $failMessage = $this->namespace->get('controller/check_mysqlversion_failure', 'Min MySQL Version %s, your version %s');

        if ($validVersion) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_VALID,
                'message' => sprintf(
                    $successMessage,
                    $minMySQLVersion,
                    $version
                ),
            ];
        }

        return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => $requirement['level'],
                'message' => sprintf(
                    $failMessage,
                    $minMySQLVersion,
                    $version
                ),
            ];
    }
}
