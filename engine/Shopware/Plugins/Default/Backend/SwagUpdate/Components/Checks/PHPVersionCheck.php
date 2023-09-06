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

namespace ShopwarePlugins\SwagUpdate\Components\Checks;

use Enlight_Components_Snippet_Namespace as SnippetNamespace;
use InvalidArgumentException;
use ShopwarePlugins\SwagUpdate\Components\CheckInterface;
use ShopwarePlugins\SwagUpdate\Components\Validation;

class PHPVersionCheck implements CheckInterface
{
    public const CHECK_TYPE = 'phpversion';

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
        return $requirement['type'] === self::CHECK_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function check($requirement)
    {
        if (!\is_string($requirement['value'])) {
            throw new InvalidArgumentException(__CLASS__ . ' needs a string as value for the requirement check');
        }

        $minPHPVersion = $requirement['value'];

        $validVersion = version_compare(PHP_VERSION, $minPHPVersion) >= 0;

        $successMessage = $this->namespace->get('controller/check_phpversion_success', 'Min PHP Version: %s, your version %s');
        $failMessage = $this->namespace->get('controller/check_phpversion_failure', 'Min PHP Version: %s, your version %s');

        if ($validVersion) {
            return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => Validation::REQUIREMENT_VALID,
                'message' => sprintf(
                    $successMessage,
                    $minPHPVersion,
                    PHP_VERSION
                ),
            ];
        }

        return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => $requirement['level'],
                'message' => sprintf(
                    $failMessage,
                    $minPHPVersion,
                    PHP_VERSION
                ),
            ];
    }
}
