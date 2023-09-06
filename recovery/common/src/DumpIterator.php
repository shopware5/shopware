<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Recovery\Common;

use Countable;
use Exception;
use OutOfBoundsException;
use ReturnTypeWillChange;
use SeekableIterator;

class DumpIterator implements SeekableIterator, Countable
{
    /**
     * @var int
     */
    protected $count;

    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $current;

    /**
     * @param string $filename
     *
     * @throws Exception
     */
    public function __construct($filename)
    {
        $this->stream = fopen($filename, 'rb');
        if (!$this->stream) {
            throw new Exception('Can not open stream. File: ' . $filename);
        }

        $this->position = 0;
        $this->count = 0;

        while (!feof($this->stream)) {
            stream_get_line($this->stream, 1000000, ";\n");
            ++$this->count;
        }

        $this->rewind();
    }

    public function __destruct()
    {
        if ($this->stream !== null) {
            fclose($this->stream);
        }
    }

    /**
     * @param int $position
     *
     * @throws OutOfBoundsException
     */
    #[ReturnTypeWillChange]
    public function seek($position)
    {
        $this->rewind();

        while ($this->position < $position && $this->valid()) {
            $this->next();
        }

        if ($this->key() < $position) {
            throw new OutOfBoundsException("invalid seek position ($position)");
        }
    }

    /**
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        rewind($this->stream);
        $this->current = stream_get_line($this->stream, 1000000, ";\n");
        $this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
        $this->position = 0;
    }

    #[ReturnTypeWillChange]
    public function current()
    {
        return $this->current;
    }

    /**
     * @return int
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        ++$this->position;

        $this->current = stream_get_line($this->stream, 1000000, ";\n");
        $this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
    }

    /**
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function valid()
    {
        return !feof($this->stream);
    }
}
