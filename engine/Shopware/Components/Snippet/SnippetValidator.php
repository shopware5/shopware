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

/**
 * @category  Shopware
 * @package   Shopware\Components\Snippet
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class SnippetValidator
{
    /**
     * @param string $snippetsDir
     * @return array
     */
    public function validate($snippetsDir)
    {
        if (!file_exists($snippetsDir)) {
            throw new \RuntimeException('Could not find ' . $snippetsDir . ' folder for snippet validation');
        }

        $dirIterator = new \RecursiveDirectoryIterator($snippetsDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator(
            $dirIterator,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $invalidSnippets = [];

        /** @var $entry \SplFileInfo */
        foreach ($iterator as $entry) {
            if (!$entry->isFile() || substr($entry->getFileName(), -4) !== '.ini') {
                continue;
            }

            $data = @parse_ini_file($entry->getRealPath(), true, INI_SCANNER_RAW);
            if ($data  === false) {
                $error = error_get_last();
                $invalidSnippets[] = $error['message'];
            }
        }

        return $invalidSnippets;
    }
}
