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

use Shopware\Recovery\Install\Service\Exceptions\LicenseHostException;
use Shopware\Recovery\Install\Service\Exceptions\LicenseInvalidException;
use Shopware\Recovery\Install\Service\Exceptions\LicenseProductKeyException;
use Shopware\Recovery\Install\Struct\LicenseInformation;
use Shopware\Recovery\Install\Struct\LicenseUnpackRequest;
use Shopware\Recovery\Install\Struct\ShopwareEdition;

class LocalLicenseUnpackService implements LicenseUnpackService
{
    /**
     * @throws LicenseHostException
     * @throws LicenseProductKeyException
     * @throws LicenseInvalidException
     *
     * @return LicenseInformation
     */
    public function evaluateLicense(LicenseUnpackRequest $request, TranslationService $translation)
    {
        $license = $request->licenseKey;
        $host = $request->host;

        $license = str_replace('-------- LICENSE BEGIN ---------', '', $license);
        $license = str_replace('--------- LICENSE END ----------', '', $license);
        $license = preg_replace('#--.+?--#', '', (string) $license);
        $license = preg_replace('#[^A-Za-z0-9+/=]#', '', $license);

        $info = base64_decode($license);
        if ($info === false) {
            throw new LicenseInvalidException($translation->translate('license_incorrect'));
        }
        $info = @gzinflate($info);
        if ($info === false) {
            throw new LicenseInvalidException($translation->translate('license_incorrect'));
        }

        if (strlen($info) > (512 + 60) || strlen($info) < 100) {
            throw new LicenseInvalidException($translation->translate('license_incorrect'));
        }

        $hash = substr($info, 0, 20);
        $coreLicense = substr($info, 20, 20);
        $moduleLicense = substr($info, 40, 20);
        $info = substr($info, 60);

        if ($hash !== sha1($coreLicense . $info . $moduleLicense, true)) {
            throw new LicenseInvalidException($translation->translate('license_incorrect'));
        }

        $info = unserialize($info);
        if ($info === false) {
            throw new LicenseInvalidException($translation->translate('license_incorrect'));
        }

        $info['license'] = $license;

        if (!$this->isValidProductKey($info['product'])) {
            throw new LicenseProductKeyException($translation->translate('license_does_not_match'));
        }

        if ($info['host'] !== $host) {
            throw new LicenseHostException(new LicenseInformation($info), $translation->translate('license_domain_error') . $request->host);
        }

        $info += ['edition' => $info['product']];

        return new LicenseInformation($info);
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
