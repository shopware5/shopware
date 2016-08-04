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

use Monolog\Formatter\LineFormatter as MonologLineFormatter;

/**
 * @category  Shopware
 * @package   Shopware\Components\Log\Formatter
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class LineFormatter extends MonologLineFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        if (isset($record['context']['exception'])
            && ($record['context']['exception'] instanceof \Exception || $record['context']['exception'] instanceof \Throwable)) {
            // Replace the exception with its formatted data
            $record['context']['exception'] = $this->normalizeException($record['context']['exception']);

            // Use the exception message, if no message has been provided
            if (empty($record['message'])) {
                $record['message'] = $record['context']['exception']['message'];
            }
        }

        return parent::format($record);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeException($exception)
    {
        if (!$exception instanceof \Exception && !$exception instanceof \Throwable) {
            throw new \InvalidArgumentException('Exception/Throwable expected, got '.gettype($exception).' / '.get_class($exception));
        }

        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
            'previous' => ($exception->getPrevious()) ? $this->normalizeException($exception->getPrevious()) : null
        ];
    }
}
