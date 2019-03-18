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

class RegexCheck implements CheckInterface
{
    const CHECK_TYPE = 'regex';

    /**
     * @var SnippetNamespace
     */
    private $namespace;

    /**
     * @var string
     */
    private $userLang;

    /**
     * @param string $userLang
     */
    public function __construct(SnippetNamespace $namespace, $userLang)
    {
        $this->namespace = $namespace;
        $this->userLang = $userLang;
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
        $results = [];
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
            return null;
        }
        $files = array_keys($results);

        return [
                'type' => self::CHECK_TYPE,
                'errorLevel' => $requirement['level'],
                'description' => $requirement['description'],
                'message' => sprintf($message, implode('<br>', $files)),
            ];
    }

    /**
     * Search for a given string
     *
     * @param string $path
     * @param string $regex
     * @param string $regexFile
     *
     * @return array
     */
    private function scanDirectoryForRegex($path, $regex, $regexFile = null)
    {
        // Iterate the given path recursively
        $directoryIterator = new \RecursiveDirectoryIterator($path);
        // get a flat iterator
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        $results = [];

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
     * @param string $file
     * @param string $regex
     *
     * @return bool
     */
    private function searchFileForRegex($file, $regex)
    {
        $content = file_get_contents($file);
        if (preg_match_all($regex, $content, $matches)) {
            return $matches;
        }

        return false;
    }

    /**
     * @param array $messages
     *
     * @return string
     */
    private function extractLocalizedMessage($messages)
    {
        $languages = [
            $this->userLang,
            'en',
            'de',
        ];

        while ($language = array_shift($languages)) {
            if (isset($messages[$language])) {
                return $messages[$language];
            }
        }

        return '';
    }
}
