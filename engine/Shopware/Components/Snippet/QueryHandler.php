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

namespace Shopware\Components\Snippet;

use Symfony\Component\Finder\Finder;

/**
 * @category  Shopware
 * @package   Shopware\Components\Snippet
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class QueryHandler
{
    /**
     * @var string The snippet dir
     */
    protected $snippetsDir;

    /**
     * @var \Enlight_Config_Adapter_File the file adapter
     */
    protected $inputAdapter;

    /**
     * @var \Enlight_Config_Writer_Query the query writer
     */
    protected $outputWriter;

    public function __construct($snippetsDir)
    {
        $this->snippetsDir = $snippetsDir;
    }

    /**
     * Parses current .ini snippet files and generates the matching MySQL queries
     *
     * @param string  $snippetsDir
     * @param bool $update if false, UPDATE IGNORE statements are generated. Default true, generates UPDATE .. ON DUPLICATE KEY statements
     * @return array The array containing the generated queries.
     */
    public function loadToQuery($snippetsDir = null, $update = true)
    {
        $snippetsDir = $snippetsDir?:$this->snippetsDir;

        if (!file_exists($snippetsDir)) {
            return array();
        }

        $locales = array();
        $locales['default'] = 'SET @locale_default = (SELECT id FROM s_core_locales WHERE locale = \'en_GB\');';

        $finder = new Finder();

        $this->inputAdapter = new \Enlight_Config_Adapter_File(array(
            'configDir' => $snippetsDir,
        ));
        $this->outputWriter = new \Enlight_Config_Writer_Query(array(
            'table' => 's_core_snippets',
            'namespaceColumn' => 'namespace',
            'sectionColumn' => array('shopID', 'localeID')
        ));

        $finder->files()->in($snippetsDir);
        foreach ($finder as $file) {
            $filePath = $file->getRelativePathname();
            if (strpos($filePath, '.ini') == strlen($filePath)-4) {
                $namespace = substr($filePath, 0, -4);
            } else {
                continue;
            }

            $namespaceData = new \Enlight_Components_Snippet_Namespace(array(
                'adapter' => $this->inputAdapter,
                'name' => $namespace,
            ));

            foreach ($namespaceData->read()->toArray() as $index => $values) {
                if (!array_key_exists($index, $locales)) {
                    $locales[$index] = 'SET @locale_'.$index.' = (SELECT id FROM s_core_locales WHERE locale = \''.$index.'\');';
                }

                $namespaceData->setSection(array(1, '@locale_'.$index))->read();
                $namespaceData->setData($values);
                $this->outputWriter->write($namespaceData, array_keys($values), $update);
            }
        }
        $result = $this->outputWriter->getQueries();
        foreach ($locales as $locale) {
            array_unshift($result, $locale);
        }
        return $result;
    }
}
