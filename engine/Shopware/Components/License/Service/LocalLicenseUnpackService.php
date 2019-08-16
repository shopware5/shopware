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

namespace Shopware\Components\License\Service;

use Shopware\Components\License\Service\Exceptions\LicenseHostException;
use Shopware\Components\License\Service\Exceptions\LicenseInvalidException;
use Shopware\Components\License\Service\Exceptions\LicenseProductKeyException;
use Shopware\Components\License\Struct\LicenseInformation;
use Shopware\Components\License\Struct\LicenseUnpackRequest;
use Shopware\Components\License\Struct\ShopwareEdition;

class LocalLicenseUnpackService implements LicenseUnpackServiceInterface
{
    /**
     * @throws LicenseHostException
     * @throws LicenseProductKeyException
     *
     * @return LicenseInformation
     */
    public function evaluateLicense(LicenseUnpackRequest $request)
    {
        $license = $request->licenseKey;
        $host = $request->host;

        $info = $this->readLicenseInfo($license);

        if (!$this->isValidProductKey($info['product'])) {
            throw new LicenseProductKeyException('License key does not match a commercial Shopware edition');
        }

        if ($info['host'] != $host) {
            throw new LicenseHostException(new LicenseInformation($info), sprintf('License key is not valid for domain %s', $request->host));
        }

        $info['edition'] = $info['product'];
        unset($info['moduleLicense']);
        unset($info['coreLicense']);

        return new LicenseInformation($info);
    }

    /**
     * @param string $license
     *
     * @throws LicenseInvalidException
     *
     * @return array
     */
    public function readLicenseInfo($license)
    {
        $license = str_replace('-------- LICENSE BEGIN ---------', '', $license);
        $license = str_replace('--------- LICENSE END ----------', '', $license);
        $license = preg_replace('#--.+?--#', '', (string) $license);
        $license = preg_replace('#[^A-Za-z0-9+/=]#', '', $license);

        $info = base64_decode($license);
        if ($info === false) {
            throw new LicenseInvalidException('License key seems to be incorrect');
        }
        $info = @gzinflate($info);
        if ($info === false) {
            throw new LicenseInvalidException('License key seems to be incorrect');
        }

        if (strlen($info) > (512 + 60) || strlen($info) < 100) {
            throw new LicenseInvalidException('License key seems to be incorrect');
        }

        $hash = substr($info, 0, 20);
        $coreLicense = substr($info, 20, 20);
        $moduleLicense = substr($info, 40, 20);
        $info = substr($info, 60);

        if ($hash !== sha1($coreLicense . $info . $moduleLicense, true)) {
            throw new LicenseInvalidException('License key seems to be incorrect');
        }

        $info = unserialize($info, ['allowed_classes' => false]);
        if ($info === false) {
            throw new LicenseInvalidException('License key seems to be incorrect');
        }

        $info['license'] = $license;
        $info['moduleLicense'] = $moduleLicense;
        $info['coreLicense'] = $coreLicense;

        return $info;
    }

    /**
     * Validates the product key provided in the license
     *
     * @param string $productKey
     *
     * @return bool
     */
    private function isValidProductKey($productKey)
    {
        if (empty($productKey)) {
            return false;
        }
        $validKeys = ShopwareEdition::getValidEditions();

        return in_array($productKey, $validKeys, true);
    }
}
