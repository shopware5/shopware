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

namespace ShopwarePlugins\SwagUpdate\Components;

/**
 * Used for plugin and system requirement validation.
 *
 * @category  Shopware
 * @package   ShopwarePlugins\SwagUpdate\Components;
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Validation
{
    /**
     * Flag if a validation is valid.
     */
    const REQUIREMENT_VALID = 0;

    /**
     * Flag if a validation should be displayed as warning
     */
    const REQUIREMENT_WARNING = 10;

    /**
     * Flag if a validation should be displayed as critical and
     * abort the update process.
     */
    const REQUIREMENT_CRITICAL = 20;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    /**
     * @var CheckInterface[]
     */
    private $checks;

    /**
     * @param \Enlight_Components_Snippet_Namespace $namespace
     */
    public function __construct(\Enlight_Components_Snippet_Namespace $namespace, array $checks)
    {
        $this->namespace = $namespace;
        $this->checks = $checks;
    }

    /**
     * @param array $requirements {
     * @type            string type => Type of the requirement check.
     * @type            array  directories => Array of directories which should be iterated
     * @type            string errorLevel => Flag how critical the error is (1 => Warning, 2 => Exception)
     * @type            string errorMessage => Error message which can be set for the validation, 1x %s will be replaced with all found files.
     * @type [optional] string value => Only used for regular expressions, contains the regular expression.
     * @type [optional] string fileRegex => Regular expression for file types.
     *                            }
     *
     * @return array {
     * @type string type       => Type of the requirement check.
     * @type int    errorLevel => Flag how critical the error is (1 => Warning, 2 => Exception)
     * @type string message    => Passed error message for failed checks.
     *               }
     *
     * @throws \Exception
     */
    public function checkRequirements($requirements)
    {
        $results = array();
        foreach ($requirements as $requirement) {
            $result = $this->handleRequirement($requirement);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * @param $requirement
     * @return array
     */
    private function handleRequirement($requirement)
    {
        foreach ($this->checks as $check) {
            if ($check->canHandle($requirement)) {
                return $check->check($requirement);
            }
        }

        return null; // unhandled requirement ignored intentionally
    }
}
