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

namespace Shopware\Components\Log\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\Logger;

/**
 * Formats a log message as an HTML table
 */
class HtmlFormatter extends NormalizerFormatter
{
    /**
     * Translates Monolog log levels to html color priorities.
     */
    private $logLevels = [
        Logger::DEBUG => '#cccccc',
        Logger::INFO => '#468847',
        Logger::NOTICE => '#3a87ad',
        Logger::WARNING => '#c09853',
        Logger::ERROR => '#f0ad4e',
        Logger::CRITICAL => '#FF7708',
        Logger::ALERT => '#C12A19',
        Logger::EMERGENCY => '#000000',
    ];

    /**
     * @param string $dateFormat The format of the timestamp: one supported by DateTime::format
     */
    public function __construct($dateFormat = null)
    {
        parent::__construct($dateFormat);
    }

    /**
     * Formats a log record.
     *
     * @param array $record A record to format
     *
     * @return mixed The formatted record
     */
    public function format(array $record)
    {
        $output = $this->addTitle($record['level_name'], $record['level']);
        $output .= '<table cellspacing="1" width="100%">';

        $output .= $this->addRow('Message', (string) $record['message']);
        $output .= $this->addRow('Time', $record['datetime']->format('Y-m-d\TH:i:s.uO'));
        $output .= $this->addRow('Channel', $record['channel']);
        if ($record['context']) {
            $output .= $this->addRow('Context', $this->convertToString($record['context']));
        }
        if ($record['extra']) {
            if (is_array($record['extra'])) {
                foreach ($record['extra'] as $key => $row) {
                    $output .= $this->addRow($key, $this->convertToString($row));
                }
            } else {
                $output .= $this->addRow('Extra', $this->convertToString($record['extra']));
            }
        }

        return $output . '</table>';
    }

    /**
     * Formats a set of log records.
     *
     * @param array $records A set of records to format
     *
     * @return mixed The formatted set of records
     */
    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    protected function convertToString($data)
    {
        if ($data === null || is_scalar($data)) {
            return (string) $data;
        }

        $data = $this->normalize($data);

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Creates an HTML table row
     *
     * @param string $th Row header content
     * @param string $td Row standard cell content
     *
     * @return string
     */
    private function addRow($th, $td = ' ')
    {
        $th = htmlspecialchars($th, ENT_NOQUOTES);
        $td = '<pre>' . htmlspecialchars($td, ENT_NOQUOTES) . '</pre>';

        return "<tr style=\"padding: 4px;spacing: 0;text-align: left;\">\n<th style=\"background: #cccccc\" width=\"100px\">$th:</th>\n<td style=\"padding: 4px;spacing: 0;text-align: left;background: #eeeeee\">" . $td . "</td>\n</tr>";
    }

    /**
     * Create a HTML h1 tag
     *
     * @param string $title Text to be in the h1
     * @param int    $level Error level
     *
     * @return string
     */
    private function addTitle($title, $level)
    {
        $title = htmlspecialchars($title, ENT_NOQUOTES);

        return '<h1 style="background: ' . $this->logLevels[$level] . ';color: #ffffff;padding: 5px;">' . $title . '</h1>';
    }
}
