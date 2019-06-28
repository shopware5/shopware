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

use Doctrine\DBAL\Connection;

class SnippetValidator
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $snippetsDir
     *
     * @return array
     */
    public function validate($snippetsDir)
    {
        if (!file_exists($snippetsDir)) {
            throw new \RuntimeException(sprintf('Could not find %s folder for snippet validation', $snippetsDir));
        }

        $dirIterator = new \RecursiveDirectoryIterator($snippetsDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator(
            $dirIterator,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $invalidSnippets = [];
        $validLocales = $this->getValidLocales();

        /** @var \SplFileInfo $entry */
        foreach ($iterator as $entry) {
            if (!$entry->isFile() || substr($entry->getFilename(), -4) !== '.ini') {
                continue;
            }

            $data = @parse_ini_file($entry->getRealPath(), true);

            if ($data === false) {
                $error = error_get_last();
                $invalidSnippets[] = $error['message'] . ' (' . $entry->getRealPath() . ')';
                continue;
            }

            $dataRaw = @parse_ini_file($entry->getRealPath(), true, INI_SCANNER_RAW);

            if ($dataRaw === false) {
                $error = error_get_last();
                $invalidSnippets[] = $error['message'] . ' (' . $entry->getRealPath() . ')';
            } else {
                $diffGroups = array_diff(array_keys($data), $validLocales);
                if (array_key_exists('default', $data)) {
                    $invalidSnippets[] = '"Default" snippet group is deprecated (' . $entry->getRealPath() . ')';
                } elseif ($diffGroups) {
                    $invalidSnippets[] = sprintf(
                        'Invalid snippet group(s): %s (%s)',
                        implode(', ', $diffGroups),
                        $entry->getRealPath()
                    );
                }
            }
        }

        return $invalidSnippets;
    }

    /**
     * @return array
     */
    private function getValidLocales()
    {
        $locales = $this->db->executeQuery(
            'SELECT locale
            FROM `s_core_locales`'
        )->fetchAll(\PDO::FETCH_COLUMN);

        return $locales;
    }
}
