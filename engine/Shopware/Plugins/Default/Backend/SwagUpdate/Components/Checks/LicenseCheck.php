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

class LicenseCheck implements CheckInterface
{
    const CHECK_TYPE = 'licensecheck';

    /**
     * @var SnippetNamespace
     */
    private $namespace;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param string $endpoint
     * @param string $shopwareVersion
     */
    public function __construct(Connection $connection, $endpoint, $shopwareVersion, SnippetNamespace $namespace)
    {
        $this->connection = $connection;
        $this->endpoint = $endpoint;
        $this->shopwareVersion = $shopwareVersion;
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
        $licenseKeys = $requirement['value']['licenseKeys'];

        if (empty($licenseKeys)) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_WARNING,
                'message' => 'License check requested but no license key provided',
            ];
        }
        $licenseData = $this->getLicenseData($licenseKeys);

        if (empty($licenseData)) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_VALID,
                'message' => $this->namespace->get('controller/check_license_nolicense'),
            ];
        }

        $url = $this->endpoint . '/licenseupgrades/permission';
        $client = new \Zend_Http_Client(
            $url, [
                'timeout' => 15,
            ]
        );

        foreach ($licenseData as $licenseDatum) {
            $client->setParameterPost('domain', $licenseDatum['host']);
            $client->setParameterPost('licenseKey', $licenseDatum['license']);
            $client->setParameterPost('version', $this->shopwareVersion);

            try {
                $response = $client->request(\Zend_Http_Client::POST);
            } catch (\Zend_Http_Client_Exception $e) {
                // Do not show exception to user if request times out
                return null;
            }

            try {
                $body = $response->getBody();
                if ($body != '') {
                    $json = \Zend_Json::decode($body, true);
                } else {
                    $json = null;
                }
            } catch (\Exception $e) {
                // Do not show exception to user if SBP returns an error
                return null;
            }

            if ($json === true) {
                return [
                    'type' => self::CHECK_TYPE,
                    'errorLevel' => Validation::REQUIREMENT_VALID,
                    'message' => $this->namespace->get('controller/check_license_success'),
                ];
            }
        }

        return [
            'type' => self::CHECK_TYPE,
            'errorLevel' => $requirement['level'],
            'message' => $this->namespace->get('controller/check_license_failure'),
        ];
    }

    /**
     * Returns existing license data for the provided keys
     *
     * @param array $licenseKeys
     *
     * @return array
     */
    private function getLicenseData($licenseKeys)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select(['host', 'license'])
            ->from('s_core_licenses', 'license')
            ->where('license.active = 1')
            ->andWhere('license.module IN (:modules)')
            ->setParameter(':modules', $licenseKeys, Connection::PARAM_STR_ARRAY);

        return $queryBuilder->execute()->fetchAll();
    }
}
