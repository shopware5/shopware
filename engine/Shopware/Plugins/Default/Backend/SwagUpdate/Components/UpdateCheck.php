<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace ShopwarePlugins\SwagUpdate\Components;

use ShopwarePlugins\SwagUpdate\Components\Exception\ApiLimitExceededException;
use ShopwarePlugins\SwagUpdate\Components\Exception\ReleasePackageNotFoundException;
use ShopwarePlugins\SwagUpdate\Components\Exception\UpdatePackageNotFoundException;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;
use Symfony\Component\HttpFoundation\Response;
use Zend_Http_Client;
use Zend_Json;
use Zend_Json_Exception;

class UpdateCheck
{
    public const SECURITY_IDENTIFIER = '[SECURITY_RELEASE]';

    private Zend_Http_Client $client;

    private bool $preRelease;

    private bool $draft;

    public function __construct(Zend_Http_Client $client, bool $draft, bool $preRelease)
    {
        $this->client = $client;
        $this->draft = $draft;
        $this->preRelease = $preRelease;
    }

    /**
     * @deprecated Additional Parameter `$params` will be removed with Shopware 5.8, as it is not used anymore
     *
     * @param string               $shopwareVersion
     * @param array<string, mixed> $params
     *
     * @return Version|null
     */
    public function checkUpdate($shopwareVersion, array $params = [])
    {
        if ($shopwareVersion === '___VERSION___') {
            return null;
        }

        $response = $this->client->request();

        if ($response->getStatus() === Response::HTTP_FORBIDDEN) {
            throw new ApiLimitExceededException();
        }

        if ($response->getStatus() === Response::HTTP_NOT_FOUND) {
            return null;
        }

        $body = $response->getBody();

        if (!\is_string($body)) {
            return null;
        }

        if ($body === '') {
            return null;
        }

        try {
            $json = Zend_Json::decode($body);
        } catch (Zend_Json_Exception $e) {
            return null;
        }

        try {
            return $this->createVersionFromGithubResponse($shopwareVersion, $json);
        } catch (ReleasePackageNotFoundException|UpdatePackageNotFoundException $exception) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $releaseInformation
     *
     * @throws UpdatePackageNotFoundException
     * @throws ReleasePackageNotFoundException
     */
    private function createVersionFromGithubResponse(string $shopwareVersion, array $releaseInformation): Version
    {
        $latestRelease = $this->getRelease($shopwareVersion, $releaseInformation);

        $installPackage = $this->getUpdatePackage($latestRelease['assets']);

        $parts = explode('_', $installPackage['name']);
        $sha1 = array_pop($parts);

        return new Version([
            'version' => $latestRelease['tag_name'],
            'release_date' => $latestRelease['created_at'],
            'size' => $installPackage['size'],
            'uri' => $installPackage['browser_download_url'],
            'changelog' => $latestRelease['html_url'],
            'isNewer' => true,
            'security_update' => str_contains(self::SECURITY_IDENTIFIER, $latestRelease['body']),
            'sha1' => str_replace('.zip', '', $sha1),
        ]);
    }

    /**
     * @param array<string, mixed> $releaseInformation
     *
     * @throws ReleasePackageNotFoundException
     *
     * @return array<string, mixed>
     */
    private function getRelease(string $shopwareVersion, array $releaseInformation): array
    {
        foreach ($releaseInformation as $release) {
            if (version_compare($shopwareVersion, $release['tag_name'], '>=')) {
                continue;
            }

            if ((bool) $release['draft'] !== $this->draft) {
                continue;
            }

            if ((bool) $release['prerelease'] !== $this->preRelease) {
                continue;
            }

            return $release;
        }

        throw new ReleasePackageNotFoundException();
    }

    /**
     * @param array<string, mixed> $assets
     *
     * @throws UpdatePackageNotFoundException
     *
     * @return array<string, mixed>
     */
    private function getUpdatePackage(array $assets): array
    {
        foreach ($assets as $asset) {
            if (str_contains($asset['name'], 'update_')) {
                return $asset;
            }
        }

        throw new UpdatePackageNotFoundException();
    }
}
