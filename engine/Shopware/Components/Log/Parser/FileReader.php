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

namespace Shopware\Components\Log\Parser;

/**
 * @category  Shopware
 * @package   Shopware\Components\Log\Parser
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class FileReader
{
    /**
     * @var LineFormatParser $lineParser
     */
    private $lineParser;

    /**
     * @var string $logsPath
     */
    private $logsPath;

    /**
     * @var string $environment
     */
    private $environment;

    /**
     * @param LineFormatParser $lineParser
     * @param string $logsPath
     * @param string $environment
     */
    public function __construct(LineFormatParser $lineParser, $logsPath, $environment)
    {
        $this->lineParser = $lineParser;
        $this->logsPath = $logsPath;
        $this->environment = $environment;
    }

    /**
     * @param string $logType
     * @param int $offset
     * @param int $limit
     * @param boolean $sortAscending
     * @return array
     */
    public function readEntries($logType, $offset = 0, $limit = -1, $sortAscending = false)
    {
        // Pars log files
        $skipped = 0;
        $entries = [];
        $logFiles = $this->findLogFiles($logType, $sortAscending);
        foreach ($logFiles as $filePath) {
            // Read file line by line
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                continue;
            }
            $lines = [];
            while (($line = fgets($handle)) !== false) {
                $lines[] = $line;
            }
            fclose($handle);

            if (!$sortAscending) {
                // Revers lines to read newest results first
                $lines = array_reverse($lines, true);
            }

            // Parse log lines
            foreach ($lines as $lineNumber => $line) {
                // Skip all entries before the offset and after reaching the limit, but count them
                if ($skipped < $offset || count($entries) === $limit) {
                    $skipped++;
                    continue;
                }

                // Parse the current line
                $logEntry = $this->lineParser->parseLine($line);
                if (!isset($logEntry['id'])) {
                    $logEntry['id'] = sha1($filePath . ':' . $lineNumber . ':' . $line);
                }
                $entries[] = $logEntry;
            }
        }

        return [
            'data' => $entries,
            'total' => count($entries) + $skipped
        ];
    }

    /**
     * @param string $logType
     * @param boolean $sortAscending
     * @return string[]
     */
    private function findLogFiles($logType, $sortAscending)
    {
        // Find log files matching the path, environment and type
        $pattern = '/'.preg_quote($logType, '/').'_'.preg_quote($this->environment, '/').'-.*\.log/';
        $files = scandir($this->logsPath, ($sortAscending) ? SCANDIR_SORT_ASCENDING : SCANDIR_SORT_DESCENDING);
        $logFiles = array_filter($files, function ($fileName) use ($pattern) {
            return preg_match($pattern, $fileName) === 1;
        });
        $logFiles = array_map(function ($fileName) {
            return $this->logsPath . '/' . $fileName;
        }, $logFiles);

        return $logFiles;
    }
}
