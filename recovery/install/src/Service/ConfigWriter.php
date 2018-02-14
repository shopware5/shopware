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

namespace Shopware\Recovery\Install\Service;

use RuntimeException;
use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ConfigWriter
{
    /**
     * @var string
     */
    private $configPath;

    /**
     * @param string $configPath
     */
    public function __construct($configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * @param DatabaseConnectionInformation $info
     *
     * @throws RuntimeException
     */
    public function writeConfig(DatabaseConnectionInformation $info)
    {
        $mapping = [
            'databaseName' => 'db.database',
            'port' => 'db.port',
            'hostname' => 'db.host',
            'socket' => 'db.socket',
            'username' => 'db.user',
            'password' => 'db.password',
        ];

        $template = file_get_contents($this->configPath . '.dist');

        foreach (get_object_vars($info) as $key => $parameter) {
            if (!isset($mapping[$key])) {
                continue;
            }

            $template = str_replace('%' . $mapping[$key] . '%', $parameter, $template);
        }

        if (!file_put_contents($this->configPath, $template)) {
            throw new RuntimeException("Could not write config: $this->configPath");
        }
    }
}
