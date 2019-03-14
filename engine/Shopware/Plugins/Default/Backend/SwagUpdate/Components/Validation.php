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

    public function __construct(\Enlight_Components_Snippet_Namespace $namespace, array $checks)
    {
        $this->namespace = $namespace;
        $this->checks = $checks;
    }

    /**
     * @param array $requirements {
     *
     * @var string     type => Type of the requirement check
     * @var array      directories => Array of directories which should be iterated
     * @var string     errorLevel => Flag how critical the error is (1 => Warning, 2 => Exception)
     * @var string     errorMessage => Error message which can be set for the validation, 1x %s will be replaced with all found files
     * @var [optional] string value => Only used for regular expressions, contains the regular expression
     * @var [optional] string fileRegex => Regular expression for file types.
     *                 }
     *
     * @throws \Exception
     *
     * @return array {
     *
     * @var string type       => Type of the requirement check
     * @var int    errorLevel => Flag how critical the error is (1 => Warning, 2 => Exception)
     * @var string message    => Passed error message for failed checks.
     *             }
     */
    public function checkRequirements($requirements)
    {
        $results = [];
        foreach ($requirements as $requirement) {
            $result = $this->handleRequirement($requirement);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * @param array $requirement
     *
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
