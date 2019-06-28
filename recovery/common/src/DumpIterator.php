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

namespace Shopware\Recovery\Common;

class DumpIterator implements \SeekableIterator, \Countable
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
     * @var
     */
    protected $current;

    /**
     * @param string $filename
     *
     * @throws \Exception
     */
    public function __construct($filename)
    {
        $this->stream = fopen($filename, 'rb');
        if (!$this->stream) {
            throw new \Exception('Can not open stream. File: ' . $filename);
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
     * @throws \OutOfBoundsException
     */
    public function seek($position)
    {
        $this->rewind();

        while ($this->position < $position && $this->valid()) {
            $this->next();
        }

        if ($this->key() < $position) {
            throw new \OutOfBoundsException("invalid seek position ($position)");
        }
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    public function rewind()
    {
        rewind($this->stream);
        $this->current = stream_get_line($this->stream, 1000000, ";\n");
        $this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
        $this->position = 0;
    }

    public function current()
    {
        return $this->current;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;

        $this->current = stream_get_line($this->stream, 1000000, ";\n");
        $this->current = trim(preg_replace('#^\s*--[^\n\r]*#', '', $this->current));
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return !feof($this->stream);
    }
}
