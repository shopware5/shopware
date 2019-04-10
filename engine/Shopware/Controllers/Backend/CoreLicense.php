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

use Shopware\Components\License\Service\Exceptions\LicenseHostException;
use Shopware\Components\License\Struct\LicenseInformation;
use Shopware\Components\License\Struct\LicenseUnpackRequest;
use Shopware\Components\LicenseInstaller;

class Shopware_Controllers_Backend_CoreLicense extends Shopware_Controllers_Backend_ExtJs
{
    /** @var array Contains the possible Exception names thrown by LocalLicenseUnpackService */
    private $licenseException = [
        'LicenseHostException',
        'LicenseInvalidException',
        'LicenseProductKeyException',
    ];

    /**
     * Expects a request parameter 'licenseString' containing a shopware core license key string.
     * Will validate and, if successful, enter the license information into the database.
     */
    public function checkLicenseAction()
    {
        $licenseString = trim($this->Request()->getPost('licenseString'));

        if (empty($licenseString)) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Empty license information cannot be validated.',
            ]);

            return;
        }

        try {
            /** @var LicenseInformation $licenseData */
            $licenseData = $this->unpackLicense($licenseString);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
                'errorType' => $this->resolveLicenseException($e),
            ]);

            return;
        }

        try {
            $licenseInstaller = new LicenseInstaller($this->container->get('dbal_connection'));
            $licenseInstaller->installLicense($licenseData);
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        $licenseData = $this->reFormatLicenseString($licenseData);

        $this->View()->assign([
            'success' => true,
            'licenseData' => $licenseData,
        ]);
    }

    /**
     * Outputs the current license information if present in db
     */
    public function loadSavedLicenseAction()
    {
        try {
            $licenseString = $this->getInstalledLicense();
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
            ]);

            return;
        }

        if (empty($licenseString)) {
            $this->View()->assign([
                'success' => false,
            ]);

            return;
        }

        try {
            /** @var LicenseInformation $licenseData */
            $licenseData = $this->unpackLicense($licenseString);
        } catch (LicenseHostException $e) {
            $license = $e->getLicenseInformation();

            $this->View()->assign([
                'success' => false,
                'errorType' => 'LicenseHostException',
                'licenseData' => $this->reFormatLicenseString($license),
            ]);

            return;
        } catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
                'errorType' => $this->resolveLicenseException($e),
            ]);

            return;
        }

        $licenseData = $this->reFormatLicenseString($licenseData);

        $this->View()->assign([
            'success' => true,
            'licenseData' => $licenseData,
        ]);
    }

    /**
     * Removes the core license and outputs information accordingly
     */
    public function uninstallLicenseAction()
    {
        try {
            $this->deleteLicense();
        } catch (RuntimeException $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage(),
                'errorType' => 'COMMON',
            ]);

            return;
        }

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Returns the license string from a currently installed
     * core license, if present.
     *
     * @throws Exception
     *
     * @return string
     */
    private function getInstalledLicense()
    {
        $sql = 'SELECT license FROM s_core_licenses WHERE active=1 AND module = "SwagCommercial"';

        return $this->container->get('dbal_connection')->query($sql)->fetchColumn();
    }

    /**
     * Checks a license key string and on error deletes
     * currently installed core licenses if $deleteOnError = true.
     * Returns LicenseInformation on success.
     *
     * @param string $licenseString
     *
     * @throws \RuntimeException
     *
     * @return LicenseInformation
     */
    private function unpackLicense($licenseString)
    {
        $repository = $this->container->get('models')->getRepository('Shopware\Models\Shop\Shop');
        $host = $repository->getActiveDefault()->getHost();
        $request = new LicenseUnpackRequest($licenseString, $host);

        /** @var LicenseInformation $licenseData */
        $licenseData = $this->container->get('shopware_core.local_license_unpack_service')->evaluateLicense($request);

        return $licenseData;
    }

    /**
     * Deletes the current Core License from db
     *
     * @throws RuntimeException
     */
    private function deleteLicense()
    {
        try {
            $sql = "DELETE FROM s_core_licenses WHERE module = 'SwagCommercial'";
            $this->container->get('dbal_connection')->query($sql);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Could not remove license from database', 0, $e);
        }
    }

    /**
     * @param Exception $e
     *
     * @return string
     */
    private function resolveLicenseException($e)
    {
        // Get class name without namespace
        $exceptionType = (new \ReflectionClass($e))->getShortName();

        if (in_array($exceptionType, $this->licenseException)) {
            $errorType = $exceptionType;

            return $errorType;
        }
        $errorType = 'COMMON';

        return $errorType;
    }

    /**
     * Creates a readable string from a minified license key.
     *
     * @return LicenseInformation
     */
    private function reFormatLicenseString(LicenseInformation $licenseInformation)
    {
        $license = $licenseInformation->license;

        $license = preg_replace('#--.+?--#', '', (string) $license);
        $license = preg_replace('#[^A-Za-z0-9+/=]#', '', $license);
        $license = chunk_split($license, 32);
        $license = "-------- LICENSE BEGIN ---------\r\n" . $license . "--------- LICENSE END ----------\r\n";

        $licenseInformation->license = $license;

        return $licenseInformation;
    }
}
