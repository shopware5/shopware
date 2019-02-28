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

namespace Shopware\Components\Snippet;

use Shopware\Components\Snippet\Writer\QueryWriter;
use Symfony\Component\Finder\Finder;

class QueryHandler
{
    /**
     * @var string The snippet dir
     */
    protected $snippetsDir;

    /**
     * @param string $snippetsDir
     */
    public function __construct($snippetsDir)
    {
        $this->snippetsDir = $snippetsDir;
    }

    /**
     * Parses current .ini snippet files and generates the matching MySQL queries
     *
     * @param string $snippetsDir
     * @param bool   $update      if false, UPDATE IGNORE statements are generated. Default true, generates UPDATE .. ON DUPLICATE KEY statements
     *
     * @return array the array containing the generated queries
     */
    public function loadToQuery($snippetsDir = null, $update = true)
    {
        $snippetsDir = $snippetsDir ?: $this->snippetsDir;

        if (!file_exists($snippetsDir)) {
            return [];
        }

        $locales = [];
        $finder = new Finder();

        $inputAdapter = new \Enlight_Config_Adapter_File([
            'configDir' => $snippetsDir,
        ]);

        $queryWriter = new QueryWriter();

        $finder->files()->in($snippetsDir);
        foreach ($finder as $file) {
            $filePath = $file->getRelativePathname();
            if (strpos($filePath, '.ini') == strlen($filePath) - 4) {
                $namespace = substr($filePath, 0, -4);
            } else {
                continue;
            }

            $namespaceData = new \Enlight_Components_Snippet_Namespace([
                'adapter' => $inputAdapter,
                'name' => $namespace,
            ]);

            foreach ($namespaceData->read()->toArray() as $index => $values) {
                if (!array_key_exists($index, $locales)) {
                    $locales[$index] = 'SET @locale_' . $index . ' = (SELECT id FROM s_core_locales WHERE locale = \'' . $index . '\');';
                }

                $queryWriter->setUpdate($update);
                $queryWriter->write($values, $namespace, '@locale_' . $index, 1);
            }
        }
        $result = $queryWriter->getQueries();
        foreach ($locales as $locale) {
            array_unshift($result, $locale);
        }

        return $result;
    }
}
