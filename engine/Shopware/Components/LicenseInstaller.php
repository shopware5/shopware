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

namespace Shopware\Components;

use Doctrine\DBAL\Connection;
use Shopware\Components\License\Struct\LicenseInformation;

class LicenseInstaller
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $pdo)
    {
        $this->connection = $pdo;
    }

    /**
     * @throws \RuntimeException
     */
    public function installLicense(LicenseInformation $license)
    {
        try {
            // Delete previous inserted licenses
            $sql = "DELETE FROM s_core_licenses WHERE module = 'SwagCommercial'";
            $this->connection->query($sql);

            // Insert new license
            $sql = <<<'EOT'
INSERT INTO s_core_licenses (module,host,label,license,version,type,source,added,creation,expiration,active)
VALUES (:module,:host,:label,:license,:version,:type,:source,now(),:creation,:expiration,1)
EOT;

            $prepareStatement = $this->connection->prepare($sql);
            $prepareStatement->execute([
                ':module' => $license->module,
                ':host' => $license->host,
                ':label' => $license->label,
                ':license' => $license->license,
                ':version' => $license->version,
                ':type' => $license->type,
                ':source' => $license->source,
                ':creation' => $this->checkDate($license->creation),
                ':expiration' => $this->checkDate($license->expiration),
            ]);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Could not insert license into database', 0, $e);
        }
    }

    /**
     * Checks if a date string is plausible.
     * If not, returns null. Otherwise the string.
     *
     * @param string $date
     *
     * @return string|null
     */
    private function checkDate($date)
    {
        $dateCheck = strtotime($date);

        return is_int($dateCheck) && $dateCheck > 0 ? $date : null;
    }
}
