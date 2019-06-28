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

use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;

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
     * @throws \RuntimeException
     */
    public function writeConfig(DatabaseConnectionInformation $info)
    {
        $databaseConfigFile = $this->configPath;

        $config = [
            'db' => [],
        ];

        $mapping = [
            'databaseName' => 'dbname',
            'hostname' => 'host',
        ];

        foreach ($info as $key => $parameter) {
            if ($key == 'port' && empty($parameter)) {
                continue;
            }
            if ($key == 'socket' && empty($parameter)) {
                continue;
            }

            if (isset($mapping[$key])) {
                $key = $mapping[$key];
            }

            $config['db'][$key] = trim($parameter);
        }

        $template = '<?php return ' . var_export($config, true) . ';';
        if (!file_put_contents($databaseConfigFile, $template)) {
            throw new \RuntimeException("Could not write config: $databaseConfigFile");
        }
    }
}
