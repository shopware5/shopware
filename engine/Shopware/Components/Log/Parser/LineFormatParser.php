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
 * Parses log entries assuming their text format to be the one used by the line formatter
 * Shopware\Components\Log\Formatter:
 *
 *      [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
 *
 * @category  Shopware
 * @package   Shopware\Components\Log\Parser
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LineFormatParser
{
    /**
     * Parses the individual components of a log line in steps, beginning with the date.
     * If a component cannot be parsed successfully, the log entry is assumed to be malformatted
     * and anything that has been parsed from the line so far is returned.
     *
     * @param string $line
     * @return array
     */
    public function parseLine($line)
    {
        $logEntry = [
            'rawLine' => $line
        ];

        try {
            // Parse the components
            $logEntry['timestamp'] = $this->parseTimestamp($line);
            $logEntry['level'] = $this->parseLevel($line);
            $logEntry['extra'] = $this->parseExtra($line);
            $logEntry['message'] = $this->parseMessage($line);
            $logEntry['context'] = $this->parseContext($line);
        } catch (\InvalidArgumentException $e) {
            if (empty($logEntry['message'])) {
                // Use 'rawLine' as fallback message
                $logEntry['message'] = $logEntry['rawLine'];
            }

            return $logEntry;
        }

        // Move exceptions to the top level if available
        if (isset($logEntry['context']['exception'])) {
            $logEntry['exception'] = $logEntry['context']['exception'];
            unset($logEntry['context']['exception']);
        }

        // Old log messages have a 'uid' from the 'extra' data as the log entry id
        if (isset($logEntry['extra']['uid'])) {
            $logEntry['id'] = $logEntry['extra']['uid'];
        }

        return $logEntry;
    }

    /**
     * @param string $line
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    private function parseTimestamp(&$line)
    {
        $result = preg_match('/^\[(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})\]\s*/u', $line, $timestampMatch);
        if ($result !== 1) {
            throw new \InvalidArgumentException('Failed to parse "timestamp" from log entry.');
        }

        // Remove timestamp from the line
        $line = substr($line, strlen($timestampMatch[0]));

        return new \DateTime($timestampMatch[1]);
    }

    /**
     * @param string $line
     * @return string
     * @throws \InvalidArgumentException
     */
    private function parseLevel(&$line)
    {
        $result = preg_match('/^\S+\.(\S+):\s*/u', $line, $levelMatch);
        if ($result !== 1) {
            throw new \InvalidArgumentException('Failed to parse "level" from log entry.');
        }

        // Remove channel and level from the line
        $line = substr($line, strlen($levelMatch[0]));

        return $levelMatch[1];
    }

    /**
     * Match 'extra' log field JSON. We match any string beginning with '{' and preceding
     * whitespace and ending with '}'. This preceding whitespace condition prevents us from
     * matching inner objects inside the complete "extra" JSON object. Within the object, we
     * do allow '{' characters, but only if they are not preceded by whitespace. This means
     * we do not allow JSON containing ' {' in a string. An alternative would be to find all
     * '\s{' backwards and try parsing each potential match as JSON until we find valid JSON.
     *
     * @param string $line
     * @return array
     * @throws \InvalidArgumentException
     */
    private function parseExtra(&$line)
    {
        $result = preg_match('/\s+((\{|\[)(\S|\s[^\{\[])*(\}|\]))\s*$/um', $line, $extraMatch);
        if ($result !== 1) {
            throw new \InvalidArgumentException('Failed to parse "extra" from log entry.');
        }

        // Try to parse the JSON
        $extra = json_decode($extraMatch[1], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Failed to parse "extra" from log entry.');
        }

        // Remove extra from the line
        $line = substr($line, 0, - 1 * strlen($extraMatch[0]));

        return $extra;
    }

    /**
     * Match 'message' log field JSON starting at the beginning of the remaining message. Since
     * we already truncated the 'extra' JSON, the only part of the original message format left
     * is '%message% %context%'. We don't allow using ' {' or ' [' in the message when trying to
     * find it and assume that the first occurrence of ' {' or ' [' markes the beginning of the
     * 'context'.
     *
     * @param string $line
     * @return string
     * @throws \InvalidArgumentException
     */
    private function parseMessage(&$line)
    {
        $result = preg_match('/^(\s*([^\{\[]|\s[^\{\[])*)?\s*(\{|\[)/u', $line, $messageMatch);
        if ($result !== 1) {
            throw new \InvalidArgumentException('Failed to parse "message" from log entry.');
        }

        // Remove extra from the line
        $line = substr($line, strlen($messageMatch[1]));

        return trim($messageMatch[1]);
    }

    /**
     * @param string $line
     * @return array
     * @throws \InvalidArgumentException
     */
    private function parseContext($line)
    {
        // Try to parse the line as JSON
        $context = json_decode(trim($line), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Failed to parse "context" from log entry.');
        }

        return $context;
    }
}
