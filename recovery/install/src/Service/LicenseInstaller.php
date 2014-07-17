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

use Shopware\Recovery\Install\Struct\LicenseInformation;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Install\Service
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LicenseInstaller
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param  LicenseInformation $license
     * @throws \RuntimeException
     */
    public function installLicense(LicenseInformation $license)
    {
        try {
            // Delete previous inserted licenses
            $sql = "DELETE FROM s_core_licenses WHERE module = 'SwagCommercial'";
            $this->pdo->query($sql);

            // Insert new license
            $sql = <<<EOT
INSERT INTO s_core_licenses (module,host,label,license,version,type,source,added,creation,expiration,active)
VALUES (?,?,?,?,'1.0.0',1,0,now(),now(),NULL,1)
EOT;

            $prepareStatement = $this->pdo->prepare($sql);
            $prepareStatement->execute([
                $license->module,
                $license->host,
                $license->label,
                $license->license
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Could not insert license into database", 0, $e);
        }
    }
}
