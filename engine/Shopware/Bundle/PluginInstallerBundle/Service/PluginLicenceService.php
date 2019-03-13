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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateLicencesRequest;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationStruct;
use Shopware\Components\License\Service\LocalLicenseUnpackService;

class PluginLicenceService
{
    const TYPE_UNLICENSED = 99;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var StoreClient
     */
    private $storeClient;

    /**
     * @var LocalLicenseUnpackService
     */
    private $unpackService;

    public function __construct(
        Connection $connection,
        StoreClient $storeClient,
        LocalLicenseUnpackService $unpackService
    ) {
        $this->connection = $connection;
        $this->storeClient = $storeClient;
        $this->unpackService = $unpackService;
    }

    /**
     * @return \Shopware\Components\HttpClient\Response
     */
    public function updateLicences(UpdateLicencesRequest $request)
    {
        $response = $this->storeClient->doAuthPostRequestRaw(
            $request->getToken(),
            '/licenseupgrades/simple',
            [
                'domain' => $request->getDomain(),
                'shopwareVersion' => $request->getShopwareVersion(),
                'locale' => $request->getLocale(),
            ]
        );

        return $response;
    }

    /**
     * function to get expired and soon expiring plugins
     *
     * @return PluginInformationStruct[]
     */
    public function getExpiringLicenses()
    {
        $expiringPluginLicenses = [];
        $licenses = $this->getLicences();

        if (empty($licenses)) {
            return $expiringPluginLicenses;
        }
        $expirations = $this->getExpirations($licenses);
        foreach ($expirations as $expiration => $license) {
            $expirationDate = new \DateTime($expiration);
            if ($this->isExpired($expirationDate) || $this->isSoonExpiring($expirationDate)) {
                $expiringPluginLicenses[] = $this->createPluginInformationStruct($license);
            }
        }

        return $expiringPluginLicenses;
    }

    /**
     * function to get only expired plugins
     *
     * @return PluginInformationStruct[]
     */
    public function getExpiredLicenses()
    {
        $expiredPluginLicenses = [];
        $licenses = $this->getLicences();

        if (empty($licenses)) {
            return $expiredPluginLicenses;
        }
        $expirations = $this->getExpirations($licenses);
        foreach ($expirations as $expiration => $license) {
            $expirationDate = new \DateTime($expiration);
            if ($this->isExpired($expirationDate)) {
                $expiredPluginLicenses[] = $this->createPluginInformationStruct($license);
            }
        }

        return $expiredPluginLicenses;
    }

    /**
     * @param PluginInformationStruct[] $pluginInformation
     * @param string                    $domain
     */
    public function updateLocalLicenseInformation(array $pluginInformation, $domain)
    {
        $this->cleanupLocalLicenseInformation();
        foreach ($pluginInformation as $plugin) {
            if ($plugin->getLicenseExpiration() == null && !$plugin->isUnknownLicense()) {
                continue;
            }
            $license = $this->getLocalLicenseByPluginName($plugin->getTechnicalName());
            if (empty($license)) {
                $this->createLocalLicenseInformation($plugin, $domain);
            } elseif (empty($license['license'])) {
                $this->updateLocalLicenseExpirationInformation($license, $plugin);
            }
        }
    }

    /**
     * @return array
     */
    private function getExpirations(array $licenses)
    {
        $expirations = [];
        foreach ($licenses as $license) {
            if (!empty($license['license'])) {
                $info = $this->unpackService->readLicenseInfo(($license['license']));
                if (!$info) {
                    continue;
                }
                $license = array_merge($license, $info);
            }

            if (empty($license['expiration'])) {
                continue;
            }

            $expirations[$license['expiration']] = $license;
        }

        return $expirations;
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function cleanupLocalLicenseInformation()
    {
        $this->connection->delete('s_core_licenses', ['license' => '']);
    }

    /**
     * @param string $pluginName
     *
     * @return array
     */
    private function getLocalLicenseByPluginName($pluginName)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('license.*')
            ->from('s_core_licenses', 'license')
            ->where('license.module = :plugin')
            ->setParameter('plugin', $pluginName);

        return $queryBuilder->execute()->fetch();
    }

    /**
     * @param string $domain
     */
    private function createLocalLicenseInformation(PluginInformationStruct $plugin, $domain)
    {
        $today = new \DateTime();
        $data = [
            'module' => $plugin->getTechnicalName(),
            'host' => $domain,
            'label' => $plugin->getLabel(),
            'license' => '',
            'version' => $plugin->getVersion(),
            'active' => 1,
            'source' => $plugin->getSource(),
            'added' => $today->format('Y-m-d'),
        ];

        if ($plugin->isUnknownLicense()) {
            $type = self::TYPE_UNLICENSED;
        } else {
            $creationDate = new \DateTime($plugin->getLicenseCreation());
            $expirationDate = new \DateTime($plugin->getLicenseExpiration());
            $type = $plugin->getType();
            $data['creation'] = $creationDate->format('Y-m-d');
            $data['expiration'] = $expirationDate->format('Y-m-d');
        }
        $data['type'] = $type;

        $this->connection->insert('s_core_licenses', $data);
    }

    private function updateLocalLicenseExpirationInformation(array $license, PluginInformationStruct $plugin)
    {
        $expirationDate = $plugin->getLicenseExpiration();
        if ($expirationDate !== $license['expiration']) {
            $this->connection->update('s_core_licenses', ['expiration' => $expirationDate], ['id' => $license['id']]);
        }
    }

    /**
     * function to get all plugin licenses of active plugins
     *
     * @return array
     */
    private function getLicences()
    {
        /** @var Connection $connection */
        $connection = $this->connection;
        $builder = $connection->createQueryBuilder();

        $builder->select(['license.module, license.label, license.expiration, license.license'])
            ->from('s_core_licenses', 'license');

        $builderExecute = $builder->execute();

        return $builderExecute->fetchAll();
    }

    /**
     * @return bool
     */
    private function isExpired(\DateTimeInterface $expirationDate)
    {
        $diff = $expirationDate->diff(new \DateTime('now'));

        return $diff->invert == 0;
    }

    /**
     * @param int $daysTillExpiration
     *
     * @return bool
     */
    private function isSoonExpiring(\DateTimeInterface $expirationDate, $daysTillExpiration = 14)
    {
        $diff = $expirationDate->diff(new \DateTime('now'));

        return $diff->invert == 1 && $diff->days <= $daysTillExpiration;
    }

    /**
     * @return PluginInformationStruct
     */
    private function createPluginInformationStruct(array $data)
    {
        $information = [
            'label' => $data['label'],
            'name' => $data['module'],
            'licenseExpiration' => (new \DateTime($data['expiration']))->format('Y-m-d'),
        ];

        return new PluginInformationStruct($information);
    }
}
