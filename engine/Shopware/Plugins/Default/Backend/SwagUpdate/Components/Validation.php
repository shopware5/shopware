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

use Exception;

/**
 * Used for plugin and system requirement validation.
 */
class Validation
{
    /**
     * Flag if a validation is valid.
     */
    public const REQUIREMENT_VALID = 0;

    /**
     * Flag if a validation should be displayed as warning
     */
    public const REQUIREMENT_WARNING = 10;

    /**
     * Flag if a validation should be displayed as critical and
     * abort the update process.
     */
    public const REQUIREMENT_CRITICAL = 20;

    /**
     * @var array<CheckInterface>
     */
    private $checks;

    /**
     * @param array<CheckInterface> $checks
     */
    public function __construct(array $checks)
    {
        $this->checks = $checks;
    }

    /**
     * requirements {
     *      string type => Type of the requirement check
     *      array  directories [optional] => Array of directories which should be iterated
     *      string errorLevel => Flag how critical the error is (1 => Warning, 2 => Exception)
     *      string errorMessage => Error message which can be set for the validation, 1x %s will be replaced with all found files
     *      string value => Only used for regular expressions, contains the regular expression
     *      string fileRegex [optional] => Regular expression for file types.
     * }
     *
     * @param array<array{type: string, value: string|array|null, level: Validation::REQUIREMENT_VALID|Validation::REQUIREMENT_WARNING|Validation::REQUIREMENT_CRITICAL}> $requirements
     *
     * @throws Exception
     *
     * return array {
     *      string type       => Type of the requirement check
     *      int    errorLevel => Flag how critical the error is (1 => Warning, 2 => Exception)
     *      string message    => Passed error message for failed checks.
     * }
     *
     * @return array<array{type: string, errorLevel: Validation::REQUIREMENT_VALID|Validation::REQUIREMENT_WARNING|Validation::REQUIREMENT_CRITICAL, message: string}|null>
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
     * @param array{type: string, value: string|array|null, level: Validation::REQUIREMENT_VALID|Validation::REQUIREMENT_WARNING|Validation::REQUIREMENT_CRITICAL} $requirement
     *
     * @return array{type: string, errorLevel: Validation::REQUIREMENT_VALID|Validation::REQUIREMENT_WARNING|Validation::REQUIREMENT_CRITICAL, message: string}|null
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
