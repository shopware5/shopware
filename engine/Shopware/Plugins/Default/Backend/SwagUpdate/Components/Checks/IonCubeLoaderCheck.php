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

use Enlight_Components_Snippet_Namespace as SnippetNamespace;
use ShopwarePlugins\SwagUpdate\Components\CheckInterface;
use ShopwarePlugins\SwagUpdate\Components\Validation;

class IonCubeLoaderCheck implements CheckInterface
{
    const CHECK_TYPE = 'ioncubeloaderversion';

    /**
     * @var SnippetNamespace
     */
    private $namespace;

    public function __construct(SnippetNamespace $namespace)
    {
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
     * @param array $requirement
     *
     * @return array
     */
    public function check($requirement)
    {
        $requiredVersion = $requirement['value'];

        if (!extension_loaded('ionCube Loader')) {
            return null;
        }

        if (!function_exists('ioncube_loader_version')) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => $requirement['level'],
                'message' => sprintf(
                    $this->namespace->get('controller/check_ioncubeloaderversion_unknown'),
                    $requiredVersion
                ),
            ];
        }

        $installedVersion = ioncube_loader_version();

        $isValid = version_compare(strtolower($installedVersion), $requiredVersion, '>');
        if ($isValid) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_VALID,
                'message' => sprintf(
                    $this->namespace->get('controller/check_ioncubeloaderversion_success'),
                    $requiredVersion,
                    $installedVersion
                ),
            ];
        }

        return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => $requirement['level'],
                'message' => sprintf(
                    $this->namespace->get('check_ioncubeloaderversion_failure'),
                    $requiredVersion,
                    $installedVersion
                ),
            ];
    }
}
