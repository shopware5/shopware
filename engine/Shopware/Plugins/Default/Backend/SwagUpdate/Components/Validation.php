<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
     * Type for regular expression validations
     */
    const CHECK_TYPE_REGEX = 'regex';

    /**
     * Type for writable directory validations
     */
    const CHECK_TYPE_WRITABLE = 'writable';

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    /**
     * @var string
     */
    private $userLang;

    /**
     * @param \Enlight_Components_Snippet_Namespace $namespace
     * @param string                                $userLang
     */
    public function __construct(\Enlight_Components_Snippet_Namespace $namespace, $userLang)
    {
        $this->namespace = $namespace;
        $this->userLang = $userLang;
    }

    /**
     *
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
            switch ($requirement['type']) {
                case self::CHECK_TYPE_REGEX:
                    $result = $this->executeRegexCheck($requirement);
                    if (!empty($result)) {
                        $results[] = $result;
                    }

                    break;

                case self::CHECK_TYPE_WRITABLE:
                    $results[] = $this->executeWritableCheck($requirement);
                    break;

                default:
                    throw new \Exception(
                        sprintf('Unknown requirement check type %s', $requirement['type'])
                    );
            }
        }

        return $results;
    }

    /**
     * @param $requirement
     * @return array
     */
    private function executeWritableCheck($requirement)
    {
        $directories = array();
        $fs = new FileSystem();
        $checkedDirectories = array();

        $successMessage = $this->namespace->get('controller/check_writable_success', "The following directories are writeable <br/>%s");
        $failMessage = $this->namespace->get('controller/check_writable_failure', "The following directories are not writable: <br> %s");

        foreach ($requirement['value'] as $path) {
            $fullPath = rtrim(Shopware()->DocPath($path), '/');
            $checkedDirectories[] = $fullPath;

            $fixPermissions = true;
            $directories = array_merge(
                $directories,
                $fs->checkDirectoryPermissions($fullPath, $fixPermissions)
            );
        }

        if (empty($directories)) {
            return array(
                'type' => self::CHECK_TYPE_WRITABLE,
                'errorLevel' => self::REQUIREMENT_VALID,
                    'message'    => sprintf(
                        $successMessage,
                        implode('<br>', $checkedDirectories)
                    )
            );
        } else {
            return array(
                'type' => self::CHECK_TYPE_WRITABLE,
                'errorLevel' => $requirement['level'],
                'message'    => sprintf(
                    $failMessage,
                    implode('<br>', $directories)
                )
            );
        }
    }

    /**
     * @param $messages
     * @return string
     */
    private function extractLocalizedMessage($messages)
    {
        $languages = array(
            $this->userLang,
            'en',
            'de',
        );

        while ($language = array_shift($languages)) {
            if (isset($messages[$language])) {
                return $messages[$language];
            }
        }

        return '';
    }

    /**
     * @param $requirement
     * @return array
     */
    private function executeRegexCheck($requirement)
    {
        $results = array();
        foreach ($requirement['value']['directories'] as $dir) {
            $result = $this->scanDirectoryForRegex(
                Shopware()->DocPath($dir),
                $requirement['value']['expression'],
                $requirement['value']['fileRegex']
            );

            $results = array_merge($results, $result);
        }

        $message = $this->extractLocalizedMessage($requirement['value']['message']);

        if (empty($results)) {
            return array();
        } else {
            $files = array_keys($results);

            return array(
                'type' => self::CHECK_TYPE_REGEX,
                'errorLevel' => $requirement['level'],
                'description' => $requirement['description'],
                'message' => sprintf($message, implode('<br>', $files))
            );
        }
    }

    /**
     * Search for a given string
     *
     * @param  string $path
     * @param  string $regex
     * @param  string $regexFile
     * @return array
     */
    public function scanDirectoryForRegex($path, $regex, $regexFile = null)
    {
        // Iterate the given path recursively
        $directoryIterator = new \RecursiveDirectoryIterator($path);
        // get a flat iterator
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        $results = array();

        // Allow files to be filtered out by name
        if (isset($regexFile) && !empty($regexFile)) {
            $iterator = new \RegexIterator($iterator, $regexFile);
        }

        // Iterate the result, get file content, check for $regex matches
        foreach ($iterator as $splFileInfo) {
            if ($splFileInfo->isDir()) {
                continue;
            }

            $realPath = $splFileInfo->getRealPath();
            if (strpos($realPath, 'SwagUpdateCheck') !== false) {
                continue;
            }

            $result = $this->searchFileForRegex($realPath, $regex);
            if ($result) {
                $results[$realPath] = $result;
            }
        }

        return $results;
    }

    /**
     * Searches inside a file for a given regex. Will return Match-Objects or false if no match was found
     *
     * @param $file
     * @param $regex
     * @return bool
     */
    public function searchFileForRegex($file, $regex)
    {
        $content = file_get_contents($file);
        if (preg_match_all($regex, $content, $matches)) {
            return $matches;
        }

        return false;
    }
}
